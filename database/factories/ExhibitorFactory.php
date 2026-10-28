<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ExhibitorFactory extends Factory
{
    public function definition(): array
    {
        $n = sprintf('%02d', $this->faker->unique()->numberBetween(1, 99));

        return [
            'slug' => "sample-exhibitor-{$n}",
            'name' => "Sample Exhibitor {$n}",
            'sector' => $this->faker->randomElement(['Marketplace', 'Training', 'Fintech', 'BPO and ITES']),
            'description' => 'Sample profile: real exhibitors appear once confirmed.',
            'is_sample' => true,
        ];
    }
}
