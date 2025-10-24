<?php

use Flux\Flux;
use Hwkdo\BueLaravel\BueLaravel;
use Hwkdo\IntranetAppHwro\Models\Vorgang;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, title, computed, mount};

title('Vorgänge - Handwerksrolle Online');

state([
    'filter' => 'alle',
    'search' => '',
    'gefundeneZuordnungen' => [],
    'showResultModal' => false,
    'sortBy' => 'vorgangsnummer',
    'sortDirection' => 'asc',
]);

mount(function () {
    // Lade den Default-Filter aus den User-Settings
    $user = Auth::user();
    $hwroSettings = $user->settings->app->hwro;
    
    if ($hwroSettings && isset($hwroSettings->defaultVorgaengeFilter)) {
        $this->filter = $hwroSettings->defaultVorgaengeFilter->value;
    }
});

$vorgaenge = computed(function () {
    return Vorgang::query()
        ->when($this->filter === 'mit_betrieb', fn($query) => $query->whereNotNull('betriebsnr'))
        ->when($this->filter === 'ohne_betrieb', fn($query) => $query->whereNull('betriebsnr'))
        ->when($this->search, fn($query) => $query->where(function ($q) {
            $q->where('vorgangsnummer', 'like', "%{$this->search}%")
              ->orWhere('betriebsnr', 'like', "%{$this->search}%");
        }))
        ->when($this->sortBy, fn($query) => $query->orderBy($this->sortBy, $this->sortDirection))
        ->paginate(15);
});

$setFilter = function (string $filter) {
    $this->filter = $filter;
};

$sort = function (string $column) {
    if ($this->sortBy === $column) {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        $this->sortBy = $column;
        $this->sortDirection = 'asc';
    }
};

$alleBetriebenrPruefen = function () {
    $this->gefundeneZuordnungen = [];
    $bueService = app(BueLaravel::class);
    
    // Hole alle aktuell angezeigten Vorgänge ohne Betriebsnummer
    $vorgaengeOhneBetriebsnr = Vorgang::query()
        ->when($this->filter === 'mit_betrieb', fn($query) => $query->whereNotNull('betriebsnr'))
        ->when($this->filter === 'ohne_betrieb', fn($query) => $query->whereNull('betriebsnr'))
        ->when($this->search, fn($query) => $query->where(function ($q) {
            $q->where('vorgangsnummer', 'like', "%{$this->search}%")
              ->orWhere('betriebsnr', 'like', "%{$this->search}%");
        }))
        ->whereNull('betriebsnr')
        ->get();
    
    foreach ($vorgaengeOhneBetriebsnr as $vorgang) {
        $betriebsnr = $bueService->getBetriebsnrByVorgangsnummer($vorgang->vorgangsnummer);
        
        if ($betriebsnr) {
            $this->gefundeneZuordnungen[] = [
                'vorgang_id' => $vorgang->id,
                'vorgangsnummer' => $vorgang->vorgangsnummer,
                'betriebsnr' => $betriebsnr,
            ];
        }
    }
    
    $this->showResultModal = true;
    
    if (empty($this->gefundeneZuordnungen)) {
        Flux::toast(text: 'Keine neuen Betriebsnummern gefunden', variant: 'info');
    }
};

$speichernZuordnungen = function () {
    $anzahl = 0;
    
    foreach ($this->gefundeneZuordnungen as $zuordnung) {
        $vorgang = Vorgang::find($zuordnung['vorgang_id']);
        if ($vorgang) {
            $vorgang->update(['betriebsnr' => $zuordnung['betriebsnr']]);
            $anzahl++;
        }
    }
    
    $this->gefundeneZuordnungen = [];
    $this->showResultModal = false;
    
    Flux::toast(text: "{$anzahl} Betriebsnummer(n) erfolgreich gespeichert!", variant: 'success');
};

$abbrechenZuordnungen = function () {
    $this->gefundeneZuordnungen = [];
    $this->showResultModal = false;
};

?>

<x-intranet-app-hwro::hwro-layout heading="Vorgänge" subheading="Verwalten Sie hier alle Vorgänge der Handwerksrolle">

        <div class="space-y-6">
            <div class="flex items-center justify-between gap-4">
                <flux:input 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Suchen nach Vorgangsnummer oder Betriebsnr..." 
                    class="w-full max-w-md"
                />
                
                <div class="flex gap-2">
                    <flux:button 
                        wire:click="alleBetriebenrPruefen" 
                        
                        icon="magnifying-glass"
                    >
                        Alle Betriebsnr prüfen
                    </flux:button>
                    
                    <flux:button 
                        :href="route('apps.hwro.vorgaenge.create')" 
                        wire:navigate 
                        variant="primary" 
                        icon="plus"
                    >
                        Neuer Vorgang
                    </flux:button>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <flux:button 
                    wire:click="setFilter('alle')" 
                    :variant="$filter === 'alle' ? 'primary' : 'ghost'"
                    size="sm"
                >
                    Alle
                </flux:button>
                <flux:button 
                    wire:click="setFilter('mit_betrieb')" 
                    :variant="$filter === 'mit_betrieb' ? 'primary' : 'ghost'"
                    size="sm"
                >
                    Nur mit Betrieb
                </flux:button>
                <flux:button 
                    wire:click="setFilter('ohne_betrieb')" 
                    :variant="$filter === 'ohne_betrieb' ? 'primary' : 'ghost'"
                    size="sm"
                >
                    Nur ohne Betrieb
                </flux:button>
            </div>

            <flux:table :paginate="$this->vorgaenge">
                <flux:table.columns>
                    <flux:table.column 
                        sortable 
                        :sorted="$sortBy === 'vorgangsnummer'" 
                        :direction="$sortDirection" 
                        wire:click="sort('vorgangsnummer')"
                    >
                        Vorgangsnummer
                    </flux:table.column>
                    <flux:table.column 
                        sortable 
                        :sorted="$sortBy === 'betriebsnr'" 
                        :direction="$sortDirection" 
                        wire:click="sort('betriebsnr')"
                    >
                        Betriebsnr
                    </flux:table.column>
                    <flux:table.column 
                        sortable 
                        :sorted="$sortBy === 'created_at'" 
                        :direction="$sortDirection" 
                        wire:click="sort('created_at')"
                    >
                        Erstellt
                    </flux:table.column>
                    <flux:table.column>Aktionen</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($this->vorgaenge as $vorgang)
                        <flux:table.row>
                            <flux:table.cell>{{ $vorgang->vorgangsnummer }}</flux:table.cell>
                            <flux:table.cell>
                                @if($vorgang->betriebsnr)
                                    {{ $vorgang->betriebsnr }}
                                @else
                                    <span class="text-zinc-400 dark:text-zinc-500">-</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>{{ $vorgang->created_at?->format('d.m.Y H:i') ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button 
                                        size="sm" 
                                        variant="ghost" 
                                        icon="eye"
                                        :href="route('apps.hwro.vorgaenge.show', $vorgang)"
                                        wire:navigate
                                    />
                                    <flux:button 
                                        size="sm" 
                                        variant="ghost" 
                                        icon="pencil"
                                    />
                                    <flux:button 
                                        size="sm" 
                                        variant="ghost" 
                                        icon="trash"
                                    />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>

        {{-- Modal für gefundene Betriebsnummern --}}
        <flux:modal wire:model="showResultModal" name="betriebsnr-results">
        <flux:heading size="lg" class="mb-4">Gefundene Betriebsnummern</flux:heading>
        
        @if(count($gefundeneZuordnungen) > 0)
            <flux:text class="mb-4">
                Es wurden <strong>{{ count($gefundeneZuordnungen) }}</strong> Betriebsnummer(n) gefunden:
            </flux:text>
            
            <div class="mb-6 max-h-96 overflow-y-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Vorgangsnummer</flux:table.column>
                        <flux:table.column>Gefundene Betriebsnr</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($gefundeneZuordnungen as $zuordnung)
                            <flux:table.row>
                                <flux:table.cell>{{ $zuordnung['vorgangsnummer'] }}</flux:table.cell>
                                <flux:table.cell>{{ $zuordnung['betriebsnr'] }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
            
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="abbrechenZuordnungen">
                    Abbrechen
                </flux:button>
                <flux:button variant="primary" wire:click="speichernZuordnungen">
                    Alle speichern
                </flux:button>
            </div>
        @else
            <flux:text class="mb-6">
                Es wurden keine neuen Betriebsnummern gefunden.
            </flux:text>
            
            <div class="flex justify-end">
                <flux:button variant="ghost" wire:click="abbrechenZuordnungen">
                    Schließen
                </flux:button>
            </div>
        @endif
        </flux:modal>
</x-intranet-app-hwro::hwro-layout>

