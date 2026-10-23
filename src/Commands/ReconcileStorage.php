<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppHwro\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToDeleteFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;

class ReconcileStorage extends Command
{
    private const string DISK = 'intranet-app-hwro';

    /**
     * @var list<string>
     */
    private const array ROOTS = ['dokumente', 'vorgaenge'];

    protected $signature = 'intranet-app-hwro:reconcile-storage
                            {--dry-run : Nur anzeigen, nichts löschen}';

    protected $description = 'Gleicht HWRO-Dateien auf der Platte mit den Media-Einträgen ab';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        Log::info('intranet-app-hwro:reconcile-storage started', ['dry_run' => $dryRun]);

        $disk = Storage::disk(self::DISK);
        $plan = $this->plan($disk);

        if ($plan['aborted']) {
            $this->error('Soll-Pfade konnten nicht ermittelt werden. Es wurde nichts gelöscht.');
            Log::error('intranet-app-hwro:reconcile-storage aborted');

            return self::FAILURE;
        }

        $this->info($dryRun ? 'HWRO-Storage-Abgleich (Dry-Run)' : 'HWRO-Storage-Abgleich');

        $failed = [];

        if ($dryRun) {
            $this->reportPaths('Dateien ohne Media-Eintrag', $plan['orphans']);
            $this->reportPaths('Ordner ohne verbleibende Datei', $plan['directories']);
        } else {
            $result = $this->delete($disk, $plan['orphans'], $plan['directories']);
            $failed = $result['failed'];
            $this->reportPaths('Gelöschte Dateien', $result['files']);
            $this->reportPaths('Gelöschte Ordner', $result['directories']);
            $this->reportPaths('Fehlgeschlagen', $failed);
        }

        $this->info('Media-Einträge ohne Datei, unverändert: '.count($plan['missing']));

        Log::info('intranet-app-hwro:reconcile-storage finished', [
            'dry_run' => $dryRun,
            'orphans' => count($plan['orphans']),
            'directories' => count($plan['directories']),
            'missing' => count($plan['missing']),
            'failed' => count($failed),
        ]);

        return $failed === [] ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @return array{aborted: bool, orphans: list<string>, directories: list<string>, missing: list<string>}
     */
    private function plan(Filesystem $disk): array
    {
        $mediaCount = Media::query()
            ->where(function ($query): void {
                $query->where('disk', self::DISK)
                    ->orWhere('conversions_disk', self::DISK);
            })
            ->count();

        $expected = $this->expectedPaths();

        if ($mediaCount > 0 && $expected === []) {
            return [
                'aborted' => true,
                'orphans' => [],
                'directories' => [],
                'missing' => [],
            ];
        }

        $files = $this->filesOnDisk($disk);
        $expectedLookup = array_fill_keys($expected, true);

        $orphans = array_values(array_filter(
            $files,
            fn (string $path): bool => ! isset($expectedLookup[$path]) && $this->isManagedPath($path),
        ));
        $missing = array_values(array_filter(
            $expected,
            fn (string $path): bool => ! in_array($path, $files, true),
        ));

        sort($orphans);
        sort($missing);

        $directories = $this->directoriesToRemove($disk, $files, $orphans);
        sort($directories);

        return [
            'aborted' => false,
            'orphans' => $orphans,
            'directories' => $directories,
            'missing' => $missing,
        ];
    }

    /**
     * @return list<string>
     */
    private function expectedPaths(): array
    {
        $paths = [];

        Media::query()
            ->where(function ($query): void {
                $query->where('disk', self::DISK)
                    ->orWhere('conversions_disk', self::DISK);
            })
            ->lazyById()
            ->each(function (Media $media) use (&$paths): void {
                if ($media->disk === self::DISK) {
                    $paths[$this->normalize($media->getPathRelativeToRoot())] = true;

                    $responsiveDirectory = PathGeneratorFactory::create($media)->getPathForResponsiveImages($media);
                    foreach ($media->responsiveImages()->getFilenames() as $fileName) {
                        $paths[$this->normalize($responsiveDirectory.$fileName)] = true;
                    }
                }

                if ($media->conversions_disk !== self::DISK) {
                    return;
                }

                foreach ($media->getGeneratedConversions() as $conversion => $generated) {
                    if (! $generated) {
                        continue;
                    }

                    $paths[$this->normalize($media->getPathRelativeToRoot($conversion))] = true;

                    $responsiveDirectory = PathGeneratorFactory::create($media)->getPathForResponsiveImages($media);
                    foreach ($media->responsiveImages($conversion)->getFilenames() as $fileName) {
                        $paths[$this->normalize($responsiveDirectory.$fileName)] = true;
                    }
                }
            });

        return array_keys($paths);
    }

    /**
     * @return list<string>
     */
    private function filesOnDisk(Filesystem $disk): array
    {
        $files = [];

        foreach (self::ROOTS as $root) {
            if (! $disk->directoryExists($root)) {
                continue;
            }

            foreach ($disk->allFiles($root) as $path) {
                $files[] = $this->normalize($path);
            }
        }

        return $files;
    }

    /**
     * @param  list<string>  $files
     * @param  list<string>  $orphans
     * @return list<string>
     */
    private function directoriesToRemove(Filesystem $disk, array $files, array $orphans): array
    {
        $orphanLookup = array_fill_keys($orphans, true);
        $directories = [];

        foreach (self::ROOTS as $root) {
            if (! $disk->directoryExists($root)) {
                continue;
            }

            foreach ($disk->allDirectories($root) as $directory) {
                $directory = $this->normalize($directory);
                if (! $this->isManagedPath($directory)) {
                    continue;
                }

                $prefix = $directory.'/';
                $keepsFile = false;

                foreach ($files as $file) {
                    if (str_starts_with($file, $prefix) && ! isset($orphanLookup[$file])) {
                        $keepsFile = true;
                        break;
                    }
                }

                if (! $keepsFile) {
                    $directories[] = $directory;
                }
            }
        }

        return $directories;
    }

    /**
     * @param  list<string>  $orphans
     * @param  list<string>  $directories
     * @return array{files: list<string>, directories: list<string>, failed: list<string>}
     */
    private function delete(Filesystem $disk, array $orphans, array $directories): array
    {
        $deletedFiles = [];
        $deletedDirectories = [];
        $failed = [];

        foreach ($orphans as $path) {
            try {
                $disk->delete($path);
            } catch (UnableToDeleteFile) {
                $failed[] = $path;

                continue;
            }

            if ($disk->exists($path)) {
                $failed[] = $path;

                continue;
            }

            $deletedFiles[] = $path;
        }

        usort($directories, fn (string $left, string $right): int => substr_count($right, '/') <=> substr_count($left, '/'));

        foreach ($directories as $directory) {
            if (! $disk->directoryExists($directory) || $disk->allFiles($directory) !== []) {
                continue;
            }

            try {
                $disk->deleteDirectory($directory);
            } catch (UnableToDeleteDirectory) {
                $failed[] = $directory;

                continue;
            }

            if ($disk->directoryExists($directory)) {
                $failed[] = $directory;

                continue;
            }

            $deletedDirectories[] = $directory;
        }

        return [
            'files' => $deletedFiles,
            'directories' => $deletedDirectories,
            'failed' => $failed,
        ];
    }

    /**
     * @param  list<string>  $paths
     */
    private function reportPaths(string $label, array $paths): void
    {
        $this->info($label.': '.count($paths));

        foreach ($paths as $path) {
            $this->line('  '.$path);
        }
    }

    private function isManagedPath(string $path): bool
    {
        foreach (self::ROOTS as $root) {
            if ($path === $root || str_starts_with($path, $root.'/')) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $path): string
    {
        return ltrim(str_replace('\\', '/', $path), '/');
    }
}
