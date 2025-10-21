<?php

use Hwkdo\D3RestLaravel\Client;
use Hwkdo\D3RestLaravel\Enums\DocTypeEnum;
use Hwkdo\IntranetAppHwro\Models\Vorgang;
use function Livewire\Volt\{state, title, mount, computed};

state(['vorgang']);

mount(function (Vorgang $vorgang) {
    $this->vorgang = $vorgang;
    
    $this->title = 'Vorgang ' . $vorgang->vorgangsnummer . ' - Handwerksrolle Online';
});

$dokumente = computed(function () {
    $d3Client = app(Client::class);
    
    return $d3Client->SearchResult(
        fulltext: $this->vorgang->vorgangsnummer,
        doc_type: DocTypeEnum::HandwerksrolleOnline,
        raw: false
    );
});

?>
<section class="w-full">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Handwerksrolle Online</flux:heading>
        <flux:subheading size="lg" class="mb-6">Verwaltung der Handwerksrolle</flux:subheading>
        <flux:separator variant="subtle" />
    </div>
    
    <x-intranet-app-hwro::hwro-layout>
        <x-slot:heading>Vorgang anzeigen</x-slot:heading>
        <x-slot:subheading>Details zum Vorgang {{ $vorgang->vorgangsnummer }}</x-slot:subheading>
        
        <x-slot:navigation>
            <flux:navlist.item :href="route('apps.hwro.index')" wire:navigate>Übersicht</flux:navlist.item>
            <flux:navlist.item :href="route('apps.hwro.vorgaenge.index')" wire:navigate>Vorgänge</flux:navlist.item>
        </x-slot:navigation>

        <div class="space-y-6">
            <flux:card>
                <div class="mb-6 flex items-center justify-between">
                    <flux:heading size="lg">Vorgangsdetails</flux:heading>
                    <flux:button 
                        :href="route('apps.hwro.vorgaenge.index')" 
                        wire:navigate 
                        variant="ghost"
                        icon="arrow-left"
                    >
                        Zurück zur Liste
                    </flux:button>
                </div>
                
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <flux:label>Vorgangsnummer</flux:label>
                        <flux:text class="mt-1 text-lg font-semibold">{{ $vorgang->vorgangsnummer }}</flux:text>
                    </div>
                    
                    <div>
                        <flux:label>Betriebsnummer</flux:label>
                        <flux:text class="mt-1 text-lg font-semibold">
                            @if($vorgang->betriebsnr)
                                {{ $vorgang->betriebsnr }}
                            @else
                                <span class="text-zinc-400 dark:text-zinc-500">Nicht zugewiesen</span>
                            @endif
                        </flux:text>
                    </div>
                    
                    <div>
                        <flux:label>Erstellt am</flux:label>
                        <flux:text class="mt-1">{{ $vorgang->created_at?->format('d.m.Y H:i') ?? '-' }}</flux:text>
                    </div>
                    
                    <div>
                        <flux:label>Aktualisiert am</flux:label>
                        <flux:text class="mt-1">{{ $vorgang->updated_at?->format('d.m.Y H:i') ?? '-' }}</flux:text>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <flux:heading size="lg" class="mb-4">D3 Dokumente Online Eintragung</flux:heading>
                
                @if($this->dokumente->isEmpty())
                    <flux:text class="text-zinc-500 dark:text-zinc-400">
                        Keine Dokumente gefunden.
                    </flux:text>
                @else
                    <div class="space-y-3">
                        @foreach($this->dokumente as $dokument)
                            <div class="flex items-center justify-between rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                                <div class="flex items-center gap-3">
                                    <flux:icon name="document" class="size-6 text-zinc-500 dark:text-zinc-400" />
                                    <div>
                                        <flux:text class="font-medium">{{ $dokument->filename }}</flux:text>
                                        @if($dokument->datum)
                                            <flux:text size="sm" class="text-zinc-500">
                                                Datum: {{ $dokument->datum }}
                                            </flux:text>
                                        @endif
                                    </div>
                                </div>
                                <flux:button 
                                    size="sm" 
                                    variant="primary"
                                    icon="arrow-top-right-on-square"
                                    href="{{ $dokument->link }}"
                                    target="_blank"
                                >
                                    Öffnen
                                </flux:button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </flux:card>
        </div>
    </x-intranet-app-hwro::hwro-layout>
</section>

