<?php

namespace Hwkdo\IntranetAppHwro\Enums;

enum VorgaengeFilterEnum: string
{
    case Alle = 'alle';
    case MitBetrieb = 'mit_betrieb';
    case OhneBetrieb = 'ohne_betrieb';

    public function label(): string
    {
        return match ($this) {
            self::Alle => 'Alle Vorgänge',
            self::MitBetrieb => 'Nur mit Betrieb',
            self::OhneBetrieb => 'Nur ohne Betrieb',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}

