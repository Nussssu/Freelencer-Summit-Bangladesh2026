<?php

namespace Database\Seeders;

use App\Enums\ProgramType;
use App\Enums\SessionType;
use App\Models\AgendaSession;
use App\Models\Booth;
use App\Models\Exhibitor;
use App\Models\JourneyProgram;
use App\Models\NewsPost;
use App\Models\Speaker;
use Illuminate\Database\Seeder;

/**
 * Local preview data only (DatabaseSeeder skips it outside `local`). Every row has is_sample = true
 * and an obviously placeholder name ("Sample …"), so it can never pass for a real person or sponsor.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $speakers = Speaker::factory()->count(6)->create();

        foreach (array_slice(SessionType::cases(), 0, 6) as $i => $type) {
            AgendaSession::factory()->create(['type' => $type, 'sort' => $i])->speakers()->attach($speakers[$i]->id);
        }

        foreach (ProgramType::cases() as $type) {
            JourneyProgram::factory()->count(2)->create(['type' => $type]);
        }

        NewsPost::factory()->count(3)->create();

        // Illustrative 8 x 3 floor plan (viewBox 0 0 800 300), every booth available.
        foreach (range(0, 23) as $i) {
            Booth::factory()->create(['code' => sprintf('B%02d', $i + 1), 'x' => ($i % 8) * 100 + 5, 'y' => intdiv($i, 8) * 100 + 5]);
        }

        Exhibitor::factory()->count(4)->create();
    }
}
