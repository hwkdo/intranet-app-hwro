<?php

use Hwkdo\IntranetAppHwro\Data\AppSettings;
use Hwkdo\IntranetAppHwro\Models\IntranetAppHwroSettings;
use Flux\Flux;

use function Livewire\Volt\{computed, mount, state, title, on};

title('Admin - Handwerksrolle Online');

state([
    'appSettings' => [],
    'settingsId' => null,
    'schedulerEvents' => [],
    'activeTab' => 'einstellungen',
]);

on([
    'echo:intranet-app-hwro-betriebsnr-search,.betriebsnr.search.started' => function ($event) {
        array_unshift($this->schedulerEvents, [
            'id' => uniqid() . '-' . mt_rand(),
            'type' => 'Suche gestartet',
            'message' => $event['msg'] ?? '',
            'variant' => 'info',
            'timestamp' => now()->format('d.m.Y H:i:s'),
        ]);
        $this->schedulerEvents = array_slice($this->schedulerEvents, 0, 50);
    },
    'echo:intranet-app-hwro-betriebsnr-search,.betriebsnr.found' => function ($event) {
        array_unshift($this->schedulerEvents, [
            'id' => uniqid() . '-' . mt_rand(),
            'type' => 'Betriebsnummer gefunden',
            'message' => $event['msg'] ?? '',
            'variant' => 'success',
            'timestamp' => now()->format('d.m.Y H:i:s'),
        ]);
        $this->schedulerEvents = array_slice($this->schedulerEvents, 0, 50);
    },
    'echo:intranet-app-hwro-betriebsnr-search,.betriebsnr.not.found' => function ($event) {
        array_unshift($this->schedulerEvents, [
            'id' => uniqid() . '-' . mt_rand(),
            'type' => 'Betriebsnummer nicht gefunden',
            'message' => $event['msg'] ?? '',
            'variant' => 'warning',
            'timestamp' => now()->format('d.m.Y H:i:s'),
        ]);
        $this->schedulerEvents = array_slice($this->schedulerEvents, 0, 50);
    },
    'echo:intranet-app-hwro-betriebsnr-search,.betriebsnr.search.finished' => function ($event) {
        array_unshift($this->schedulerEvents, [
            'id' => uniqid() . '-' . mt_rand(),
            'type' => 'Suche abgeschlossen',
            'message' => $event['msg'] ?? '',
            'variant' => 'neutral',
            'timestamp' => now()->format('d.m.Y H:i:s'),
        ]);
        $this->schedulerEvents = array_slice($this->schedulerEvents, 0, 50);
    },
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

$clearSchedulerEvents = function () {
    $this->schedulerEvents = [];
};

$runSearchBetriebsnr = function () {
    \Artisan::call('intranet-app-hwro:search-betriebsnr');
    
    Flux::toast(
        heading: 'Command gestartet',
        text: 'Der Betriebsnummern-Suchvorgang wurde gestartet.',
        variant: 'success'
    );
};

$runMakeBetriebsakte = function () {
    \Artisan::call('intranet-app-hwro:make-betriebsakte');
    
    Flux::toast(
        heading: 'Command gestartet',
        text: 'Die Betriebsakten-Erstellung wurde gestartet.',
        variant: 'success'
    );
};

?>

<!-- Cache-Bust: {{ now()->timestamp }} -->
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

        {{-- Tabs mit Livewire --}}
        <div>
            <div class="mb-6 border-b border-zinc-200 dark:border-zinc-700">
                <div class="flex gap-4">
                    <button 
                        wire:click="$set('activeTab', 'einstellungen')"
                        class="px-4 py-2 -mb-px border-b-2 transition-colors {{ $activeTab === 'einstellungen' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200' }}"
                    >
                        Einstellungen
                    </button>
                    <button 
                        wire:click="$set('activeTab', 'scheduler')"
                        class="px-4 py-2 -mb-px border-b-2 transition-colors {{ $activeTab === 'scheduler' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200' }}"
                    >
                        Scheduler
                    </button>
                </div>
            </div>

            @if($activeTab === 'einstellungen')
            <div style="min-height: 400px;">
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
            @endif

            @if($activeTab === 'scheduler')
            <div style="min-height: 400px;">
                <flux:card>
                    <div class="mb-4 flex items-center justify-between">
                        <flux:heading size="lg">Scheduler Events</flux:heading>
                        <div class="flex gap-2">
                            @if(count($schedulerEvents) > 0)
                                <flux:button 
                                    wire:click="clearSchedulerEvents"
                                    variant="ghost"
                                    size="sm"
                                >
                                    Liste leeren
                                </flux:button>
                            @endif
                        </div>
                    </div>
                    
                    <flux:text class="mb-4 text-zinc-500 dark:text-zinc-400">
                        Live-Übersicht der Betriebsnummern-Suchvorgänge.
                    </flux:text>

                    <div class="mb-6 flex gap-2">
                        <flux:button 
                            wire:click="runSearchBetriebsnr"
                            icon="magnifying-glass"
                            size="sm"
                        >
                            Betriebsnummern suchen
                        </flux:button>
                        <flux:button 
                            wire:click="runMakeBetriebsakte"
                            icon="document-plus"
                            size="sm"
                            variant="outline"
                        >
                            Betriebsakten erstellen
                        </flux:button>
                    </div>

                    <flux:separator variant="subtle" class="mb-4" />

                    <div class="space-y-2" wire:poll.visible>
                        @if(count($schedulerEvents) === 0)
                            <div class="rounded-lg border border-dashed border-zinc-300 dark:border-zinc-700 p-8 text-center">
                                <flux:text class="text-zinc-500 dark:text-zinc-400">
                                    Keine Events empfangen. Warten auf Scheduler-Aktivität...
                                </flux:text>
                            </div>
                        @else
                            @foreach($schedulerEvents as $event)
                                <div 
                                    class="rounded-lg border p-4 @if($event['variant'] === 'info') border-blue-200 bg-blue-50 dark:border-blue-900/50 dark:bg-blue-950/30 @elseif($event['variant'] === 'success') border-green-200 bg-green-50 dark:border-green-900/50 dark:bg-green-950/30 @elseif($event['variant'] === 'warning') border-orange-200 bg-orange-50 dark:border-orange-900/50 dark:bg-orange-950/30 @else border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50 @endif"
                                    wire:key="event-{{ $event['id'] }}"
                                >
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium @if($event['variant'] === 'info') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @elseif($event['variant'] === 'success') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @elseif($event['variant'] === 'warning') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200 @else bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200 @endif">
                                                    {{ $event['type'] }}
                                                </span>
                                                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $event['timestamp'] }}</span>
                                            </div>
                                            <p class="text-sm text-zinc-900 dark:text-zinc-100 break-words">{{ $event['message'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </flux:card>
            </div>
            @endif
        </div>
    </x-intranet-app-hwro::hwro-layout>
</section>
