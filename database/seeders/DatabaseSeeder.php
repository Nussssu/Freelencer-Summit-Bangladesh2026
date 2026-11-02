<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([StructureSeeder::class, CmsSeeder::class]);

        if (app()->environment('local')) {
            // CampaignNewsSeeder is real (brief-based) content: on production, run it once with --class when wanted.
            $this->call([DemoSeeder::class, LeadDemoSeeder::class, CampaignNewsSeeder::class]);
        }
    }
}
