<?php

namespace Hwkdo\IntranetAppHwro\Models;

use Hwkdo\BueLaravel\BueLaravel;
use Hwkdo\D3RestLaravel\Client as D3Client;
use Hwkdo\IntranetAppHwro\Services\d3Service;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Vorgang extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    
    protected $table = 'intranet_app_hwro_vorgangs';

    protected $fillable = ['vorgangsnummer', 'betriebsnr'];

    protected static function booted(): void
    {
        static::deleting(function (Vorgang $vorgang) {
            // Lösche alle Dokumente mit ihren Media-Dateien
            foreach ($vorgang->dokumente as $dokument) {
                // Lösche alle Media-Dateien des Dokuments
                $dokument->clearMediaCollection();
                
                // Lösche das Dokument selbst
                $dokument->delete();
            }
            
            // Lösche auch die GEWAN-Media-Datei
            $vorgang->clearMediaCollection('gewan');
        });
    }

    protected function casts(): array
    {
        return [
            'vorgangsnummer' => 'integer',
            'betriebsnr' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gewan')
            ->singleFile()
            ->useDisk('intranet-app-hwro');
    }

    public function bueBetrieb()
    {
        if (! $this->betriebsnr) {
            return null;
        }

        return app(BueLaravel::class)->getBetriebByBetriebsnr($this->betriebsnr);
    }

    public function makeD3Betriebsakte($delete = false)
    {
        if (! $this->betriebsnr) {
            return null;
        }

        $d3Service = app(d3Service::class);
        $d3Client = app(D3Client::class);
        $pdfService = app(\App\Services\PdfService::class);
        $tempFiles = [];

        // Hole alle Online-Eintragungsdokumente (als Modelle)
        $dokumente = $this->getD3OnlineEintragung();

        if ($dokumente->isEmpty()) {
            return null;
        }

        // Download aller Dokumente
        foreach ($dokumente as $dokument) {
            // Hole Betreff (hwro_typ)
            $betreff = $dokument->hwro_typ;

            if (! $betreff) {
                continue;
            }

            // Bestimme das Schlagwort basierend auf Betreff
            $schlagwort = match ($betreff) {
                'Eintragung' => 'Antrag auf Eintragung',
                'EintragungAnhang' => 'Anhang',
                default => null,
            };

            if (! $schlagwort) {
                continue;
            }

            // Erstelle temporären Dateipfad mit Präfix für Eintragung
            $prefix = $betreff === 'Eintragung' ? '1_' : '';
            $tempPath = storage_path('app/temp/'.$prefix.uniqid().'.pdf');
            \Illuminate\Support\Facades\File::ensureDirectoryExists(dirname($tempPath));

            // Download der Datei von D3 direkt in den temporären Pfad
            $downloadSuccess = $d3Client->downloadDoc($dokument->id, $tempPath);

            if (! $downloadSuccess) {
                // Lösche alle bisherigen temporären Dateien bei Fehler
                foreach ($tempFiles as $file) {
                    \Illuminate\Support\Facades\File::delete($file);
                }

                return [
                    'success' => false,
                    'message' => 'Download von D3 fehlgeschlagen für Dokument: '.$dokument->filename,
                ];
            }

            $tempFiles[] = $tempPath;
        }

        // Falls keine Dateien heruntergeladen wurden
        if (empty($tempFiles)) {
            return null;
        }

        // Sortiere Dateien, damit "1_" Dateien zuerst kommen
        sort($tempFiles);

        try {
            // Merge alle PDFs zu einer Datei
            $mergedPath = storage_path('app/temp');
            $mergedFilename = 'merged_'.uniqid().'.pdf';
            $mergedFullPath = $pdfService->mergePdfs($tempFiles, $mergedPath, $mergedFilename);

            // Lade das zusammengeführte PDF in D3 hoch
            $result = $d3Service->FormwerkEintragungToD3(
                $this->betriebsnr,
                $mergedFullPath,
                'Antrag auf Eintragung'
            );

            // Lösche alle temporären Dateien
            foreach ($tempFiles as $file) {
                \Illuminate\Support\Facades\File::delete($file);
            }
            \Illuminate\Support\Facades\File::delete($mergedFullPath);

            // Lösche Online-Eintragungsdokumente wenn gewünscht
            if ($delete && $result->success) {
                foreach ($dokumente as $dokument) {
                    $dokument->delete();
                }
            }

            return [
                'success' => $result->success,
                'message' => $result->message,
                'id' => $result->id,
                'merged_files' => count($tempFiles),
            ];
        } catch (\Exception $e) {
            // Lösche alle temporären Dateien auch bei Fehler
            foreach ($tempFiles as $file) {
                if (\Illuminate\Support\Facades\File::exists($file)) {
                    \Illuminate\Support\Facades\File::delete($file);
                }
            }

            if (isset($mergedFullPath) && \Illuminate\Support\Facades\File::exists($mergedFullPath)) {
                \Illuminate\Support\Facades\File::delete($mergedFullPath);
            }

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function makeD3BetriebsakteFromLocal()
    {
        if (! $this->betriebsnr) {
            return null;
        }

        $d3Service = app(d3Service::class);

        // Hole alle lokalen Dokumente mit Schlagwort und Media
        $dokumente = $this->dokumente()
            ->with(['schlagwort', 'media'])
            ->get();

        if ($dokumente->isEmpty()) {
            return null;
        }

        $uploadedCount = 0;
        $errors = [];

        // Übertrage jedes Dokument einzeln
        foreach ($dokumente as $dokument) {
            // Überspringe Dokumente ohne Schlagwort
            if (! $dokument->schlagwort) {
                continue;
            }

            // Hole die Media-Datei
            $media = $dokument->getFirstMedia();
            
            if (! $media) {
                continue;
            }

            try {
                // Lade das Dokument zu D3 hoch
                $result = $d3Service->FormwerkEintragungToD3(
                    $this->betriebsnr,
                    $media->getPath(),
                    $dokument->schlagwort->schlagwort
                );

                if ($result->success) {
                    $uploadedCount++;
                } else {
                    $errors[] = 'Fehler beim Hochladen von '.$media->file_name.': '.$result->message;
                }
            } catch (\Exception $e) {
                $errors[] = 'Fehler beim Hochladen von '.$media->file_name.': '.$e->getMessage();
            }
        }

        // Rückgabe der Ergebnisse
        if ($uploadedCount > 0) {
            return [
                'success' => true,
                'message' => $uploadedCount.' Dokument(e) erfolgreich übertragen',
                'uploaded_count' => $uploadedCount,
                'errors' => $errors,
            ];
        } elseif (! empty($errors)) {
            return [
                'success' => false,
                'message' => 'Fehler beim Übertragen: '.implode(', ', $errors),
                'errors' => $errors,
            ];
        }

        return null;
    }

    public function getD3OnlineEintragung()
    {
        return app(d3Service::class)->getD3OnlineEintragungByVorgangsnummer($this->vorgangsnummer);
    }

    public function getD3Betriebsakte()
    {
        return app(d3Service::class)->getD3BetriebsakteByBetriebsnr($this->betriebsnr);
    }

    public function dokumente(): HasMany
    {
        return $this->hasMany(Dokument::class);
    }
}
