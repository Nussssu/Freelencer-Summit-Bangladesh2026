<?php

namespace Database\Factories;

use Database\Factories\Concerns\BangladeshiContacts;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ExhibitEnquiryFactory extends Factory
{
    use BangladeshiContacts;

    public function definition(): array
    {
        $company = $this->company();
        $person = $this->personName();
        $at = $this->submittedAt();

        return [
            'company_name' => $company,
            'website' => 'https://www.'.Str::slug($company, '').'.example.com',
            'industry' => $this->faker->randomElement(['BPO & ITES', 'Software development', 'Digital marketing', 'Training institute', 'Fintech & payments']),
            'head_office' => $this->faker->randomElement(['Dhaka', 'Chattogram', 'Sylhet', 'Rajshahi', 'Khulna']),
            'full_name' => $person,
            'designation' => $this->designation(),
            'email' => $this->emailFor($person, $company),
            'phone' => $this->bdPhone(),
            'created_at' => $at,
            'updated_at' => $at,
        ];
    }
}
