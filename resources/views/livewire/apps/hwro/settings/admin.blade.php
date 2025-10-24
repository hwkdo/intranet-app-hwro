<?php

use Hwkdo\IntranetAppHwro\Data\AppSettings;
use Hwkdo\IntranetAppHwro\Models\IntranetAppHwroSettings;
use Flux\Flux;

use function Livewire\Volt\{computed, mount, state, title};

title('Admin-Einstellungen - Handwerksrolle Online');

state([
    'appSettings' => [],
    'settingsId' => null,
]);

mount(function () {
    $settings = IntranetAppHwroSettings::current();
    
    if ($settings && $settings->settings) {
        $this->settingsId = $settings->id;
        $reflection = new \ReflectionClass($settings->settings);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        
        foreach ($properties as $property) {
            $key = $property->getName();
            $value = $property->getValue($settings->settings);
            
            if ($value instanceof \UnitEnum) {
                $this->appSettings[$key] = $value instanceof \BackedEnum ? $value->value : $value->name;
            } else {
                $this->appSettings[$key] = $value;
            }
        }
    }
});

$settingsStructure = computed(function () {
    $settings = IntranetAppHwroSettings::current();
    
    if (!$settings || !$settings->settings) {
        return [];
    }
    
    $structure = [];
    $appSettingsClass = AppSettings::class;
    $reflection = new \ReflectionClass($appSettingsClass);
    $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
    
    foreach ($settings->settings->toArray() as $key => $value) {
        $property = collect($properties)->first(fn ($p) => $p->getName() === $key);
        $propertyType = $property?->getType();
        
        // Prüfe ob das Property ein Enum ist
        if ($propertyType && !$propertyType->isBuiltin()) {
            $typeName = $propertyType instanceof \ReflectionNamedType ? $propertyType->getName() : null;
            
            if ($typeName && enum_exists($typeName)) {
                $enumClass = $typeName;
                $options = method_exists($enumClass, 'options')
                    ? $enumClass::options()
                    : collect($enumClass::cases())->mapWithKeys(fn ($case) => [$case->value => $case->name])->toArray();
                
                $structure[] = [
                    'key' => $key,
                    'type' => 'select',
                    'options' => $options,
                    'label' => __(str_replace('_', ' ', ucfirst($key))),
                    'description' => '',
                ];
                
                continue;
            }
        }
        
        // Fallback zu normalen Typen
        if (is_bool($value)) {
            $structure[] = [
                'key' => $key,
                'type' => 'switch',
                'label' => __(str_replace('_', ' ', ucfirst($key))),
                'description' => '',
            ];
        } elseif (is_numeric($value)) {
            $structure[] = [
                'key' => $key,
                'type' => 'number',
                'label' => __(str_replace('_', ' ', ucfirst($key))),
                'description' => '',
            ];
        } elseif (is_string($value)) {
            $structure[] = [
                'key' => $key,
                'type' => 'text',
                'label' => __(str_replace('_', ' ', ucfirst($key))),
                'description' => '',
            ];
        }
    }
    
    return $structure;
});

$save = function () {
    $settings = IntranetAppHwroSettings::find($this->settingsId);
    
    if ($settings) {
        $settings->settings = AppSettings::from($this->appSettings);
        $settings->save();
        
        Flux::toast(
            heading: 'Einstellungen gespeichert',
            text: 'Die Admin-Einstellungen wurden erfolgreich aktualisiert.',
            variant: 'success'
        );
    }
};

?>

<section class="w-full">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Handwerksrolle Online</flux:heading>
        <flux:subheading size="lg" class="mb-6">Admin-Einstellungen</flux:subheading>
        <flux:separator variant="subtle" />
    </div>
    
    <x-intranet-app-hwro::hwro-layout>

        <flux:card>
            <flux:heading size="lg" class="mb-4">Administrator-Einstellungen</flux:heading>
            <flux:text class="mb-6">
                Verwalten Sie die globalen Einstellungen für die Handwerksrolle-App.
            </flux:text>
            
            <div class="space-y-4">
                @foreach($this->settingsStructure as $field)
                    @if($field['type'] === 'switch')
                        <flux:switch 
                            wire:model.live="appSettings.{{ $field['key'] }}" 
                            :label="$field['label']"
                            :description="$field['description']"
                        />
                        @if(!$loop->last)
                            <flux:separator variant="subtle" />
                        @endif
                    @elseif($field['type'] === 'number')
                        <flux:input 
                            type="number"
                            wire:model="appSettings.{{ $field['key'] }}" 
                            :label="$field['label']"
                            :description="$field['description']"
                        />
                        @if(!$loop->last)
                            <flux:separator variant="subtle" />
                        @endif
                    @elseif($field['type'] === 'text')
                        <flux:input 
                            type="text"
                            wire:model="appSettings.{{ $field['key'] }}" 
                            :label="$field['label']"
                            :description="$field['description']"
                        />
                        @if(!$loop->last)
                            <flux:separator variant="subtle" />
                        @endif
                    @elseif($field['type'] === 'select')
                        <flux:select 
                            wire:model="appSettings.{{ $field['key'] }}"
                            variant="listbox"
                            :label="$field['label']"
                            :description="$field['description']"
                        >
                            @foreach($field['options'] as $value => $label)
                                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        @if(!$loop->last)
                            <flux:separator variant="subtle" />
                        @endif
                    @endif
                @endforeach
            </div>
            
            <div class="mt-6 flex justify-end">
                <flux:button wire:click="save" variant="primary">
                    Einstellungen speichern
                </flux:button>
            </div>
        </flux:card>
    </x-intranet-app-hwro::hwro-layout>
</section>

