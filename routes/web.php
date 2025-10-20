<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;


Route::middleware(['web','auth','can:see-app-hwro'])->group(function () {        
    Volt::route('apps/hwro', 'apps.hwro.index')->name('apps.hwro.index');                
});
