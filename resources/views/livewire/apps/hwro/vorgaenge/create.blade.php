<?php

use Flux\Flux;
use Hwkdo\IntranetAppHwro\Models\Vorgang;
use function Livewire\Volt\{state, title, rules};

title('Vorgang erstellen');

state([
    'vorgangsnummer' => '',
    'betriebsnr' => '',
]);

rules([
    'vorgangsnummer' => 'required|integer',
    'betriebsnr' => 'nullable|integer',
]);

$save = function () {
    $validated = $this->validate();
    
    // Konvertiere leere Strings zu null
    if (empty($validated['betriebsnr'])) {
        $validated['betriebsnr'] = null;
    }
    
    Vorgang::create($validated);
    
    Flux::toast(text: 'Vorgang erfolgreich erstellt!', variant: 'success');
    
    $this->redirect(route('apps.hwro.vorgaenge.index'), navigate: true);
};

?>
<section class="w-full">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Handwerksrolle Online</flux:heading>
        <flux:subheading size="lg" class="mb-6">Verwaltung der Handwerksrolle</flux:subheading>
        <flux:separator variant="subtle" />
    </div>
    
    <x-intranet-app-hwro::hwro-layout>
        <x-slot:heading>Neuer Vorgang</x-slot:heading>
        <x-slot:subheading>Erstellen Sie einen neuen Vorgang</x-slot:subheading>

        <flux:card>
            <flux:heading size="lg" class="mb-6">Neuer Vorgang</flux:heading>
            
            <form wire:submit="save" class="space-y-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <flux:field>
                        <flux:label>Vorgangsnummer *</flux:label>
                        <flux:input wire:model="vorgangsnummer" type="number" />
                        <flux:error name="vorgangsnummer" />
                    </flux:field>
                    
                    <flux:field>
                        <flux:label>Betriebsnummer</flux:label>
                        <flux:input wire:model="betriebsnr" type="number" />
                        <flux:error name="betriebsnr" />
                        <flux:description>Optional - kann auch später hinzugefügt werden</flux:description>
                    </flux:field>
                </div>
                
                <div class="flex justify-end gap-2">
                    <flux:button variant="ghost" :href="route('apps.hwro.vorgaenge.index')" wire:navigate>
                        Abbrechen
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        Speichern
                    </flux:button>
                </div>
            </form>
        </flux:card>
    </x-intranet-app-hwro::hwro-layout>
</section>

