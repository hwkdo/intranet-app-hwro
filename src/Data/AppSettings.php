<?php

namespace Hwkdo\IntranetAppHwro\Data;

use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class AppSettings extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public bool $scheduleSearchBetriebsnr = true,
        public bool $scheduleMakeBetriebsakte = true,
        public int $scheduleSearchBetriebsnrIntervalMinutes = 15,
        public int $scheduleMakeBetriebsakteIntervalMinutes = 15,
    ) {}
}
