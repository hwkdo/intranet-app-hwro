<?php

namespace Hwkdo\IntranetAppHwro\Data;

use Hwkdo\IntranetAppHwro\Data\Attributes\Description;
use Hwkdo\IntranetAppHwro\Enums\VorgaengeFilterEnum;
use Livewire\Wireable;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class UserSettings extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        #[Description('Standard-Filter für die Anzeige von Vorgängen (Alle, Offen, Geschlossen, etc.)')]
        #[WithCast(EnumCast::class)]
        public VorgaengeFilterEnum $defaultVorgaengeFilter = VorgaengeFilterEnum::Alle,
    ) {}

    /**
     * Gibt die Beschreibung für eine bestimmte Eigenschaft zurück
     */
    public function getDescriptionFor(string $property): ?string
    {
        $reflection = new \ReflectionClass($this);
        
        if (!$reflection->hasProperty($property)) {
            return null;
        }
        
        $propertyReflection = $reflection->getProperty($property);
        $attributes = $propertyReflection->getAttributes(Description::class);
        
        if (empty($attributes)) {
            return null;
        }
        
        return $attributes[0]->newInstance()->description;
    }

    /**
     * Gibt alle Eigenschaften mit ihren Beschreibungen zurück
     */
    public function getPropertiesWithDescriptions(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = [];
        
        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(Description::class);
            $description = null;
            
            if (!empty($attributes)) {
                $description = $attributes[0]->newInstance()->description;
            }
            
            $properties[$property->getName()] = [
                'value' => $property->getValue($this),
                'type' => $property->getType()?->getName(),
                'description' => $description,
            ];
        }
        
        return $properties;
    }
}
