<?php

use Hwkdo\IntranetAppHwro\Models\Vorgang;
use function Livewire\Volt\{state, title, computed};

title('Vorgänge - Handwerksrolle Online');

state([
    'filter' => 'alle',
    'search' => '',
]);

$vorgaenge = computed(function () {
    return Vorgang::query()
        ->when($this->filter === 'mit_betrieb', fn($query) => $query->whereNotNull('betriebsnr'))
        ->when($this->filter === 'ohne_betrieb', fn($query) => $query->whereNull('betriebsnr'))
        ->when($this->search, fn($query) => $query->where(function ($q) {
            $q->where('vorgangsnummer', 'like', "%{$this->search}%")
              ->orWhere('betriebsnr', 'like', "%{$this->search}%");
        }))
        ->orderBy('vorgangsnummer')
        ->paginate(15);
});

$setFilter = function (string $filter) {
    $this->filter = $filter;
};

?>
<section class="w-full">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Handwerksrolle Online</flux:heading>
        <flux:subheading size="lg" class="mb-6">Verwaltung der Handwerksrolle</flux:subheading>
        <flux:separator variant="subtle" />
    </div>
    
    <x-intranet-app-hwro::hwro-layout>
        <x-slot:heading>Vorgänge</x-slot:heading>
        <x-slot:subheading>Verwalten Sie hier alle Vorgänge der Handwerksrolle</x-slot:subheading>
        
        <x-slot:navigation>
            <flux:navlist.item :href="route('apps.hwro.index')" wire:navigate>Übersicht</flux:navlist.item>
            <flux:navlist.item :href="route('apps.hwro.vorgaenge.index')" wire:navigate current>Vorgänge</flux:navlist.item>
        </x-slot:navigation>

        <div class="space-y-6">
            <div class="flex items-center justify-between gap-4">
                <flux:input 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Suchen nach Vorgangsnummer oder Betriebsnr..." 
                    class="w-full max-w-md"
                />
                
                <flux:button 
                    :href="route('apps.hwro.vorgaenge.create')" 
                    wire:navigate 
                    variant="primary" 
                    icon="plus"
                >
                    Neuer Vorgang
                </flux:button>
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
                    <flux:table.column>Vorgangsnummer</flux:table.column>
                    <flux:table.column>Betriebsnr</flux:table.column>
                    <flux:table.column>Erstellt</flux:table.column>
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
    </x-intranet-app-hwro::hwro-layout>
</section>

