<?php

namespace Hwkdo\IntranetAppHwro\Data;

use Hwkdo\IntranetAppBase\Data\BaseUserSettings;
use Hwkdo\IntranetAppHwro\Data\Attributes\Description;
use Hwkdo\IntranetAppHwro\Enums\VorgaengeFilterEnum;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;

class UserSettings extends BaseUserSettings
{
    public function __construct(
        #[Description('Standard-Filter für die Anzeige von Vorgängen (Alle, Offen, Geschlossen, etc.)')]
        #[WithCast(EnumCast::class)]
        public VorgaengeFilterEnum $defaultVorgaengeFilter = VorgaengeFilterEnum::Alle,
    ) {}

}
