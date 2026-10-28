<?php

namespace Database\Factories;

use App\Enums\AttendeeType;
use Illuminate\Database\Eloquent\Factories\Factory;

class SummitRegistrationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'edition' => (string) config('summit.edition'),
            'name' => 'Test Attendee',
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => '+8801712345678',
            'attendee_type' => AttendeeType::Freelancer,
            'interests' => ['ai'],
            'consent_version' => config('consent.version'),
            'consented_at' => now(),
        ];
    }
}
