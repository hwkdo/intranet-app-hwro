<?php

use Hwkdo\IntranetAppHwro\Models\IntranetAppHwroSettings;
use Illuminate\Support\Facades\Schedule;

// In Tests überspringe das Laden der Settings
if (! app()->runningUnitTests()) {
    $settings = IntranetAppHwroSettings::current()?->settings;

    if ($settings?->scheduleSearchBetriebsnr) {
        Schedule::command('intranet-app-hwro:search-betriebsnr')
            ->cron("*/{$settings->scheduleSearchBetriebsnrIntervalMinutes} * * * *");
    }

    if ($settings?->scheduleMakeBetriebsakte) {
        Schedule::command('intranet-app-hwro:make-betriebsakte')
            ->cron("*/{$settings->scheduleMakeBetriebsakteIntervalMinutes} * * * *");
    }
}