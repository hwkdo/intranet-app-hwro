<?php

use Hwkdo\IntranetAppHwro\Data\UserSettings;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;

use function Livewire\Volt\{computed, mount, state, title};

title('Meine Einstellungen - Handwerksrolle Online');

state([
    'userSettings' => [],
]);

mount(function () {
    $settings = Auth::user()->settings->app->hwro;
    
    if ($settings) {
        $reflection = new \ReflectionClass($settings);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        
        foreach ($properties as $property) {
            $key = $property->getName();
            $value = $property->getValue($settings);
            
            if ($value instanceof \UnitEnum) {
                $this->userSettings[$key] = $value instanceof \BackedEnum ? $value->value : $value->name;
            } else {
                $this->userSettings[$key] = $value;
            }
        }
    }
});

$settingsStructure = computed(function () {
    $settings = Auth::user()->settings->app->hwro;
    
    if (!$settings) {
        return [];
    }
    
    $structure = [];
    $userSettingsClass = UserSettings::class;
    $reflection = new \ReflectionClass($userSettingsClass);
    $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
    
    foreach ($settings->toArray() as $key => $value) {
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
    $user = Auth::user();
    $user->settings = $user->settings->updateAppSettings('hwro', $this->userSettings);
    $user->save();
    
    Flux::toast(
        heading: 'Einstellungen gespeichert',
        text: 'Ihre Einstellungen wurden erfolgreich aktualisiert.',
        variant: 'success'
    );
};

?>

<section class="w-full">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Handwerksrolle Online</flux:heading>
        <flux:subheading size="lg" class="mb-6">Meine Einstellungen</flux:subheading>
        <flux:separator variant="subtle" />
    </div>
    
    <x-intranet-app-hwro::hwro-layout>

        <flux:card>
            <flux:heading size="lg" class="mb-4">Persönliche Einstellungen</flux:heading>
            <flux:text class="mb-6">
                Passen Sie Ihre persönlichen Einstellungen für die Handwerksrolle an.
            </flux:text>
            
            <div class="space-y-4">
                @foreach($this->settingsStructure as $field)
                    @if($field['type'] === 'switch')
                        <flux:switch 
                            wire:model.live="userSettings.{{ $field['key'] }}" 
                            :label="$field['label']"
                            :description="$field['description']"
                        />
                        @if(!$loop->last)
                            <flux:separator variant="subtle" />
                        @endif
                    @elseif($field['type'] === 'number')
                        <flux:input 
                            type="number"
                            wire:model="userSettings.{{ $field['key'] }}" 
                            :label="$field['label']"
                            :description="$field['description']"
                        />
                        @if(!$loop->last)
                            <flux:separator variant="subtle" />
                        @endif
                    @elseif($field['type'] === 'text')
                        <flux:input 
                            type="text"
                            wire:model="userSettings.{{ $field['key'] }}" 
                            :label="$field['label']"
                            :description="$field['description']"
                        />
                        @if(!$loop->last)
                            <flux:separator variant="subtle" />
                        @endif
                    @elseif($field['type'] === 'select')
                        <flux:select 
                            wire:model="userSettings.{{ $field['key'] }}"
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

