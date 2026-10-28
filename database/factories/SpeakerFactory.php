<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** Placeholder speakers: numbered "Sample Speaker" names, no photo, no invented biography. */
class SpeakerFactory extends Factory
{
    public function definition(): array
    {
        $n = sprintf('%02d', $this->faker->unique()->numberBetween(1, 99));

        return [
            'slug' => "sample-speaker-{$n}",
            'name' => "Sample Speaker {$n}",
            'designation' => 'Designation to be confirmed',
            'organisation' => 'Sample Organisation',
            'country' => 'BD',
            'role_label' => 'Speaker',
            'expertise' => ['Sample topic'],
            'is_sample' => true,
        ];
    }
}
