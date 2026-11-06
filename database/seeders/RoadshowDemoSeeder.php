<?php

namespace Database\Seeders;

use App\Enums\MediaKind;
use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Models\District;
use App\Models\JourneyProgram;
use App\Models\MediaItem;
use Illuminate\Database\Seeder;

/**
 * Local preview roadshows for the District Roadshows directory (DemoSeeder runs it, local only). Believable venues,
 * dates and organisers across all eight divisions and every status, so the list, filters and detail pages can be
 * reviewed at full length; every row keeps is_sample = true so they are easy to find and remove.
 */
class RoadshowDemoSeeder extends Seeder
{
    // [district slug, venue, organiser, start (Asia/Dhaka) or null, status, registration open]
    private const ROWS = [
        ['cumilla', 'Cumilla Town Hall', 'Cumilla Freelancers Forum', '2026-08-22 10:00', ProgramStatus::Completed, false],
        ['bogura', 'Bogura Zilla Parishad Auditorium', 'Barind Skills Academy', '2026-09-12 10:00', ProgramStatus::Completed, false],
        ['jashore', 'Jashore Shilpakala Academy', 'Jashore IT Park Association', '2026-10-03 09:30', ProgramStatus::Completed, false],
        ['moulvibazar', 'Moulvibazar Government College Hall', 'Sreemangal Digital Hub', '2026-10-24 10:00', ProgramStatus::Completed, false],
        ['narayanganj', 'Narayanganj Club Auditorium', 'Shitalakshya Youth Network', '2026-11-14 10:00', ProgramStatus::RegistrationOpen, true],
        ['dinajpur', 'Dinajpur Zilla Shilpakala Academy', 'Uttarbanga Freelancers Society', '2026-11-21 10:00', ProgramStatus::RegistrationOpen, true],
        ['coxs-bazar', 'Cox\'s Bazar Officers Club', 'Bayside Outsourcing Ltd', '2026-11-28 09:30', ProgramStatus::RegistrationOpen, true],
        ['patuakhali', 'Patuakhali Science and Technology University', 'Kuakata Youth Tech Circle', '2026-12-05 10:00', ProgramStatus::Upcoming, false],
        ['tangail', 'Tangail Zilla Parishad Auditorium', 'Tangail Digital Skills Club', '2026-12-12 10:00', ProgramStatus::Upcoming, false],
        ['kushtia', 'Lalon Academy Auditorium, Kushtia', 'Gorai IT Institute', '2026-12-19 10:00', ProgramStatus::Upcoming, false],
        ['netrokona', 'Netrokona Government College Hall', 'Mymensingh Freelancers Collective', '2027-01-09 10:00', ProgramStatus::Upcoming, false],
        ['sunamganj', 'Sunamganj Shilpakala Academy', 'Haor Digital Initiative', '2026-11-07 10:00', ProgramStatus::Postponed, false],
        ['kurigram', null, 'Teesta Youth Development Forum', null, ProgramStatus::Upcoming, false],
        ['bandarban', null, 'Hill Tracts Digital Network', null, ProgramStatus::Upcoming, false],
    ];

    public function run(): void
    {
        // Replace the generic "Sample program nnn" roadshows from the factory
        JourneyProgram::where('type', ProgramType::DistrictRoadshow)->where('slug', 'like', 'sample-program-%')->delete();

        $photo = 1;
        foreach (self::ROWS as [$slug, $venue, $partner, $start, $status, $open]) {
            $district = District::with('division')->where('slug', $slug)->firstOrFail();
            // Times are Dhaka local; the app stores UTC.
            $starts = $start ? \Carbon\Carbon::parse($start, 'Asia/Dhaka') : null;

            $program = JourneyProgram::updateOrCreate(['slug' => "{$slug}-district-roadshow"], [
                'type' => ProgramType::DistrictRoadshow,
                'title' => "{$district->name} District Roadshow",
                'division_id' => $district->division_id,
                'district_id' => $district->id,
                'starts_at' => $starts?->copy()->utc(),
                'ends_at' => $starts?->copy()->setTime(17, 0)->utc(),
                'location' => $venue,
                'partner' => $partner,
                'description' => "<p>A one-day roadshow bringing Freelancer Summit Bangladesh to {$district->name}: career awareness talks, hands-on skill sessions, marketplace guidance and stories from freelancers who started in the {$district->division->name} division.</p><p>Free to attend for students, job seekers and working freelancers. Bring a laptop for the afternoon workshops if you have one.</p>",
                'status' => $status,
                'registration_open' => $open,
                'cover_image' => "/media/photos/division-{$district->division->slug}.jpg",
                'is_sample' => true,
            ]);

            // Completed roadshows get a few photos for the "From the road" strip
            if ($status === ProgramStatus::Completed && ! $program->media()->exists()) {
                foreach (range(0, 1) as $i) {
                    MediaItem::create([
                        'journey_program_id' => $program->id,
                        'stage' => ProgramType::DistrictRoadshow->value,
                        'kind' => MediaKind::Photo,
                        'src' => sprintf('/media/photos/gallery-%02d.jpg', $photo++),
                        'caption' => "{$district->name} District Roadshow",
                        'sort' => $i,
                        'is_sample' => true,
                    ]);
                }
            }
        }
    }
}
