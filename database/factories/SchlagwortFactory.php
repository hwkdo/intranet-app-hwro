<?php

namespace Hwkdo\IntranetAppHwro\Database\Factories;

use Hwkdo\IntranetAppHwro\Models\Schlagwort;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchlagwortFactory extends Factory
{
    protected $model = Schlagwort::class;

    public function definition(): array
    {
        return [
            'schlagwort' => fake()->word(),
            'filenames' => [
                fake()->word() . '.pdf',
                fake()->word() . '.pdf',
            ],
        ];
    }
}

