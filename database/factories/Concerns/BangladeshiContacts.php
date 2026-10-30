<?php

namespace Database\Factories\Concerns;

use Illuminate\Support\Str;

/**
 * Realistic-looking local demo contacts. Every email is on a reserved example.com domain, so a "Resend emails"
 * click in the admin can never reach a real inbox. Companies are invented.
 */
trait BangladeshiContacts
{
    private const FIRST = ['Tanvir', 'Nusrat', 'Rafiqul', 'Farhana', 'Mahmudul', 'Sadia', 'Arif', 'Taslima', 'Shakil', 'Mehjabin', 'Imran', 'Rumana',
        'Kamrul', 'Sharmin', 'Rezaul', 'Nadia', 'Fahim', 'Jannatul', 'Sabbir', 'Tahmina', 'Asif', 'Moumita', 'Ripon', 'Sumaiya', 'Zahid', 'Afsana'];

    private const LAST = ['Ahmed', 'Islam', 'Rahman', 'Hossain', 'Akter', 'Chowdhury', 'Hasan', 'Kabir', 'Sultana', 'Karim', 'Haque', 'Khan', 'Das', 'Mia', 'Begum', 'Sarker'];

    private const COMPANIES = ['Padma Soft Solutions', 'Meghna Digital Labs', 'Jamuna IT Services', 'Karnaphuli Data Systems', 'Sundarban Cloud Works',
        'Teesta Tech BD', 'Brahmaputra BPO', 'Rupsha Creative Studio', 'Dhanshiri AI Labs', 'Surma Outsourcing', 'Gorai Web Factory', 'Shitalakshya Systems'];

    private const DESIGNATIONS = ['Managing Director', 'CEO', 'Head of Operations', 'HR Manager', 'Business Development Manager', 'Marketing Lead', 'CTO', 'Project Manager'];

    private function personName(): string
    {
        return $this->faker->randomElement(self::FIRST).' '.$this->faker->randomElement(self::LAST);
    }

    private function emailFor(string $name, ?string $company = null): string
    {
        $domain = $company ? Str::slug($company, '').'.example.com' : 'example.com';

        return Str::slug($name, '.').'.'.$this->faker->unique()->numberBetween(10, 9999).'@'.$domain;
    }

    private function bdPhone(): string
    {
        return '+880 1'.$this->faker->randomElement(['3', '5', '7', '8', '9']).$this->faker->numerify('## ######');
    }

    private function company(): string
    {
        return $this->faker->randomElement(self::COMPANIES);
    }

    private function designation(): string
    {
        return $this->faker->randomElement(self::DESIGNATIONS);
    }

    /** Submitted sometime in the last six weeks, during office hours. */
    private function submittedAt(): \DateTimeInterface
    {
        return now()->subDays($this->faker->numberBetween(0, 42))->setTime($this->faker->numberBetween(9, 20), $this->faker->numberBetween(0, 59));
    }
}
