<?php

namespace Database\Factories;

use App\Enums\AttendeeType;
use App\Enums\Interest;
use App\Models\District;
use Database\Factories\Concerns\BangladeshiContacts;
use Illuminate\Database\Eloquent\Factories\Factory;

class SummitRegistrationFactory extends Factory
{
    use BangladeshiContacts;

    public function definition(): array
    {
        $name = $this->personName();
        $type = $this->faker->randomElement(AttendeeType::cases());
        $works = in_array($type, [AttendeeType::Company, AttendeeType::Entrepreneur, AttendeeType::Partner, AttendeeType::Marketplace], true);

        return [
            'edition' => (string) config('summit.edition'),
            'name' => $name,
            'email' => $this->emailFor($name),
            'phone' => $this->bdPhone(),
            'organisation' => $works ? $this->company() : null,
            'designation' => $works ? $this->designation() : null,
            'district_id' => District::query()->inRandomOrder()->value('id'),
            'attendee_type' => $type,
            'interests' => $this->faker->randomElements(Interest::values(), $this->faker->numberBetween(1, 4)),
            'consent_version' => config('consent.version'),
            'consented_at' => $at = $this->submittedAt(),
            'created_at' => $at,
            'updated_at' => $at,
        ];
    }
}
