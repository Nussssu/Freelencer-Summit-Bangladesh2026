<?php

namespace Database\Seeders;

use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Models\Campus;
use App\Models\District;
use App\Enums\MediaKind;
use App\Models\JourneyProgram;
use App\Models\MediaItem;
use Illuminate\Database\Seeder;

/**
 * Local preview campus programmes for the Campus Programs directory (DemoSeeder runs it, local only), with the
 * institutions they run at, across all eight divisions and every status; completed ones get two photos each for the
 * Media Gallery. Rows keep is_sample = true so they are easy to find and remove.
 */
class CampusDemoSeeder extends Seeder
{
    // [institution, type, district slug, venue, organiser, start (Asia/Dhaka) or null, status, registration open]
    private const ROWS = [
        ['University of Dhaka', 'university', 'dhaka', 'Nabab Nawab Ali Chowdhury Senate Bhaban', 'DU Freelancing Club', '2026-11-12 10:00', ProgramStatus::RegistrationOpen, true],
        ['Bangladesh University of Engineering and Technology', 'university', 'dhaka', 'BUET Auditorium', 'BUET Career Society', '2026-11-19 10:00', ProgramStatus::RegistrationOpen, true],
        ['Jahangirnagar University', 'university', 'dhaka', 'Zahir Raihan Auditorium', 'JU Digital Skills Forum', '2026-12-03 10:30', ProgramStatus::Upcoming, false],
        ['Dhaka University of Engineering and Technology', 'university', 'gazipur', 'DUET Central Auditorium', 'DUET Tech Club', '2026-10-15 10:00', ProgramStatus::Completed, false],
        ['Chittagong University of Engineering and Technology', 'university', 'chattogram', 'CUET Shaheed Mohammad Shah Hall Auditorium', 'CUET Computer Club', '2026-10-02 10:00', ProgramStatus::Completed, false],
        ['University of Chittagong', 'university', 'chattogram', 'CU Central Auditorium', 'CU Career Club', '2026-11-26 10:00', ProgramStatus::RegistrationOpen, true],
        ['Rajshahi University of Engineering and Technology', 'university', 'rajshahi', 'RUET Central Auditorium', 'RUET Freelancers Network', '2026-12-10 10:00', ProgramStatus::Upcoming, false],
        ['Khulna University', 'university', 'khulna', 'Khulna University Liberation War Memorial Auditorium', 'KU Career Development Club', '2026-09-24 10:00', ProgramStatus::Completed, false],
        ['Shahjalal University of Science and Technology', 'university', 'sylhet', 'SUST Central Auditorium', 'SUST Digital Hub', '2026-12-17 10:00', ProgramStatus::Upcoming, false],
        ['University of Barishal', 'university', 'barishal', 'Barishal University Auditorium', 'BU Skills Circle', '2026-11-08 10:00', ProgramStatus::Postponed, false],
        ['Begum Rokeya University, Rangpur', 'university', 'rangpur', 'BRUR Central Auditorium', 'BRUR Freelancing Forum', null, ProgramStatus::Upcoming, false],
        ['Bangladesh Agricultural University', 'university', 'mymensingh', 'BAU Syed Nazrul Islam Auditorium', 'BAU Career Club', '2027-01-14 10:00', ProgramStatus::Upcoming, false],
        ['Dhaka Polytechnic Institute', 'polytechnic', 'dhaka', 'Dhaka Polytechnic Auditorium', 'DPI Tech Society', '2026-12-08 11:00', ProgramStatus::Upcoming, false],
    ];

    public function run(): void
    {
        // The two named rows DemoSeeder used to create are folded into the full list below
        JourneyProgram::where('type', ProgramType::CampusProgram)->whereIn('slug', ['campus-program-at-khulna-university', 'campus-program-at-begum-rokeya-university-rangpur'])->delete();

        $photo = 9; // gallery-01..08 belong to the roadshows
        foreach (self::ROWS as [$name, $kind, $district, $venue, $partner, $start, $status, $open]) {
            $district = District::with('division')->where('slug', $district)->firstOrFail();
            $campus = Campus::updateOrCreate(['slug' => str($name)->slug()->toString()], [
                'name' => $name,
                'institution_type' => $kind,
                'division_id' => $district->division_id,
                'district_id' => $district->id,
                'is_sample' => true,
            ]);
            // Times are Dhaka local; the app stores UTC.
            $starts = $start ? \Carbon\Carbon::parse($start, 'Asia/Dhaka') : null;
            $title = "Campus Program at {$name}";

            $program = JourneyProgram::updateOrCreate(['slug' => str($title)->slug()->toString()], [
                'type' => ProgramType::CampusProgram,
                'title' => $title,
                'division_id' => $district->division_id,
                'district_id' => $district->id,
                'campus_id' => $campus->id,
                'starts_at' => $starts?->copy()->utc(),
                'ends_at' => $starts?->copy()->setTime(16, 0)->utc(),
                'location' => $venue,
                'partner' => $partner,
                'description' => "<p>A one-day campus program at {$name}: a seminar on freelancing and the digital economy, hands-on workshops, career talks on ITES, BPO and AI-based services, and an AI awareness session with tools students can use straight away.</p><p>Open to students and recent graduates of {$name}. Bring a laptop for the workshops if you have one.</p>",
                'status' => $status,
                'registration_open' => $open,
                'is_sample' => true,
            ]);

            if ($status === ProgramStatus::Completed && ! $program->media()->exists()) {
                foreach (range(0, 1) as $i) {
                    MediaItem::create(['journey_program_id' => $program->id, 'stage' => ProgramType::CampusProgram->value, 'kind' => MediaKind::Photo, 'src' => sprintf('/media/photos/gallery-%02d.jpg', $photo++), 'caption' => $title, 'sort' => $i, 'is_sample' => true]);
                }
            }
        }
    }
}
