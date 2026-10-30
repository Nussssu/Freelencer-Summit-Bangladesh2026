<?php

namespace Database\Factories;

use Database\Factories\Concerns\BangladeshiContacts;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExhibitionApplicationFactory extends Factory
{
    use BangladeshiContacts;

    public function definition(): array
    {
        $company = $this->company();
        $person = $this->personName();

        return [
            'organisation' => $company,
            'sector' => $this->faker->randomElement(['BPO & ITES', 'Software development', 'Marketplace / platform', 'Training institute', 'Fintech & payments', 'AI services']),
            'booth_requirement' => $this->faker->randomElement(['Standard booth (3×3 m)', 'Double booth (6×3 m)', 'Corner booth', 'Not sure yet']),
            'contact_person' => $person,
            'designation' => $this->designation(),
            'email' => $this->emailFor($person, $company),
            'phone' => $this->bdPhone(),
            'products' => $this->faker->randomElement(['Outsourced customer support and back-office services.', 'Freelancer training courses and certification.', 'Payment collection for freelancers working with foreign clients.', 'Custom web and mobile app development.']),
            'branding_needs' => $this->faker->randomElement(['Backdrop and two roll-up banners', 'Logo on the main stage screen', 'Branded counter and brochure stand', null]),
            'requirements' => $this->faker->randomElement(['Power outlet and Wi-Fi for live demos', 'Two chairs and a high table', 'TV screen for a product video', null]),
            'consent_version' => config('consent.version'),
            'consented_at' => $at = $this->submittedAt(),
            'created_at' => $at,
            'updated_at' => $at,
        ];
    }
}
