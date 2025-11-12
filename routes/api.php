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

    Route::post('apps/hwro/vorgang/{vorgang:vorgangsnummer}/gewan', function (Request $request, Vorgang $vorgang) {
        

        // Erstelle temporäre XML-Datei aus dem String
        $tempPath = tempnam(sys_get_temp_dir(), 'gewan_');
        $xmlFilename = 'gewan_' . $vorgang->vorgangsnummer . '_' . now()->format('Y-m-d_H-i-s') . '.xml';
        file_put_contents($tempPath, $request->getContent());

        try {
            // Füge die XML-Datei zur "gewan" Collection hinzu
            $vorgang->addMedia($tempPath)
                ->usingFileName($xmlFilename)
                ->toMediaCollection('default');

            // Lösche die temporäre Datei
            @unlink($tempPath);

            return response()->json([
                'message' => 'GEWAN-Datei erfolgreich gespeichert',
                #'vorgang' => $vorgang,
                #'media' => $vorgang->getFirstMedia('default'),
            ], 200);
        } catch (\Exception $e) {
            // Lösche die temporäre Datei im Fehlerfall
            @unlink($tempPath);
            
            return response()->json([
                'message' => 'Fehler beim Speichern der GEWAN-Datei: ' . $e->getMessage(),
            ], 500);
        }
    })->name('api.apps.hwro.vorgang.gewan.store');
});
