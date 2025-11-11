<?php

namespace Hwkdo\IntranetAppHwro\Database\Factories;

use Hwkdo\IntranetAppHwro\Models\Vorgang;
use Illuminate\Database\Eloquent\Factories\Factory;

class VorgangFactory extends Factory
{
    protected $model = Vorgang::class;

    public function definition(): array
    {
        return [
            'vorgangsnummer' => $this->faker->unique()->numberBetween(1000000, 9999999),
            'betriebsnr' => $this->faker->optional()->numberBetween(100000, 999999),
        ];
    }

    public function withoutBetriebsnr(): static
    {
        return $this->state(fn (array $attributes) => [
            'betriebsnr' => null,
        ]);
    }

    public function withBetriebsnr(): static
    {
        return $this->state(fn (array $attributes) => [
            'betriebsnr' => $this->faker->numberBetween(100000, 999999),
        ]);
    }
}

