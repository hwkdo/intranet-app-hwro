<?php

use Flux\Flux;
use Hwkdo\IntranetAppHwro\Models\Dokument;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, title, computed, usesPagination};

usesPagination();

title('Dokumente - Handwerksrolle Online');

state([
    'search' => '',
    'sortBy' => 'created_at',
    'sortDirection' => 'desc',
]);

$dokumente = computed(function () {
    return Dokument::query()
        ->with(['vorgang', 'media', 'schlagwort'])
        ->when($this->search, fn($query) => $query->where(function ($q) {
            $q->whereHas('schlagwort', fn($query) => $query->where('schlagwort', 'like', "%{$this->search}%"))
              ->orWhereHas('vorgang', fn($query) => $query->where('vorgangsnummer', 'like', "%{$this->search}%"));
        }))
        ->when($this->sortBy, fn($query) => $query->orderBy($this->sortBy, $this->sortDirection))
        ->paginate(15);
});

$sort = function (string $column) {
    if ($this->sortBy === $column) {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        $this->sortBy = $column;
        $this->sortDirection = 'asc';
    }
};

$deleteDokument = function (Dokument $dokument) {
    $dokument->clearMediaCollection();
    $dokument->delete();
    
    Flux::toast(text: 'Dokument erfolgreich gelöscht!', variant: 'success');
};

?>
<div>
<x-intranet-app-hwro::hwro-layout heading="Dokumente" subheading="Verwalten Sie hier alle Dokumente der Handwerksrolle">

        <div class="space-y-6">
            <div class="flex items-center justify-between gap-4">
                <flux:input 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Suchen nach Schlagwort oder Vorgangsnummer..." 
                    class="w-full max-w-md"
                />
                
                <flux:button 
                    :href="route('apps.hwro.dokumente.create')" 
                    wire:navigate 
                    variant="primary" 
                    icon="plus"
                >
                    Neues Dokument
                </flux:button>
            </div>

            <flux:table :paginate="$this->dokumente">
                <flux:table.columns>
                    <flux:table.column>
                        Schlagwort
                    </flux:table.column>
                    <flux:table.column>
                        Vorgang
                    </flux:table.column>
                    <flux:table.column>
                        Datei
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
                    @foreach ($this->dokumente as $dokument)
                        <flux:table.row>
                            <flux:table.cell>
                                @if($dokument->schlagwort)
                                    {{ $dokument->schlagwort->schlagwort }}
                                @else
                                    <span class="text-zinc-400 dark:text-zinc-500">-</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($dokument->vorgang)
                                    {{ $dokument->vorgang->vorgangsnummer }}
                                @else
                                    <span class="text-zinc-400 dark:text-zinc-500">-</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($dokument->hasMedia())
                                    {{ $dokument->getFirstMedia()->file_name }}
                                @else
                                    <span class="text-zinc-400 dark:text-zinc-500">Keine Datei</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>{{ $dokument->created_at?->format('d.m.Y H:i') ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button 
                                        size="sm" 
                                        variant="ghost" 
                                        icon="eye"
                                        :href="route('apps.hwro.dokumente.show', $dokument)"
                                        wire:navigate
                                    />
                                    <flux:button 
                                        size="sm" 
                                        variant="ghost" 
                                        icon="pencil"
                                        :href="route('apps.hwro.dokumente.edit', $dokument)"
                                        wire:navigate
                                    />
                                    <flux:button 
                                        size="sm" 
                                        variant="ghost" 
                                        icon="trash"
                                        wire:click="deleteDokument({{ $dokument->id }})"
                                        wire:confirm="Möchten Sie dieses Dokument wirklich löschen?"
                                    />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>

</x-intranet-app-hwro::hwro-layout>
</div>

