<?php

use Flux\Flux;

use function Livewire\Volt\{state, title, on};

title('Admin - Handwerksrolle Online');

state([
    'schedulerEvents' => [],
    'activeTab' => 'einstellungen',
]);

on([
    'echo:intranet-app-hwro-scheduler,.betriebsnr.search.started' => function ($event) {
        array_unshift($this->schedulerEvents, [
            'id' => uniqid() . '-' . mt_rand(),
            'type' => 'Suche gestartet',
            'message' => $event['msg'] ?? '',
            'variant' => 'info',
            'timestamp' => now()->format('d.m.Y H:i:s'),
        ]);
        $this->schedulerEvents = array_slice($this->schedulerEvents, 0, 50);
    },
    'echo:intranet-app-hwro-scheduler,.betriebsnr.found' => function ($event) {
        array_unshift($this->schedulerEvents, [
            'id' => uniqid() . '-' . mt_rand(),
            'type' => 'Betriebsnummer gefunden',
            'message' => $event['msg'] ?? '',
            'variant' => 'success',
            'timestamp' => now()->format('d.m.Y H:i:s'),
        ]);
        $this->schedulerEvents = array_slice($this->schedulerEvents, 0, 50);
    },
    'echo:intranet-app-hwro-scheduler,.betriebsnr.not.found' => function ($event) {
        array_unshift($this->schedulerEvents, [
            'id' => uniqid() . '-' . mt_rand(),
            'type' => 'Betriebsnummer nicht gefunden',
            'message' => $event['msg'] ?? '',
            'variant' => 'warning',
            'timestamp' => now()->format('d.m.Y H:i:s'),
        ]);
        $this->schedulerEvents = array_slice($this->schedulerEvents, 0, 50);
    },
    'echo:intranet-app-hwro-scheduler,.betriebsnr.search.finished' => function ($event) {
        array_unshift($this->schedulerEvents, [
            'id' => uniqid() . '-' . mt_rand(),
            'type' => 'Suche abgeschlossen',
            'message' => $event['msg'] ?? '',
            'variant' => 'neutral',
            'timestamp' => now()->format('d.m.Y H:i:s'),
        ]);
        $this->schedulerEvents = array_slice($this->schedulerEvents, 0, 50);
    },
    'echo:intranet-app-hwro-scheduler,.betriebsakte.make.started' => function ($event) {
        array_unshift($this->schedulerEvents, [
            'id' => uniqid() . '-' . mt_rand(),
            'type' => 'Betriebsakte erstellung gestartet',
            'message' => $event['msg'] ?? '',
            'variant' => 'info',
            'timestamp' => now()->format('d.m.Y H:i:s'),
        ]);
        $this->schedulerEvents = array_slice($this->schedulerEvents, 0, 50);
    },
    'echo:intranet-app-hwro-scheduler,.betriebsakte.make.finished' => function ($event) {
        array_unshift($this->schedulerEvents, [
            'id' => uniqid() . '-' . mt_rand(),
            'type' => 'Betriebsakte erstellung abgeschlossen',
            'message' => $event['msg'] ?? '',
            'variant' => 'success',
            'timestamp' => now()->format('d.m.Y H:i:s'),
        ]);
        $this->schedulerEvents = array_slice($this->schedulerEvents, 0, 50);
    },
]);


$clearSchedulerEvents = function () {
    $this->schedulerEvents = [];
};

$runSearchBetriebsnr = function () {
    \Illuminate\Support\Facades\Artisan::call('intranet-app-hwro:search-betriebsnr');
    
    Flux::toast(
        heading: 'Command gestartet',
        text: 'Der Betriebsnummern-Suchvorgang wurde gestartet.',
        variant: 'success'
    );
};

$runMakeBetriebsakte = function () {
    \Illuminate\Support\Facades\Artisan::call('intranet-app-hwro:make-betriebsakte');
    
    Flux::toast(
        heading: 'Command gestartet',
        text: 'Die Betriebsakten-Erstellung wurde gestartet.',
        variant: 'success'
    );
};

?>

<!-- Cache-Bust: {{ now()->timestamp }} -->
<x-intranet-app-hwro::hwro-layout heading="Admin" subheading="Verwaltung der Handwerksrolle">

        {{-- Flux Tabs --}}
        <flux:tab.group>
            <flux:tabs wire:model="activeTab">
                <flux:tab name="einstellungen" icon="cog-6-tooth">Einstellungen</flux:tab>
                <flux:tab name="scheduler" icon="clock">Scheduler</flux:tab>
            </flux:tabs>
            
        <flux:tab.panel name="einstellungen">
            <div style="min-height: 400px;">
                @livewire('intranet-app-base::admin-settings', [
                    'appIdentifier' => 'hwro',
                    'settingsModelClass' => '\Hwkdo\IntranetAppHwro\Models\IntranetAppHwroSettings',
                    'appSettingsClass' => '\Hwkdo\IntranetAppHwro\Data\AppSettings'
                ])
            </div>
        </flux:tab.panel>

            <flux:tab.panel name="scheduler">
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
                        
                        <flux:text class="mb-6">
                            Live-Übersicht der Scheduler-Events.
                        </flux:text>

                        <div class="mb-6 flex items-center gap-4">
                            <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Scheduler manuell ausführen:
                            </flux:text>
                            <div class="flex gap-2">
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
                        </div>

                        <flux:separator variant="subtle" class="mb-4" />

                        <div class="space-y-2" wire:poll.visible>
                            @if(count($schedulerEvents) === 0)
                                <div class="rounded-lg border border-dashed border-zinc-300 dark:border-zinc-700 p-8 text-center">
                                    <flux:text>
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
            </flux:tab.panel>
        </flux:tab.group>
</x-intranet-app-hwro::hwro-layout>
