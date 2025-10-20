<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Hwkdo\IntranetAppHwro\Models\Vorgang;

Route::middleware(['auth:sanctum','can:manage-app-hwro'])
->prefix('api')
->group(function () {    
    Route::post('apps/hwro', function (Request $request) {
        $validated = $request->validate([
            'vorgangsnummer' => 'required|integer',
        ]);

        $vorgang = Vorgang::firstOrCreate([
            'vorgangsnummer' => $validated['vorgangsnummer'],
        ], [
            'betriebsnr' => null,
        ]);

        return response()->json($vorgang, 201);
    })->name('api.apps.hwro.store');
});
