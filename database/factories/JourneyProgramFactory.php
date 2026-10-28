<?php

namespace Database\Factories;

use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use Illuminate\Database\Eloquent\Factories\Factory;

class JourneyProgramFactory extends Factory
{
    public function definition(): array
    {
        $n = $this->faker->unique()->numberBetween(1, 999);

        return [
            'type' => $this->faker->randomElement(ProgramType::cases()),
            'slug' => "sample-program-{$n}",
            'title' => "Sample program {$n}",
            'status' => ProgramStatus::Upcoming,
            'location' => null, // to be confirmed
            'is_sample' => true,
        ];
    }
}
