<?php

namespace Hwkdo\IntranetAppHwro\Services;

use Hwkdo\IntranetAppHwro\Models\IntranetAppHwroSettings;
use Hwkdo\IntranetAppHwro\Models\Schlagwort;
use Illuminate\Support\Facades\Log;

class SchlagwortService
{
    /**
     * Ermittelt das passende Schlagwort basierend auf dem Dateinamen
     */
    public function findByFilename(string $filename): Schlagwort
    {
        $schlagwoerter = Schlagwort::all();

        // Durchlaufe alle Schlagwörter und prüfe, ob ein Wort aus filenames im Dateinamen vorkommt
        foreach ($schlagwoerter as $schlagwort) {
            if ($schlagwort->filenames && is_array($schlagwort->filenames)) {
                foreach ($schlagwort->filenames as $filenamePattern) {
                    if (stripos($filename, $filenamePattern) !== false) {
                        Log::info("Schlagwort '{$schlagwort->schlagwort}' gefunden für Datei: {$filename}");

                        return $schlagwort;
                    }
                }
            }
        }

        // Kein passendes Schlagwort gefunden, verwende Standard-Schlagwort
        $defaultSchlagwortName = IntranetAppHwroSettings::current()?->settings?->defaultSchlagwort;
        $defaultSchlagwort = Schlagwort::where('schlagwort', $defaultSchlagwortName)->first();

        if (! $defaultSchlagwort) {
            Log::warning("Standard-Schlagwort '{$defaultSchlagwortName}' nicht gefunden. Verwende erstes Schlagwort.");
            $defaultSchlagwort = Schlagwort::first();
        }

        Log::info("Standard-Schlagwort '{$defaultSchlagwort->schlagwort}' verwendet für Datei: {$filename}");

        return $defaultSchlagwort;
    }
}

