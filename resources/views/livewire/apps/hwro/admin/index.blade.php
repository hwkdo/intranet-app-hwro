<?php

use Flux\Flux;
use Hwkdo\IntranetAppHwro\Models\Schlagwort;

use function Livewire\Volt\computed;
use function Livewire\Volt\on;
use function Livewire\Volt\state;
use function Livewire\Volt\title;
use function Livewire\Volt\usesPagination;

usesPagination();

title('Admin - Handwerksrolle Online');

state([
    'schedulerEvents' => [],
    'activeTab' => 'einstellungen',
    'schlagwoerterSearch' => '',
    'schlagwoerterSortBy' => 'schlagwort',
    'schlagwoerterSortDirection' => 'asc',
    'editingSchlagwortId' => null,
    'schlagwortForm' => [
        'schlagwort' => '',
        'filenames' => [],
    ],
    'filenamesInput' => '',
]);

on([
    'echo:intranet-app-hwro-scheduler,.betriebsnr.search.started' => function ($event) {
        array_unshift($this->schedulerEvents, [
            'id' => uniqid().'-'.mt_rand(),
            'type' => 'Suche gestartet',
            'message' => $event['msg'] ?? '',
            'variant' => 'info',
            'timestamp' => now()->format('d.m.Y H:i:s'),
        ]);
        $this->schedulerEvents = array_slice($this->schedulerEvents, 0, 50);
    },
    'echo:intranet-app-hwro-scheduler,.betriebsnr.found' => function ($event) {
        array_unshift($this->schedulerEvents, [
            'id' => uniqid().'-'.mt_rand(),
            'type' => 'Betriebsnummer gefunden',
            'message' => $event['msg'] ?? '',
            'variant' => 'success',
            'timestamp' => now()->format('d.m.Y H:i:s'),
        ]);
        $this->schedulerEvents = array_slice($this->schedulerEvents, 0, 50);
    },
    'echo:intranet-app-hwro-scheduler,.betriebsnr.not.found' => function ($event) {
        array_unshift($this->schedulerEvents, [
            'id' => uniqid().'-'.mt_rand(),
            'type' => 'Betriebsnummer nicht gefunden',
            'message' => $event['msg'] ?? '',
            'variant' => 'warning',
            'timestamp' => now()->format('d.m.Y H:i:s'),
        ]);
        $this->schedulerEvents = array_slice($this->schedulerEvents, 0, 50);
    },
    'echo:intranet-app-hwro-scheduler,.betriebsnr.search.finished' => function ($event) {
        array_unshift($this->schedulerEvents, [
            'id' => uniqid().'-'.mt_rand(),
            'type' => 'Suche abgeschlossen',
            'message' => $event['msg'] ?? '',
            'variant' => 'neutral',
            'timestamp' => now()->format('d.m.Y H:i:s'),
        ]);
        $this->schedulerEvents = array_slice($this->schedulerEvents, 0, 50);
    },
    'echo:intranet-app-hwro-scheduler,.betriebsakte.make.started' => function ($event) {
        array_unshift($this->schedulerEvents, [
            'id' => uniqid().'-'.mt_rand(),
            'type' => 'Betriebsakte erstellung gestartet',
            'message' => $event['msg'] ?? '',
            'variant' => 'info',
            'timestamp' => now()->format('d.m.Y H:i:s'),
        ]);
        $this->schedulerEvents = array_slice($this->schedulerEvents, 0, 50);
    },
    'echo:intranet-app-hwro-scheduler,.betriebsakte.make.finished' => function ($event) {
        array_unshift($this->schedulerEvents, [
            'id' => uniqid().'-'.mt_rand(),
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

// Schlagwörter
$schlagwoerter = computed(function () {
    return Schlagwort::query()
        ->withCount('dokumente')
        ->when($this->schlagwoerterSearch, fn ($query) => $query->where('schlagwort', 'like', "%{$this->schlagwoerterSearch}%"))
        ->when($this->schlagwoerterSortBy, fn ($query) => $query->orderBy($this->schlagwoerterSortBy, $this->schlagwoerterSortDirection))
        ->paginate(15);
});

$sortSchlagwoerter = function (string $column) {
    if ($this->schlagwoerterSortBy === $column) {
        $this->schlagwoerterSortDirection = $this->schlagwoerterSortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        $this->schlagwoerterSortBy = $column;
        $this->schlagwoerterSortDirection = 'asc';
    }
};

$openCreateSchlagwortModal = function () {
    $this->activeTab = 'schlagwoerter';
    $this->reset('schlagwortForm', 'editingSchlagwortId', 'filenamesInput');
    Flux::modal('schlagwort-form')->show();
};

$openEditSchlagwortModal = function (Schlagwort $schlagwort) {
    $this->activeTab = 'schlagwoerter';
    $this->editingSchlagwortId = $schlagwort->id;
    $this->schlagwortForm = [
        'schlagwort' => $schlagwort->schlagwort,
        'filenames' => $schlagwort->filenames ?? [],
    ];
    $this->filenamesInput = is_array($schlagwort->filenames) ? implode("\n", $schlagwort->filenames) : '';
    Flux::modal('schlagwort-form')->show();
};

$saveSchlagwort = function () {
    $validated = $this->validate([
        'schlagwortForm.schlagwort' => 'required|string|max:255',
        'filenamesInput' => 'nullable|string',
    ]);

    // Parse filenames from textarea (one per line)
    $filenames = array_filter(
        array_map('trim', explode("\n", $this->filenamesInput)),
        fn ($line) => ! empty($line)
    );

    if ($this->editingSchlagwortId) {
        $schlagwort = Schlagwort::find($this->editingSchlagwortId);
        $schlagwort->update([
            'schlagwort' => $this->schlagwortForm['schlagwort'],
            'filenames' => $filenames,
        ]);

        Flux::toast(
            heading: 'Schlagwort aktualisiert',
            text: 'Das Schlagwort wurde erfolgreich aktualisiert.',
            variant: 'success'
        );
    } else {
        Schlagwort::create([
            'schlagwort' => $this->schlagwortForm['schlagwort'],
            'filenames' => $filenames,
        ]);

        Flux::toast(
            heading: 'Schlagwort erstellt',
            text: 'Das Schlagwort wurde erfolgreich erstellt.',
            variant: 'success'
        );
    }

    Flux::modal('schlagwort-form')->close();
    $this->reset('schlagwortForm', 'editingSchlagwortId', 'filenamesInput');
    unset($this->schlagwoerter);
};

$deleteSchlagwort = function (Schlagwort $schlagwort) {
    $schlagwort->delete();

    Flux::toast(
        heading: 'Schlagwort gelöscht',
        text: 'Das Schlagwort wurde erfolgreich gelöscht.',
        variant: 'success'
    );

    unset($this->schlagwoerter);
};

?>
<div>
<!-- Cache-Bust: {{ now()->timestamp }} -->
<x-intranet-app-hwro::hwro-layout heading="Admin" subheading="Verwaltung der Handwerksrolle">

        {{-- Flux Tabs --}}
        <flux:tab.group>
            <flux:tabs wire:model="activeTab">
                <flux:tab name="einstellungen" icon="cog-6-tooth">Einstellungen</flux:tab>
                <flux:tab name="scheduler" icon="clock">Scheduler</flux:tab>
                <flux:tab name="schlagwoerter" icon="tag">Schlagwörter</flux:tab>
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

            <flux:tab.panel name="schlagwoerter">
                <div style="min-height: 400px;">
                    <flux:card>
                        <div class="mb-4 flex items-center justify-between">
                            <flux:heading size="lg">Schlagwörter</flux:heading>
                            <flux:button wire:click="openCreateSchlagwortModal" variant="primary" icon="plus">
                                Neues Schlagwort
                            </flux:button>
                        </div>
                        
                        <flux:text class="mb-6">
                            Verwalten Sie Schlagwörter für Dokumente. Die Filenames werden später für die automatische Zuordnung verwendet.
                        </flux:text>

                        <div class="mb-4">
                            <flux:input 
                                wire:model.live.debounce.300ms="schlagwoerterSearch" 
                                placeholder="Suchen nach Schlagwort..." 
                                class="w-full max-w-md"
                            />
                        </div>

                        <flux:table :paginate="$this->schlagwoerter">
                            <flux:table.columns>
                                <flux:table.column 
                                    sortable 
                                    :sorted="$schlagwoerterSortBy === 'schlagwort'" 
                                    :direction="$schlagwoerterSortDirection" 
                                    wire:click="sortSchlagwoerter('schlagwort')"
                                >
                                    Schlagwort
                                </flux:table.column>
                                <flux:table.column>
                                    Filenames
                                </flux:table.column>
                                <flux:table.column>
                                    Dokumente
                                </flux:table.column>
                                <flux:table.column 
                                    sortable 
                                    :sorted="$schlagwoerterSortBy === 'created_at'" 
                                    :direction="$schlagwoerterSortDirection" 
                                    wire:click="sortSchlagwoerter('created_at')"
                                >
                                    Erstellt
                                </flux:table.column>
                                <flux:table.column>Aktionen</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach ($this->schlagwoerter as $schlagwort)
                                    <flux:table.row wire:key="schlagwort-{{ $schlagwort->id }}">
                                        <flux:table.cell>{{ $schlagwort->schlagwort }}</flux:table.cell>
                                        <flux:table.cell>
                                            @if($schlagwort->filenames && count($schlagwort->filenames) > 0)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($schlagwort->filenames as $filename)
                                                        <flux:badge size="sm" variant="outline">{{ $filename }}</flux:badge>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-zinc-400 dark:text-zinc-500">Keine</span>
                                            @endif
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <flux:badge>{{ $schlagwort->dokumente_count }}</flux:badge>
                                        </flux:table.cell>
                                        <flux:table.cell>{{ $schlagwort->created_at?->format('d.m.Y H:i') ?? '-' }}</flux:table.cell>
                                        <flux:table.cell>
                                            <div class="flex gap-2">
                                                <flux:button 
                                                    size="sm" 
                                                    variant="ghost" 
                                                    icon="pencil"
                                                    wire:click="openEditSchlagwortModal({{ $schlagwort->id }})"
                                                />
                                                <flux:button 
                                                    size="sm" 
                                                    variant="ghost" 
                                                    icon="trash"
                                                    wire:click="deleteSchlagwort({{ $schlagwort->id }})"
                                                    wire:confirm="Möchten Sie dieses Schlagwort wirklich löschen? Alle zugehörigen Dokumente werden ebenfalls gelöscht."
                                                />
                                            </div>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </flux:card>                    
                </div>
            </flux:tab.panel>
        </flux:tab.group>
</x-intranet-app-hwro::hwro-layout>
{{-- Modal für Create/Edit --}}
                    <flux:modal name="schlagwort-form" class="md:w-96">
                        <form wire:submit="saveSchlagwort" class="space-y-6">
                            <flux:heading size="lg">
                                {{ $editingSchlagwortId ? 'Schlagwort bearbeiten' : 'Neues Schlagwort' }}
                            </flux:heading>

                            <div class="space-y-4">
                                <flux:field>
                                    <flux:label>Schlagwort</flux:label>
                                    <flux:input 
                                        wire:model="schlagwortForm.schlagwort" 
                                        placeholder="z.B. Antrag auf Eintragung"
                                        required
                                    />
                                    <flux:error name="schlagwortForm.schlagwort" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>Filenames (ein Filename pro Zeile)</flux:label>
                                    <flux:textarea 
                                        wire:model="filenamesInput" 
                                        placeholder="filename1.pdf&#10;filename2.pdf&#10;pattern*.pdf"
                                        rows="5"
                                    />
                                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                        Geben Sie Filenames ein, die später für die automatische Zuordnung verwendet werden. Ein Filename pro Zeile.
                                    </flux:text>
                                    <flux:error name="filenamesInput" />
                                </flux:field>
                            </div>

                            <div class="flex justify-end gap-2">
                                <flux:modal.close>
                                    <flux:button type="button" variant="ghost">
                                        Abbrechen
                                    </flux:button>
                                </flux:modal.close>
                                <flux:button type="submit" variant="primary">
                                    {{ $editingSchlagwortId ? 'Aktualisieren' : 'Erstellen' }}
                                </flux:button>
                            </div>
                        </form>
                    </flux:modal>
                </div>