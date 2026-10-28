<?php

namespace Database\Factories;

use App\Enums\BoothStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class BoothFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'B'.$this->faker->unique()->numberBetween(100, 999),
            'status' => BoothStatus::Available,
            'x' => 0, 'y' => 0, 'w' => 90, 'h' => 90,
            'is_sample' => true,
        ];
    }
}
