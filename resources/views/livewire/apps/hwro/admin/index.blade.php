<?php

use Hwkdo\IntranetAppHwro\Data\AppSettings;
use Hwkdo\IntranetAppHwro\Models\IntranetAppHwroSettings;
use Flux\Flux;

use function Livewire\Volt\{computed, mount, state, title};

title('Admin - Handwerksrolle Online');

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
        <flux:subheading size="lg" class="mb-6">Admin</flux:subheading>
        <flux:separator variant="subtle" />
    </div>
    
    <x-intranet-app-hwro::hwro-layout>
        <x-slot:navigation>
            <flux:navlist.item :href="route('apps.hwro.index')" wire:navigate>Übersicht</flux:navlist.item>
            <flux:navlist.item :href="route('apps.hwro.vorgaenge.index')" wire:navigate>Vorgänge</flux:navlist.item>
            <flux:navlist.item :href="route('apps.hwro.settings.user')" wire:navigate>Meine Einstellungen</flux:navlist.item>
            @can('manage-app-hwro')
                <flux:navlist.item :href="route('apps.hwro.admin.index')" wire:navigate current>Admin</flux:navlist.item>
            @endcan
        </x-slot:navigation>

        {{-- Custom Tabs mit Alpine.js (clientseitig) --}}
        <div x-data="{ activeTab: 'einstellungen' }">
            <div class="mb-6 border-b border-zinc-200 dark:border-zinc-700">
                <div class="flex gap-4">
                    <button 
                        @click="activeTab = 'einstellungen'"
                        :class="activeTab === 'einstellungen' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200'"
                        class="px-4 py-2 -mb-px border-b-2 transition-colors"
                    >
                        Einstellungen
                    </button>
                    <button 
                        @click="activeTab = 'scheduler'"
                        :class="activeTab === 'scheduler' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200'"
                        class="px-4 py-2 -mb-px border-b-2 transition-colors"
                    >
                        Scheduler
                    </button>
                </div>
            </div>

            <div x-show="activeTab === 'einstellungen'" x-cloak style="min-height: 400px;">
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
                    
                </div>
                
                <div class="mt-6 flex justify-end">
                    <flux:button wire:click="save" variant="primary">
                        Einstellungen speichern
                    </flux:button>
                </div>
            </flux:card>
            </div>

            <div x-show="activeTab === 'scheduler'" x-cloak style="min-height: 400px;">
                <flux:card>
                    <flux:heading size="lg" class="mb-4">Scheduler</flux:heading>
                    <flux:text class="text-zinc-500 dark:text-zinc-400">
                        Dieser Bereich wird zu einem späteren Zeitpunkt erweitert.
                    </flux:text>
                </flux:card>
            </div>
        </div>
    </x-intranet-app-hwro::hwro-layout>
</section>

