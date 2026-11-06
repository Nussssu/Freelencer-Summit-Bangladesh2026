<?php

namespace Database\Seeders;

use App\Enums\MediaKind;
use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Models\Division;
use App\Models\JourneyProgram;
use App\Models\MediaItem;
use App\Models\Speaker;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Local preview summits for the Divisional Summits directory (DemoSeeder runs it, local only): one per division, with
 * the venues and dates the National Journey page shows, a fuller description, speakers and, once held, photos, so the
 * list, filters and detail pages can be reviewed at full length. Every row keeps is_sample = true.
 */
class DivisionalDemoSeeder extends Seeder
{
    // [division slug, venue, start (Asia/Dhaka), status, registration open, districts, campuses, focus]
    private const ROWS = [
        ['rajshahi', 'Rajshahi University Auditorium', '2026-09-13 10:00', ProgramStatus::Completed, false, 8, 14, 'agri-tech, e-commerce and content work for buyers abroad'],
        ['khulna', 'KUET Convention Hall', '2026-09-20 10:00', ProgramStatus::Completed, false, 10, 12, 'software, graphic design and the blue economy around Mongla'],
        ['rangpur', 'Begum Rokeya University', '2026-09-27 10:00', ProgramStatus::Completed, false, 8, 10, 'first-time freelancers, women in digital work and Bangla content'],
        ['chattogram', 'Chittagong Club Convention Centre', '2026-10-04 10:00', ProgramStatus::Completed, false, 11, 15, 'logistics, port services, BPO and customer support'],
        ['sylhet', 'SUST Central Auditorium', '2026-10-11 10:00', ProgramStatus::Completed, false, 4, 9, 'diaspora clients, tourism marketing and remote teams'],
        ['barishal', 'Barishal Shilpakala Academy', '2026-10-25 10:00', ProgramStatus::Completed, false, 6, 8, 'digital skills for coastal youth and online micro-businesses'],
        ['mymensingh', 'Bangladesh Agricultural University', '2026-11-08 10:00', ProgramStatus::RegistrationOpen, true, 4, 9, 'agri data, research services and AI tools for students'],
        ['dhaka', 'Bangabandhu International Conference Center', '2026-11-15 09:30', ProgramStatus::RegistrationOpen, true, 13, 23, 'AI services, agencies and the road to the Grand Summit'],
    ];

    public function run(): void
    {
        $speakers = Speaker::where('visible', true)->orderBy('id')->pluck('id');
        $photo = 4;

        foreach (self::ROWS as $n => [$slug, $venue, $start, $status, $open, $districts, $campuses, $focus]) {
            $division = Division::where('slug', $slug)->firstOrFail();
            // Times are Dhaka local; the app stores UTC.
            $starts = Carbon::parse($start, 'Asia/Dhaka');

            $program = JourneyProgram::updateOrCreate(['slug' => "{$slug}-divisional-summit"], [
                'type' => ProgramType::DivisionalSummit,
                'title' => "{$division->name} Divisional Summit",
                'division_id' => $division->id,
                'district_id' => null,
                'starts_at' => $starts->copy()->utc(),
                'ends_at' => $starts->copy()->setTime(17, 0)->utc(),
                'location' => $venue,
                'partner' => "BACCO {$division->name} Chapter",
                'description' => "<p>The {$division->name} Divisional Summit brings freelancers, students, trainers and companies from {$districts} districts and {$campuses} campuses together for a full day at {$venue}, one stop on the national journey to the Grand Summit in Dhaka.</p>"
                    ."<p>The day opens with keynotes from government and industry, then moves into panels on {$focus}. Afternoon workshops cover marketplace profiles, pricing, payments and the AI tools clients now expect, and local freelancers share how they found their first clients.</p>"
                    .'<p>Entry is free with registration. Bring a laptop for the workshops if you have one; seats in the hands-on rooms go first come, first served.</p>',
                'status' => $status,
                'registration_open' => $open,
                'cover_image' => "/media/photos/division-{$slug}.jpg",
                'is_sample' => true,
            ]);

            // Three speakers per summit, rotating through the visible ones
            if ($speakers->isNotEmpty()) {
                $program->speakers()->sync(collect(range(0, min(2, $speakers->count() - 1)))->mapWithKeys(fn ($i) => [$speakers[($n + $i) % $speakers->count()] => ['sort' => $i]])->all());
            }

            // Held summits get a few photos
            if ($status === ProgramStatus::Completed && ! $program->media()->exists()) {
                foreach (range(0, 2) as $i) {
                    MediaItem::create([
                        'journey_program_id' => $program->id,
                        'stage' => ProgramType::DivisionalSummit->value,
                        'kind' => MediaKind::Photo,
                        'src' => sprintf('/media/photos/gallery-%02d.jpg', ($photo++ % 14) + 1),
                        'caption' => "{$division->name} Divisional Summit",
                        'sort' => $i,
                        'is_sample' => true,
                    ]);
                }
            }
        }
    }
}
