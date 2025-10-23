<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;


Route::middleware(['web','auth','can:see-app-hwro'])->group(function () {        
    Volt::route('apps/hwro', 'apps.hwro.index')->name('apps.hwro.index');
    
    // Vorgänge
    Volt::route('apps/hwro/vorgaenge', 'apps.hwro.vorgaenge.index')->name('apps.hwro.vorgaenge.index');
    Volt::route('apps/hwro/vorgaenge/create', 'apps.hwro.vorgaenge.create')->name('apps.hwro.vorgaenge.create');
    Volt::route('apps/hwro/vorgaenge/{vorgang}', 'apps.hwro.vorgaenge.show')->name('apps.hwro.vorgaenge.show');
    
    // Settings
    Volt::route('apps/hwro/settings/user', 'apps.hwro.settings.user')->name('apps.hwro.settings.user');
});

Route::middleware(['web','auth','can:manage-app-hwro'])->group(function () {
    Volt::route('apps/hwro/admin', 'apps.hwro.admin.index')->name('apps.hwro.admin.index');
    Route::redirect('apps/hwro/settings/admin', 'apps/hwro/admin');
});
