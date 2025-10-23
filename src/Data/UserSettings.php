<?php

namespace Hwkdo\IntranetAppHwro\Data;

use Hwkdo\IntranetAppHwro\Enums\VorgaengeFilterEnum;
use Livewire\Wireable;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class UserSettings extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        #[WithCast(EnumCast::class)]
        public VorgaengeFilterEnum $defaultVorgaengeFilter = VorgaengeFilterEnum::Alle,
    ) {}
}
