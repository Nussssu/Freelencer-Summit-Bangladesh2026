<?php

namespace Database\Factories;

use App\Enums\SessionType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgendaSessionFactory extends Factory
{
    public function definition(): array
    {
        $n = $this->faker->unique()->numberBetween(1, 999);
        $hour = 10 + $n % 7;

        return [
            'slug' => "sample-session-{$n}",
            'title' => "Sample session {$n}",
            'date' => null, // summit date not confirmed
            'starts_at' => sprintf('%02d:00', $hour),
            'ends_at' => sprintf('%02d:45', $hour),
            'type' => $this->faker->randomElement(SessionType::cases()),
            'is_sample' => true,
        ];
    }
}
