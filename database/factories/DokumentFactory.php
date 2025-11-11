<?php

namespace Hwkdo\IntranetAppHwro\Database\Factories;

use Hwkdo\IntranetAppHwro\Models\Dokument;
use Hwkdo\IntranetAppHwro\Models\Schlagwort;
use Hwkdo\IntranetAppHwro\Models\Vorgang;
use Illuminate\Database\Eloquent\Factories\Factory;

class DokumentFactory extends Factory
{
    protected $model = Dokument::class;

    public function definition(): array
    {
        return [
            'vorgang_id' => Vorgang::factory(),
            'schlagwort_id' => Schlagwort::factory(),
        ];
    }
}

