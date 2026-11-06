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

            // Fill the admin modules (speakers, agenda, stories, sponsors, partners, award categories) with the content
            // the site shows; never overwrites a module that has content. On a server, run `php artisan cms:import-content` once.
            $this->command?->call('cms:import-content');
        }
    }
}
