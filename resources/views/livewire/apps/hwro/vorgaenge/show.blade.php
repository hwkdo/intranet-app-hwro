<?php

use Flux\Flux;
use Hwkdo\BueLaravel\BueLaravel;
use Hwkdo\D3RestLaravel\Client;
use Hwkdo\D3RestLaravel\Enums\DocTypeEnum;
use Hwkdo\IntranetAppHwro\Models\Vorgang;

use function Livewire\Volt\computed;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

state([
    'vorgang',
    'gefundeneBetriebsnr' => null,
    'showModal' => false,
]);

mount(function (Vorgang $vorgang) {
    $this->vorgang = $vorgang;

    $this->title = 'Vorgang '.$vorgang->vorgangsnummer.' - Handwerksrolle Online';
});

$dokumente = computed(function () {
    $d3Client = app(Client::class);

    return $d3Client->SearchResult(
        fulltext: $this->vorgang->vorgangsnummer,
        doc_type: DocTypeEnum::HandwerksrolleOnline,
        raw: false
    );
});

$betriebsakteDokumente = computed(function () {
    if (! $this->vorgang->betriebsnr) {
        return collect([]);
    }

    $d3Client = app(Client::class);

    return $d3Client->SearchResult(
        fulltext: $this->vorgang->betriebsnr,
        doc_type: DocTypeEnum::Handwerksrolle,
        raw: false
    );
});

$betriebsdaten = computed(function () {
    if (! $this->vorgang->betriebsnr) {
        return null;
    }

    $bueService = app(BueLaravel::class);

    return $bueService->getBetriebByBetriebsnr($this->vorgang->betriebsnr);
});

$pruefen = function () {
    $bueService = app(BueLaravel::class);
    $result = $bueService->getBetriebsnrByVorgangsnummer($this->vorgang->vorgangsnummer);

    if ($result) {
        $this->gefundeneBetriebsnr = $result;
        $this->showModal = true;
    } else {
        $this->gefundeneBetriebsnr = null;
        $this->showModal = false;
        Flux::toast(text: 'Keine Betriebsnr gefunden', variant: 'warning');
    }
};

$speichernBetriebsnr = function () {
    if ($this->gefundeneBetriebsnr) {
        $this->vorgang->update(['betriebsnr' => $this->gefundeneBetriebsnr]);
        $this->vorgang->refresh();
        $this->gefundeneBetriebsnr = null;
        $this->showModal = false;

        Flux::toast(text: 'Betriebsnummer erfolgreich gespeichert!', variant: 'success');
    }
};

$abbrechenBetriebsnr = function () {
    $this->gefundeneBetriebsnr = null;
    $this->showModal = false;
};

$uebertragenAusOnlineEintragung = function () {
    $result = $this->vorgang->makeD3Betriebsakte();
    
    if ($result && $result['success']) {
        // Invalidiere das computed property, damit es beim nächsten Zugriff neu geladen wird
        unset($this->betriebsakteDokumente);
        
        Flux::toast(
            text: 'Dokumente erfolgreich in die Betriebsakte übertragen!',
            variant: 'success'
        );
    } elseif ($result) {
        Flux::toast(
            text: 'Fehler beim Übertragen: ' . ($result['message'] ?? 'Unbekannter Fehler'),
            variant: 'danger'
        );
    } else {
        Flux::toast(
            text: 'Keine Dokumente zum Übertragen gefunden.',
            variant: 'warning'
        );
    }
};

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
            <flux:navlist.item :href="route('apps.hwro.settings.user')" wire:navigate>Meine Einstellungen</flux:navlist.item>
            @can('manage-app-hwro')
                <flux:navlist.item :href="route('apps.hwro.admin.index')" wire:navigate>Admin</flux:navlist.item>
            @endcan
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
                        @if($vorgang->betriebsnr)
                            <flux:text class="mt-1 text-lg font-semibold">{{ $vorgang->betriebsnr }}</flux:text>
                        @else
                            <div class="mt-1">
                                <flux:button 
                                    size="sm" 
                                    variant="primary"
                                    wire:click="pruefen"
                                >
                                    Prüfen
                                </flux:button>
                            </div>
                        @endif
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

            @if($vorgang->betriebsnr)
                <div class="grid gap-6 md:grid-cols-2">
                    <flux:card>
                        <flux:heading size="lg" class="mb-4">D3 Dokumente Betriebsakte</flux:heading>
                        
                        @if($this->betriebsakteDokumente->isEmpty())
                            <div class="space-y-4">
                                <flux:text class="text-zinc-500 dark:text-zinc-400">
                                    Keine Dokumente gefunden.
                                </flux:text>
                                
                                @if(!$this->dokumente->isEmpty())
                                    <flux:button 
                                        variant="primary"
                                        icon="arrow-path"
                                        wire:click="uebertragenAusOnlineEintragung"
                                    >
                                        Übertragen aus Online Eintragung
                                    </flux:button>
                                @endif
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($this->betriebsakteDokumente as $dokument)
                                    <div class="flex items-center justify-between rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                                        <div class="flex items-center gap-3">
                                            <flux:icon name="document" class="size-6 text-zinc-500 dark:text-zinc-400" />
                                            <div>
                                                <flux:text class="font-medium">{{ $dokument->filename }}</flux:text>
                                                @if(isset($dokument->Belegdatum) && $dokument->Belegdatum)
                                                    <flux:text size="sm" class="text-zinc-500">
                                                        Belegdatum: {{ $dokument->Belegdatum }}
                                                    </flux:text>
                                                @endif
                                                @if(isset($dokument->Schlagwort) && $dokument->Schlagwort)
                                                    <flux:text size="sm" class="text-zinc-500">
                                                        Schlagwort: {{ $dokument->Schlagwort }}
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

                    <flux:card>
                        <flux:heading size="lg" class="mb-4">BuE Betriebsdaten</flux:heading>
                        
                        @if($this->betriebsdaten)
                            <div class="space-y-4">
                                <div>
                                    <flux:label>Name</flux:label>
                                    <flux:text class="mt-1 text-lg font-semibold">{{ $this->betriebsdaten->name ?? '-' }}</flux:text>
                                </div>
                                
                                <div>
                                    <flux:label>Eintragungsdatum</flux:label>
                                    <flux:text class="mt-1">{{ $this->betriebsdaten->edat ?? '-' }}</flux:text>
                                </div>
                                
                                <div>
                                    <flux:label>Betriebsart</flux:label>
                                    <flux:text class="mt-1">{{ $this->betriebsdaten->betriebsart ?? '-' }}</flux:text>
                                </div>
                            </div>
                        @else
                            <flux:text class="text-zinc-500 dark:text-zinc-400">
                                Keine Betriebsdaten gefunden.
                            </flux:text>
                        @endif
                    </flux:card>
                </div>
            @endif
        </div>
    </x-intranet-app-hwro::hwro-layout>

    {{-- Modal für Betriebsnummer-Bestätigung --}}
    <flux:modal wire:model="showModal" name="betriebsnr-confirm">
        <flux:heading size="lg" class="mb-4">Betriebsnummer gefunden</flux:heading>
        
        <flux:text class="mb-6">
            Es wurde die Betriebsnummer <strong>{{ $gefundeneBetriebsnr }}</strong> gefunden. 
            Möchten Sie diese dem Vorgang zuweisen?
        </flux:text>
        
        <div class="flex justify-end gap-2">
            <flux:button variant="ghost" wire:click="abbrechenBetriebsnr">
                Nein
            </flux:button>
            <flux:button variant="primary" wire:click="speichernBetriebsnr">
                Ja, speichern
            </flux:button>
        </div>
    </flux:modal>
</section>

