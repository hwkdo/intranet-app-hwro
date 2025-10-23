<?php

namespace Hwkdo\IntranetAppHwro;

use Hwkdo\IntranetAppHwro\Commands\SearchBetriebsnr;
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
            ->hasCommand(SearchBetriebsnr::class);
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
}
