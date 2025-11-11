<?php

use Flux\Flux;
use Hwkdo\IntranetAppHwro\Models\Dokument;
use Hwkdo\IntranetAppHwro\Models\Schlagwort;
use Hwkdo\IntranetAppHwro\Models\Vorgang;
use Livewire\WithFileUploads;
use function Livewire\Volt\{state, title, computed, mount, uses, rules};

uses([WithFileUploads::class]);

state([
    'dokument',
    'vorgang_id',
    'schlagwort_id',
    'datei' => null,
]);

title(fn() => 'Dokument bearbeiten: ' . $this->dokument->schlagwort?->schlagwort);

mount(function (Dokument $dokument) {
    $this->dokument = $dokument->load(['vorgang', 'schlagwort', 'media']);
    $this->vorgang_id = $dokument->vorgang_id;
    $this->schlagwort_id = $dokument->schlagwort_id;
});

rules([
    'vorgang_id' => 'required|exists:intranet_app_hwro_vorgangs,id',
    'schlagwort_id' => 'required|exists:intranet_app_hwro_schlagworts,id',
    'datei' => 'nullable|file|max:10240', // max 10MB
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
    
    $this->dokument->update([
        'vorgang_id' => $this->vorgang_id,
        'schlagwort_id' => $this->schlagwort_id,
    ]);
    
    // Wenn eine neue Datei hochgeladen wurde, ersetze die alte
    if ($this->datei) {
        $schlagwort = Schlagwort::find($this->schlagwort_id);
        $this->dokument->clearMediaCollection('default');
        $this->dokument->addMedia($this->datei->getRealPath())
            ->usingName($schlagwort->schlagwort)
            ->usingFileName($this->datei->getClientOriginalName())
            ->toMediaCollection('default');
    }
    
    Flux::toast(text: 'Dokument erfolgreich aktualisiert!', variant: 'success');
    
    $this->redirect(route('apps.hwro.dokumente.show', $this->dokument));
};

$cancel = function () {
    $this->redirect(route('apps.hwro.dokumente.show', $this->dokument));
};

?>
<div>
<x-intranet-app-hwro::hwro-layout heading="Dokument bearbeiten" subheading="Bearbeiten Sie die Dokument-Details">

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
            <flux:label>Datei ersetzen (optional)</flux:label>
            
            @if($dokument->hasMedia())
                <div class="mb-2 p-3 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                    <flux:text class="text-sm">
                        Aktuelle Datei: {{ $dokument->getFirstMedia()->file_name }}
                    </flux:text>
                </div>
            @endif
            
            <input 
                type="file" 
                wire:model="datei" 
                class="block w-full text-sm text-zinc-900 border border-zinc-300 rounded-lg cursor-pointer bg-zinc-50 dark:text-zinc-400 focus:outline-none dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400"
            />
            <flux:error name="datei" />
            
            @if ($datei)
                <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    Neue Datei: {{ $datei->getClientOriginalName() }}
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

