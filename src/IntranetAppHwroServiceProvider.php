<?php

namespace Hwkdo\IntranetAppHwro;

use Hwkdo\IntranetAppHwro\Commands\SearchBetriebsnr;
use Hwkdo\IntranetAppHwro\Commands\MakeBetriebsakte;
use Hwkdo\IntranetAppHwro\Commands\CleanVorgaenge;
use Livewire\Volt\Volt;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class IntranetAppHwroServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('intranet-app-hwro')
            ->hasConfigFile()
            ->hasViews()
            ->discoversMigrations()
            ->hasCommand(SearchBetriebsnr::class)
            ->hasCommand(MakeBetriebsakte::class)
            ->hasCommand(CleanVorgaenge::class);
    }

    public function boot(): void
    {
        parent::boot();

        $this->app->booted(function () {
            Volt::mount(__DIR__.'/../resources/views/livewire');
        });
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/console.php');
    }

    public function register(): void
    {
        parent::register();
        $this->mergeConfigFrom(__DIR__ . '/../config/intranet-app-hwro-disk.php', 'filesystems.disks.intranet-app-hwro');
        $this->mergeConfigFrom(__DIR__ . '/../config/intranet-app-hwro-medialibrary.php', 'media-library.custom_path_generators');
    }
}
