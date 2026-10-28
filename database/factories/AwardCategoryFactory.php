<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** Test-only: real award categories come from the client, so the demo seeder never creates these. */
class AwardCategoryFactory extends Factory
{
    public function definition(): array
    {
        $n = $this->faker->unique()->numberBetween(1, 999);

        return ['slug' => "test-category-{$n}", 'name' => "Test category {$n}", 'is_open' => true, 'deadline' => now()->addMonth()];
    }
}
