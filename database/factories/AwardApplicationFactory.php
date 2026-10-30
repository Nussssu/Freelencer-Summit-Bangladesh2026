<?php

namespace Database\Factories;

use App\Enums\AwardStatus;
use App\Models\AwardCategory;
use App\Models\District;
use Database\Factories\Concerns\BangladeshiContacts;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AwardApplicationFactory extends Factory
{
    use BangladeshiContacts;

    public function definition(): array
    {
        $name = $this->personName();
        $self = $this->faker->boolean(70);
        $nominator = $self ? null : $this->personName();
        $status = $this->faker->randomElement(AwardStatus::cases());
        $handle = Str::slug($name, '');

        return [
            'award_category_id' => AwardCategory::query()->inRandomOrder()->value('id') ?? AwardCategory::factory(),
            'edition' => (string) config('summit.edition'),
            'self_nominated' => $self,
            'nominator_name' => $nominator,
            'nominator_email' => $nominator ? $this->emailFor($nominator) : null,
            'nominator_phone' => $nominator ? $this->bdPhone() : null,
            'nominee_name' => $name,
            'nominee_email' => $this->emailFor($name),
            'nominee_phone' => $this->bdPhone(),
            'district_id' => District::query()->inRandomOrder()->value('id'),
            'portfolio_links' => [['platform' => 'Behance', 'url' => "https://www.behance.net/{$handle}"], ['platform' => 'GitHub', 'url' => "https://github.com/{$handle}"]],
            'marketplace_profiles' => [['platform' => 'Upwork', 'url' => "https://www.upwork.com/freelancers/~{$handle}"]],
            'achievements' => $this->faker->randomElement([
                'Top Rated Plus on Upwork with 120+ completed projects for clients in the US, UK and Australia.',
                'Built a five-person remote design team serving e-commerce brands in Europe.',
                'Delivered AI chatbot integrations for 30+ small businesses; mentors students at a local polytechnic.',
                'Grew from first gig in 2021 to a registered agency with recurring clients in three countries.',
            ]),
            'status' => $status,
            'shortlist_published' => in_array($status, [AwardStatus::Shortlisted, AwardStatus::Winner], true) && $this->faker->boolean(50),
            'winner_published' => false, // the public archive stays empty until real winners are announced
            'consent_version' => config('consent.version'),
            'consented_at' => $at = $this->submittedAt(),
            'created_at' => $at,
            'updated_at' => $at,
        ];
    }
}
