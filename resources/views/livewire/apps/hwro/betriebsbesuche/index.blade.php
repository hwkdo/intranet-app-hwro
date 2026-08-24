<?php

use Hwkdo\BueLaravel\BueLaravel;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use function Livewire\Volt\computed;
use function Livewire\Volt\state;
use function Livewire\Volt\title;

title('Betriebsbesuche - Handwerksrolle Online');

state(['searchQuery' => ''])->url(as: 'q');

$resultCards = computed(function (): Collection {
    $query = trim($this->searchQuery);

    if ($query === '' || mb_strlen($query) < 2) {
        return collect();
    }

    $betriebe = app(BueLaravel::class)->searchBetriebe($query);

    return $betriebe->map(function (object $betrieb) use ($query): array {
        $fieldValues = [
            'bnr' => (string) $betrieb->bnr,
            'name' => (string) ($betrieb->name ?? ''),
            'anschrift' => (string) ($betrieb->betriebsanschrift
                ?: trim(implode(' ', array_filter([
                    $betrieb->strasse ?? null,
                    $betrieb->hausnummer ?? null,
                    $betrieb->betr_plz ?? null,
                    $betrieb->betr_ort ?? null,
                ])))),
            'betriebsart' => (string) ($betrieb->betriebsart ?? ''),
            'rechtsform' => (string) ($betrieb->rechtsform ?? ''),
        ];

        $labels = [
            'bnr' => 'Betriebsnummer',
            'name' => 'Name',
            'anschrift' => 'Anschrift',
            'betriebsart' => 'Betriebsart',
            'rechtsform' => 'Rechtsform',
        ];

        $matchedOn = collect($betrieb->matched_on ?? []);

        $fieldMatches = collect($labels)
            ->map(function (string $label, string $field) use ($fieldValues, $query): ?array {
                $value = $fieldValues[$field] ?? '';

                if ($value === '' || ! Str::contains(mb_strtolower($value), mb_strtolower($query))) {
                    return null;
                }

                return [
                    'label' => $label,
                    'snippet' => $this->fallbackHighlight($value, $query),
                ];
            })
            ->filter()
            ->values();

        if ($matchedOn->contains('person')) {
            $fieldMatches = $fieldMatches->push([
                'label' => 'Person',
                'snippet' => 'Treffer über Betriebsperson (Name, Geburtsdatum oder Anschrift)',
            ]);
        }

        return [
            'betrieb' => $betrieb,
            'fieldMatches' => $fieldMatches,
        ];
    });
});

$fallbackHighlight = function (string $text, string $query): string {
    $escapedText = e($text);
    $escapedQuery = preg_quote($query, '/');

    return (string) preg_replace('/('.$escapedQuery.')/iu', '<mark>$1</mark>', $escapedText);
};

?>

<div>
    <x-intranet-app-hwro::hwro-layout heading="Betriebsbesuche" subheading="Betriebe in der Handwerksrolle suchen und prüfen">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">Betrieb suchen</flux:heading>
                <flux:text class="text-zinc-700 dark:text-zinc-200">
                    Suche nach Betriebsnummer, Name, Anschrift oder Personen (Name, Geburtsdatum, Anschrift).
                </flux:text>
            </div>

            <div class="relative">
                <flux:input
                    wire:model.live.debounce.300ms="searchQuery"
                    placeholder="Betriebsnummer, Name, Anschrift, Geburtsdatum …"
                    icon="magnifying-glass"
                    class="w-full"
                />
                <div
                    wire:loading.flex
                    wire:target="searchQuery"
                    class="pointer-events-none absolute inset-y-0 right-3 items-center"
                >
                    <flux:icon.arrow-path class="size-5 animate-spin text-zinc-500" />
                </div>
            </div>

            <div
                wire:loading.flex
                wire:target="searchQuery"
                class="items-center gap-4 rounded-xl border border-sky-200 bg-sky-50 px-5 py-6 dark:border-sky-800 dark:bg-sky-950/40"
            >
                <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-sky-100 dark:bg-sky-900/60">
                    <flux:icon.arrow-path class="size-6 animate-spin text-sky-700 dark:text-sky-300" />
                </div>
                <div class="min-w-0 space-y-1">
                    <flux:heading size="sm">Suche läuft …</flux:heading>
                    <flux:text class="text-sm text-zinc-700 dark:text-zinc-300">
                        Betriebe in der Handwerksrolle werden durchsucht. Das kann einen Moment dauern.
                    </flux:text>
                </div>
            </div>

            @if (trim($searchQuery) !== '' && mb_strlen(trim($searchQuery)) < 2)
                <flux:callout variant="info" icon="information-circle">
                    Geben Sie mindestens 2 Zeichen ein, um die Suche zu starten.
                </flux:callout>
            @endif

            <div wire:loading.remove wire:target="searchQuery">
                @if (trim($searchQuery) !== '' && mb_strlen(trim($searchQuery)) >= 2)
                    <div class="space-y-6">
                        <div class="flex items-center gap-2">
                            <flux:heading size="md">Treffer</flux:heading>
                            <flux:badge variant="outline">{{ $this->resultCards->count() }}</flux:badge>
                        </div>

                        @if ($this->resultCards->isEmpty())
                            <flux:callout variant="info" icon="information-circle">
                                Keine Treffer gefunden. Bitte versuchen Sie einen anderen Suchbegriff.
                            </flux:callout>
                        @else
                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                @foreach ($this->resultCards as $card)
                                    @php
                                        $betrieb = $card['betrieb'];
                                    @endphp
                                    <flux:card wire:key="betrieb-search-{{ $betrieb->bnr }}" class="space-y-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <flux:heading size="sm" class="break-words">
                                                    {!! $this->fallbackHighlight((string) ($betrieb->name ?? 'Ohne Namen'), trim($searchQuery)) !!}
                                                </flux:heading>
                                                <flux:text class="text-sm text-zinc-500">
                                                    BNR {{ $betrieb->bnr }}
                                                    @if ($betrieb->betriebsart || $betrieb->rechtsform)
                                                        · {{ collect([$betrieb->betriebsart, $betrieb->rechtsform])->filter()->implode(' · ') }}
                                                    @endif
                                                </flux:text>
                                            </div>
                                            <flux:button
                                                href="{{ route('apps.hwro.betriebsbesuche.show', $betrieb->bnr) }}"
                                                size="sm"
                                                variant="ghost"
                                                icon="eye"
                                            >
                                                Öffnen
                                            </flux:button>
                                        </div>

                                        @if ($betrieb->betriebsanschrift)
                                            <div class="text-sm text-zinc-700 dark:text-zinc-200">
                                                {!! $this->fallbackHighlight((string) $betrieb->betriebsanschrift, trim($searchQuery)) !!}
                                            </div>
                                        @endif

                                        @if (collect($card['fieldMatches'])->isNotEmpty())
                                            <div class="space-y-2 rounded-lg bg-emerald-50 p-3 text-sm dark:bg-emerald-900/20">
                                                <div class="font-medium text-emerald-800 dark:text-emerald-200">Treffer in Feldern</div>
                                                @foreach ($card['fieldMatches'] as $match)
                                                    <div>
                                                        <span class="font-medium">{{ $match['label'] }}:</span>
                                                        {!! $match['snippet'] !!}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </flux:card>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </x-intranet-app-hwro::hwro-layout>
</div>
