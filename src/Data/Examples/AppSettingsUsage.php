<?php

namespace Hwkdo\IntranetAppHwro\Data\Examples;

use Hwkdo\IntranetAppHwro\Data\AppSettings;

/**
 * Beispiel für die Verwendung der AppSettings mit Beschreibungen
 */
class AppSettingsUsage
{
    public function demonstrateUsage(): void
    {
        $settings = new AppSettings();
        
        // Einzelne Beschreibung abrufen
        $description = $settings->getDescriptionFor('scheduleSearchBetriebsnr');
        echo "Beschreibung für scheduleSearchBetriebsnr: " . $description . "\n";
        
        // Alle Eigenschaften mit Beschreibungen abrufen
        $allProperties = $settings->getPropertiesWithDescriptions();
        
        foreach ($allProperties as $propertyName => $propertyData) {
            echo "\nEigenschaft: {$propertyName}\n";
            echo "Wert: " . var_export($propertyData['value'], true) . "\n";
            echo "Typ: {$propertyData['type']}\n";
            echo "Beschreibung: {$propertyData['description']}\n";
        }
    }
}
