<?php

namespace Database\Seeders;

use App\Enums\MediaKind;
use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Enums\SessionType;
use App\Models\AgendaSession;
use App\Models\Booth;
use App\Models\Division;
use App\Models\JourneyProgram;
use App\Models\MediaItem;
use App\Models\Speaker;
use Illuminate\Database\Seeder;

/**
 * Local preview data only (DatabaseSeeder skips it outside `local`). Every row has is_sample = true, so the
 * demo rows are easy to find and remove even where they carry believable names.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $speakers = Speaker::factory()->count(6)->create();

        foreach (array_slice(SessionType::cases(), 0, 6) as $i => $type) {
            AgendaSession::factory()->create(['type' => $type, 'sort' => $i])->speakers()->attach($speakers[$i]->id);
        }

        // Two named divisional summits (DivisionalDemoSeeder fills in all eight; roadshows and campus programs come from their
        // own demo seeders); Rajshahi has taken place and has two photos for the Media Gallery.
        $named = [
            [ProgramType::DivisionalSummit, 'Rajshahi Divisional Summit', 'rajshahi', ['status' => ProgramStatus::Completed, 'starts_at' => '2026-09-05 04:00:00']],
            [ProgramType::DivisionalSummit, 'Sylhet Divisional Summit', 'sylhet', []],
        ];
        foreach ($named as [$type, $title, $division, $extra]) {
            JourneyProgram::factory()->create(['type' => $type, 'title' => $title, 'slug' => str($title)->slug()->toString(), 'division_id' => Division::where('slug', $division)->value('id'), ...$extra]);
        }
        $rajshahi = JourneyProgram::where('slug', 'rajshahi-divisional-summit')->first();
        foreach (['highlight-keynote.jpg', 'highlight-stories.jpg'] as $i => $src) {
            MediaItem::create(['journey_program_id' => $rajshahi->id, 'stage' => ProgramType::DivisionalSummit->value, 'kind' => MediaKind::Photo, 'src' => "/media/photos/{$src}", 'caption' => $rajshahi->title, 'sort' => $i, 'is_sample' => true]);
        }

        // Illustrative 8 x 3 floor plan (viewBox 0 0 800 300), every booth available.
        foreach (range(0, 23) as $i) {
            Booth::factory()->create(['code' => sprintf('B%02d', $i + 1), 'x' => ($i % 8) * 100 + 5, 'y' => intdiv($i, 8) * 100 + 5]);
        }

        $this->call([ExhibitorDemoSeeder::class, DivisionalDemoSeeder::class, RoadshowDemoSeeder::class, CampusDemoSeeder::class]);
    }
}
