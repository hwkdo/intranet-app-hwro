<?php

use Flux\Flux;
use Hwkdo\IntranetAppHwro\Models\Dokument;
use Hwkdo\IntranetAppHwro\Models\Schlagwort;
use Hwkdo\IntranetAppHwro\Models\Vorgang;
use Livewire\WithFileUploads;
use function Livewire\Volt\{state, title, computed, uses, rules};

uses([WithFileUploads::class]);

title('Neues Dokument - Handwerksrolle Online');

state([
    'vorgang_id' => null,
    'schlagwort_id' => null,
    'datei' => null,
]);

rules([
    'vorgang_id' => 'required|exists:intranet_app_hwro_vorgangs,id',
    'schlagwort_id' => 'required|exists:intranet_app_hwro_schlagworts,id',
    'datei' => 'required|file|max:10240', // max 10MB
]);

$vorgaenge = computed(function () {
    return Vorgang::query()
        ->orderBy('vorgangsnummer', 'desc')
        ->get()
        ->mapWithKeys(fn($vorgang) => [$vorgang->id => $vorgang->vorgangsnummer]);
});

$schlagworter = computed(function () {
    return Schlagwort::query()
        ->orderBy('schlagwort')
        ->get()
        ->mapWithKeys(fn($schlagwort) => [$schlagwort->id => $schlagwort->schlagwort]);
});

$save = function () {
    $this->validate();
    
    $dokument = Dokument::create([
        'vorgang_id' => $this->vorgang_id,
        'schlagwort_id' => $this->schlagwort_id,
    ]);
    
    if ($this->datei) {
        $schlagwort = Schlagwort::find($this->schlagwort_id);
        
        // Dateiname immer mit .pdf Endung, da Konvertierung erfolgt
        $originalName = $this->datei->getClientOriginalName();
        $pdfName = pathinfo($originalName, PATHINFO_FILENAME) . '.pdf';
        
        $dokument->addMediaWithPdfConversion($this->datei->getRealPath())
            ->usingName($schlagwort->schlagwort)
            ->usingFileName($pdfName)
            ->toMediaCollection('default');
    }
    
    Flux::toast(text: 'Dokument erfolgreich erstellt!', variant: 'success');
    
    $this->redirect(route('apps.hwro.dokumente.index'));
};

$cancel = function () {
    $this->redirect(route('apps.hwro.dokumente.index'));
};

?>
<div>
<x-intranet-app-hwro::hwro-layout heading="Neues Dokument" subheading="Erstellen Sie ein neues Dokument">

    <form wire:submit="save" class="space-y-6">
        <flux:field>
            <flux:label>Vorgang</flux:label>
            <flux:select wire:model="vorgang_id" variant="listbox" searchable placeholder="Vorgang auswählen...">
                @foreach($this->vorgaenge as $id => $vorgangsnummer)
                    <flux:select.option value="{{ $id }}">{{ $vorgangsnummer }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="vorgang_id" />
        </flux:field>

        <flux:field>
            <flux:label>Schlagwort</flux:label>
            <flux:select wire:model="schlagwort_id" variant="listbox" searchable placeholder="Schlagwort auswählen...">
                @foreach($this->schlagworter as $id => $schlagwort)
                    <flux:select.option value="{{ $id }}">{{ $schlagwort }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="schlagwort_id" />
        </flux:field>

        <flux:field>
            <flux:label>Datei</flux:label>
            <input 
                type="file" 
                wire:model="datei" 
                class="block w-full text-sm text-zinc-900 border border-zinc-300 rounded-lg cursor-pointer bg-zinc-50 dark:text-zinc-400 focus:outline-none dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400"
            />
            <flux:error name="datei" />
            
            @if ($datei)
                <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    Ausgewählte Datei: {{ $datei->getClientOriginalName() }}
                </div>
            @endif
            
            <div wire:loading wire:target="datei" class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                Datei wird hochgeladen...
            </div>
        </flux:field>

        <div class="flex gap-2">
            <flux:button type="submit" variant="primary" icon="check">
                Speichern
            </flux:button>
            <flux:button type="button" variant="ghost" wire:click="cancel">
                Abbrechen
            </flux:button>
        </div>
    </form>

</x-intranet-app-hwro::hwro-layout>
</div>

