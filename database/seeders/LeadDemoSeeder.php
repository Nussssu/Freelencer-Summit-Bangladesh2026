<?php

namespace Database\Seeders;

use App\Models\AwardApplication;
use App\Models\AwardCategory;
use App\Models\ExhibitEnquiry;
use App\Models\ExhibitionApplication;
use App\Models\Lead;
use App\Models\SummitRegistration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Local preview submissions for the admin (DatabaseSeeder runs it only in `local`). No public page reads these tables.
 * Mail is faked so seeding queues no emails; each lead takes its submission's date and a mix of follow-up statuses.
 */
class LeadDemoSeeder extends Seeder
{
    public function run(): void
    {
        Mail::fake();

        // The provisional categories the site already lists (HomeContent::awards()); replace when the client confirms them.
        foreach (['Rising Freelancer', 'Woman Freelancer', 'AI & Automation', 'Creative & Design', 'Freelancer to Entrepreneur', 'Campus Freelancer'] as $i => $name) {
            AwardCategory::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'is_open' => true, 'sort' => $i]);
        }

        $rows = [
            ...SummitRegistration::factory()->count(24)->create(),
            ...AwardApplication::factory()->count(18)->create(),
            ...ExhibitionApplication::factory()->count(16)->create(),
            ...ExhibitEnquiry::factory()->count(15)->create(),
        ];

        foreach ($rows as $row) {
            Lead::where('submittable_type', $row->getMorphClass())->where('submittable_id', $row->getKey())->update([
                'created_at' => $row->created_at,
                'updated_at' => $row->created_at,
                'status' => $row->created_at->lt(now()->subWeeks(2)) ? fake()->randomElement(['contacted', 'closed', 'closed', 'spam']) : fake()->randomElement(['new', 'new', 'contacted']),
            ]);
        }
    }
}
