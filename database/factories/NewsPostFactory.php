<?php

namespace Database\Factories;

use App\Enums\NewsCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsPostFactory extends Factory
{
    public function definition(): array
    {
        $n = $this->faker->unique()->numberBetween(1, 999);

        return [
            'slug' => "sample-update-{$n}",
            'category' => $this->faker->randomElement(NewsCategory::cases()),
            'title' => "Sample update {$n}",
            'excerpt' => 'Sample text: the organiser will publish real updates here.',
            'published_at' => now()->subDays($n % 30),
            'is_sample' => true,
        ];
    }
}
