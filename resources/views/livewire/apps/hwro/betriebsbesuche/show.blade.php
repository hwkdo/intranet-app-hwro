<?php

use Hwkdo\BueLaravel\BueLaravel;

use function Livewire\Volt\computed;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\title;

title('Betrieb - Handwerksrolle Online');

state(['bnr' => null]);

mount(function (string $bnr): void {
    $this->bnr = $bnr;

    if (app(BueLaravel::class)->getBetriebByBetriebsnr($bnr) === null) {
        abort(404);
    }

    $this->title = 'Betrieb '.$bnr.' - Handwerksrolle Online';
});

$betrieb = computed(function (): object {
    return app(BueLaravel::class)->getBetriebDetailByBetriebsnr($this->bnr);
});

?>

<div>
    <x-intranet-app-hwro::hwro-layout
        heading="{{ $this->betrieb->name ?? 'Betrieb '.$bnr }}"
        subheading="Betriebsnummer {{ $bnr }}"
    >
        <div class="space-y-6">
            <div class="flex flex-wrap items-center gap-2">
                <flux:button
                    href="{{ route('apps.hwro.betriebsbesuche.index') }}"
                    variant="ghost"
                    icon="arrow-left"
                    size="sm"
                >
                    Zurück zur Suche
                </flux:button>
            </div>

            <flux:card class="space-y-4">
                <flux:heading size="lg">Stammdaten</flux:heading>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <flux:text class="text-sm font-medium text-zinc-500">Betriebsnummer</flux:text>
                        <div>{{ $this->betrieb->bnr }}</div>
                    </div>
                    <div>
                        <flux:text class="text-sm font-medium text-zinc-500">Eintragungsdatum</flux:text>
                        <div>{{ $this->betrieb->edat ?? '—' }}</div>
                    </div>
                    <div>
                        <flux:text class="text-sm font-medium text-zinc-500">Betriebsart</flux:text>
                        <div>{{ $this->betrieb->betriebsart ?? '—' }}</div>
                    </div>
                    <div>
                        <flux:text class="text-sm font-medium text-zinc-500">Rechtsform</flux:text>
                        <div>{{ $this->betrieb->rechtsform ?? '—' }}</div>
                    </div>
                    <div class="sm:col-span-2">
                        <flux:text class="text-sm font-medium text-zinc-500">Name</flux:text>
                        <div class="whitespace-pre-line">{{ $this->betrieb->name ?? '—' }}</div>
                    </div>
                    <div class="sm:col-span-2">
                        <flux:text class="text-sm font-medium text-zinc-500">Anschrift</flux:text>
                        <div>
                            {{ $this->betrieb->betriebsanschrift
                                ?? (trim(implode(' ', array_filter([
                                    $this->betrieb->strasse ?? null,
                                    $this->betrieb->hausnummer ?? null,
                                    $this->betrieb->betr_plz ?? null,
                                    $this->betrieb->betr_ort ?? null,
                                ]))) ?: '—') }}
                        </div>
                    </div>
                </div>
            </flux:card>

            <flux:card class="space-y-4">
                <flux:heading size="lg">Kontaktdaten</flux:heading>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <flux:text class="text-sm font-medium text-zinc-500">Telefon</flux:text>
                        <div>{{ $this->betrieb->betr_telefon ?? '—' }}</div>
                    </div>
                    <div>
                        <flux:text class="text-sm font-medium text-zinc-500">Handy</flux:text>
                        <div>{{ $this->betrieb->betr_handy ?? '—' }}</div>
                    </div>
                    <div>
                        <flux:text class="text-sm font-medium text-zinc-500">E-Mail</flux:text>
                        <div>{{ $this->betrieb->betr_email ?? '—' }}</div>
                    </div>
                    <div>
                        <flux:text class="text-sm font-medium text-zinc-500">Fax</flux:text>
                        <div>{{ $this->betrieb->betr_fax ?? '—' }}</div>
                    </div>
                    <div class="sm:col-span-2">
                        <flux:text class="text-sm font-medium text-zinc-500">Internet</flux:text>
                        <div>{{ $this->betrieb->internet ?? '—' }}</div>
                    </div>
                </div>
            </flux:card>

            <flux:card class="space-y-4">
                <flux:heading size="lg">Handelsregister</flux:heading>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <flux:text class="text-sm font-medium text-zinc-500">Abteilung</flux:text>
                        <div>{{ $this->betrieb->hr_abt ?? '—' }}</div>
                    </div>
                    <div>
                        <flux:text class="text-sm font-medium text-zinc-500">Nummer</flux:text>
                        <div>{{ $this->betrieb->hr_nummer ?? '—' }}</div>
                    </div>
                    <div>
                        <flux:text class="text-sm font-medium text-zinc-500">Gericht</flux:text>
                        <div>{{ $this->betrieb->hr_gericht ?? '—' }}</div>
                    </div>
                    <div>
                        <flux:text class="text-sm font-medium text-zinc-500">Datum</flux:text>
                        <div>{{ $this->betrieb->hr_datum ?? '—' }}</div>
                    </div>
                </div>
            </flux:card>

            <flux:card class="space-y-4">
                <flux:heading size="lg">Eingetragene Handwerke</flux:heading>
                @if ($this->betrieb->gewerbe->isEmpty())
                    <flux:callout variant="info" icon="information-circle">
                        Keine Handwerke hinterlegt.
                    </flux:callout>
                @else
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Gewerbe</flux:table.column>
                            <flux:table.column>Eintragungsdatum</flux:table.column>
                            <flux:table.column>Teiltätigkeit</flux:table.column>
                            <flux:table.column>Betriebsart</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($this->betrieb->gewerbe as $gewerbe)
                                <flux:table.row wire:key="gewerbe-{{ $gewerbe->gewerbe }}-{{ $loop->index }}">
                                    <flux:table.cell>
                                        {{ $gewerbe->gewerbename ?? $gewerbe->gewerbe ?? '—' }}
                                        @if ($gewerbe->gewerbe)
                                            <flux:text class="text-xs text-zinc-500">{{ $gewerbe->gewerbe }}</flux:text>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @php
                                            $eintragung = $gewerbe->eintragungsdatum;
                                            try {
                                                $eintragungFormatted = $eintragung
                                                    ? \Illuminate\Support\Carbon::parse($eintragung)->format('d.m.Y')
                                                    : '—';
                                            } catch (\Throwable) {
                                                $eintragungFormatted = (string) $eintragung;
                                            }
                                        @endphp
                                        {{ $eintragungFormatted }}
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $gewerbe->teiltaetigkeit ?? '—' }}</flux:table.cell>
                                    <flux:table.cell>{{ $gewerbe->betriebsart ?? '—' }}</flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @endif
            </flux:card>

            <flux:card class="space-y-4">
                <flux:heading size="lg">Betriebspersonen</flux:heading>
                @if ($this->betrieb->personen->isEmpty())
                    <flux:callout variant="info" icon="information-circle">
                        Keine Personen hinterlegt.
                    </flux:callout>
                @else
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Stellung</flux:table.column>
                            <flux:table.column>Name</flux:table.column>
                            <flux:table.column>Geburtsdatum</flux:table.column>
                            <flux:table.column>Anschrift</flux:table.column>
                            <flux:table.column>Qualifikation</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($this->betrieb->personen as $person)
                                <flux:table.row wire:key="person-{{ $person->personennummer }}-{{ $loop->index }}">
                                    <flux:table.cell>{{ $person->personhatstellung ?? '—' }}</flux:table.cell>
                                    <flux:table.cell>
                                        {{ trim(($person->vorname ?? '').' '.($person->name ?? '')) ?: '—' }}
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $person->geburtsdatum ?? '—' }}</flux:table.cell>
                                    <flux:table.cell>
                                        {{ trim(implode(', ', array_filter([
                                            $person->strasse ?? null,
                                            trim(implode(' ', array_filter([$person->plz ?? null, $person->ort ?? null]))),
                                        ]))) ?: '—' }}
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @php
                                            $qualifikationen = collect($person->qualifikationen ?? []);
                                        @endphp
                                        @if ($qualifikationen->isEmpty())
                                            —
                                        @else
                                            <div class="space-y-2">
                                                @foreach ($qualifikationen as $quali)
                                                    <div class="space-y-0.5">
                                                        <div class="font-medium">
                                                            {{ $quali->gewerbe_bezeichnung ?? $quali->gewerbe ?? '—' }}
                                                        </div>
                                                        @if (! empty($quali->eintragungsvoraussetzung))
                                                            <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">
                                                                {{ $quali->eintragungsvoraussetzung }}
                                                            </flux:text>
                                                        @endif
                                                        @if (! empty($quali->teiltaetigkeit))
                                                            <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">
                                                                Teiltätigkeit: {{ $quali->teiltaetigkeit }}
                                                            </flux:text>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @endif
            </flux:card>
        </div>
    </x-intranet-app-hwro::hwro-layout>
</div>
