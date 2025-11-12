<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Hwkdo\IntranetAppHwro\Models\Vorgang;
use Illuminate\Support\Facades\Log;

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

    Route::post('apps/hwro/vorgang/{vorgangsnummer}/gewan', function (Request $request, string $vorgangsnummer) {
        $validated = $request->validate([
            'file' => 'required|file',
        ]);


        try {
            // Füge die XML-Datei zur "gewan" Collection hinzu
            $vorgang = Vorgang::firstWhere('vorgangsnummer', $vorgangsnummer);
            $vorgang->clearMediaCollection('default');
            if (!$vorgang) {
                return response()->json([
                    'message' => 'Vorgang nicht gefunden',
                ], 404);
            }
            
            $result = $vorgang->addMedia($validated['file'])
                ->usingFileName($validated['file']->getClientOriginalName())
                ->toMediaCollection('default');


            return response()->json([
                'message' => 'GEWAN-Datei erfolgreich gespeichert',
            ], 200);
        } catch (\Exception $e) {
            report($e);
            Log::error('api.apps.hwro.vorgang.gewan.store', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Fehler beim Speichern der GEWAN-Datei: ' . $e->getMessage(),
            ], 500);
        }
    })->name('api.apps.hwro.vorgang.gewan.store');
});
