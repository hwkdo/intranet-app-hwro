<?php

namespace Hwkdo\IntranetAppHwro\Data\Examples;

use Hwkdo\IntranetAppHwro\Data\UserSettings;
use Hwkdo\IntranetAppHwro\Enums\VorgaengeFilterEnum;

/**
 * Beispiel für die Verwendung der UserSettings mit Beschreibungen
 */
class UserSettingsUsage
{
    public function demonstrateUsage(): void
    {
        $settings = new UserSettings();
        
        // Einzelne Beschreibung abrufen
        $description = $settings->getDescriptionFor('defaultVorgaengeFilter');
        echo "Beschreibung für defaultVorgaengeFilter: " . $description . "\n";
        
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
