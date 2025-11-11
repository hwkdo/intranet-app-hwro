<?php

use Flux\Flux;
use Hwkdo\IntranetAppHwro\Models\Dokument;
use function Livewire\Volt\{state, title, mount};

state(['dokument']);

title(fn() => 'Dokument: ' . $this->dokument->schlagwort?->schlagwort);

mount(function (Dokument $dokument) {
    $this->dokument = $dokument->load(['vorgang', 'schlagwort', 'media']);
});

$deleteDokument = function () {
    $this->dokument->clearMediaCollection();
    $this->dokument->delete();
    
    Flux::toast(text: 'Dokument erfolgreich gelöscht!', variant: 'success');
    
    $this->redirect(route('apps.hwro.dokumente.index'));
};

$downloadFile = function () {
    if ($this->dokument->hasMedia()) {
        return response()->download($this->dokument->getFirstMedia()->getPath());
    }
    
    Flux::toast(text: 'Keine Datei vorhanden!', variant: 'error');
};

?>
<div>
<x-intranet-app-hwro::hwro-layout heading="Dokument Details" subheading="Ansicht des Dokuments">

    <div class="space-y-6">
        <flux:card>
            <div class="space-y-4">
                <div>
                    <flux:heading size="sm" class="mb-2">Schlagwort</flux:heading>
                    @if($dokument->schlagwort)
                        <flux:text>{{ $dokument->schlagwort->schlagwort }}</flux:text>
                    @else
                        <flux:text class="text-zinc-400 dark:text-zinc-500">Kein Schlagwort zugeordnet</flux:text>
                    @endif
                </div>

                <flux:separator />

                <div>
                    <flux:heading size="sm" class="mb-2">Vorgang</flux:heading>
                    @if($dokument->vorgang)
                        <flux:text>
                            Vorgangsnummer: {{ $dokument->vorgang->vorgangsnummer }}
                            @if($dokument->vorgang->betriebsnr)
                                <br>Betriebsnr: {{ $dokument->vorgang->betriebsnr }}
                            @endif
                        </flux:text>
                    @else
                        <flux:text class="text-zinc-400 dark:text-zinc-500">Kein Vorgang zugeordnet</flux:text>
                    @endif
                </div>

                <flux:separator />

                <div>
                    <flux:heading size="sm" class="mb-2">Datei</flux:heading>
                    @if($dokument->hasMedia())
                        @php
                            $media = $dokument->getFirstMedia();
                        @endphp
                        <div class="space-y-2">
                            <flux:text>
                                Dateiname: {{ $media->file_name }}<br>
                                Größe: {{ number_format($media->size / 1024, 2) }} KB<br>
                                Typ: {{ $media->mime_type }}
                            </flux:text>
                            <div>
                                <flux:button 
                                    variant="primary" 
                                    icon="arrow-down-tray"
                                    wire:click="downloadFile"
                                >
                                    Datei herunterladen
                                </flux:button>
                            </div>
                        </div>
                    @else
                        <flux:text class="text-zinc-400 dark:text-zinc-500">Keine Datei vorhanden</flux:text>
                    @endif
                </div>

                <flux:separator />

                <div>
                    <flux:heading size="sm" class="mb-2">Zeitstempel</flux:heading>
                    <flux:text>
                        Erstellt: {{ $dokument->created_at?->format('d.m.Y H:i') ?? '-' }}<br>
                        Aktualisiert: {{ $dokument->updated_at?->format('d.m.Y H:i') ?? '-' }}
                    </flux:text>
                </div>
            </div>
        </flux:card>

        <div class="flex gap-2">
            <flux:button 
                :href="route('apps.hwro.dokumente.edit', $dokument)" 
                wire:navigate 
                variant="primary" 
                icon="pencil"
            >
                Bearbeiten
            </flux:button>
            <flux:button 
                wire:click="deleteDokument" 
                variant="danger" 
                icon="trash"
                wire:confirm="Möchten Sie dieses Dokument wirklich löschen?"
            >
                Löschen
            </flux:button>
            <flux:button 
                :href="route('apps.hwro.dokumente.index')" 
                wire:navigate 
                variant="ghost"
            >
                Zurück zur Liste
            </flux:button>
        </div>
    </div>

</x-intranet-app-hwro::hwro-layout>
</div>

