<?php

use App\Enums\AttendeeType;
use App\Enums\AwardStatus;
use App\Enums\ProgramType;
use App\Enums\SessionType;
use App\Mail\SubmissionReceived;
use App\Models\AwardCategory;
use App\Models\District;
use App\Models\Division;
use App\Models\JourneyProgram;
use App\Models\Speaker;
use App\Models\SummitRegistration;
use App\Queries\ContentQuery;
use App\Queries\ProgramQuery;
use App\Rules\BdMobile;
use App\Support\ReferenceNumber;
use App\Support\Uploads;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Database\Seeders\StructureSeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

it('keeps the brief lists verbatim in enums', function () {
    expect(SessionType::cases())->toHaveCount(11)
        ->and(array_column(SessionType::options(), 'label'))->toBe(['Keynote', 'Seminar', 'Workshop', 'Career Talk', 'Panel', 'AI Awareness', 'Success Story', 'Marketplace Session', 'Freelancer-to-Entrepreneur', 'Award Session', 'Networking'])
        ->and(AttendeeType::cases())->toHaveCount(11)
        ->and(AttendeeType::Company->label())->toBe('Company or buyer')
        ->and(AwardStatus::Rejected->label())->toBe('Rejected / Incomplete')
        ->and(ProgramType::options()[0])->toBe(['value' => 'divisional-summit', 'label' => 'Divisional summit']);
});

it('seeds the 8 divisions and 64 districts idempotently', function () {
    $this->seed(StructureSeeder::class);
    $this->seed(StructureSeeder::class);

    expect(Division::count())->toBe(8)
        ->and(District::count())->toBe(64)
        ->and(Division::where('slug', 'dhaka')->first()->districts()->count())->toBe(13)
        ->and((float) Division::where('slug', 'sylhet')->value('lat'))->toBe(24.8949);
});

it('runs demo data only locally and marks every demo row as sample', function () {
    $this->seed(DatabaseSeeder::class); // testing env: structure only
    expect(Speaker::count())->toBe(0);

    $this->seed(DemoSeeder::class);
    expect(Speaker::where('is_sample', false)->count())->toBe(0)
        ->and(Speaker::pluck('name')->every(fn ($n) => str_starts_with($n, 'Sample ')))->toBeTrue()
        ->and(JourneyProgram::where('is_sample', false)->count())->toBe(0)
        ->and(DB::table('booths')->count())->toBe(24)
        ->and(AwardCategory::count())->toBe(0); // categories wait for the client
});

it('issues sequential reference numbers per journey and edition', function () {
    expect(ReferenceNumber::next('REG'))->toBe('FSB-REG-2026-00001')
        ->and(ReferenceNumber::next('REG'))->toBe('FSB-REG-2026-00002')
        ->and(ReferenceNumber::next('AWD'))->toBe('FSB-AWD-2026-00001')
        ->and(ReferenceNumber::next('REG', '2027'))->toBe('FSB-REG-2027-00001');
});

it('stores submissions with reference, consent, hashed ip, utm and encrypted phone', function () {
    $request = Request::create('/register', 'POST', ['utm' => ['utm_source' => 'facebook', 'utm_medium' => 'social']], server: ['REMOTE_ADDR' => '203.0.113.7']);

    $r = SummitRegistration::submit([
        'edition' => '2026', 'name' => 'Test Attendee', 'email' => 'a@example.com', 'phone' => '+8801712345678',
        'attendee_type' => AttendeeType::Student, 'interests' => ['ai'],
    ], $request);

    expect($r->reference)->toBe('FSB-REG-2026-00001')
        ->and($r->consent_version)->toBe(config('consent.version'))
        ->and($r->consented_at)->not->toBeNull()
        ->and($r->ip_hash)->toHaveLength(64)->not->toContain('203.0.113.7')
        ->and($r->utm_source)->toBe('facebook')
        ->and(DB::table('summit_registrations')->value('phone'))->not->toBe('+8801712345678')
        ->and($r->fresh()->phone)->toBe('+8801712345678');

    $r->delete();
    expect(SummitRegistration::withTrashed()->count())->toBe(1);
});

it('allows one registration per email per edition', function () {
    SummitRegistration::factory()->create(['email' => 'same@example.com']);
    SummitRegistration::factory()->create(['email' => 'same@example.com']);
})->throws(UniqueConstraintViolationException::class);

it('validates and normalises Bangladesh mobile numbers', function (string $input, bool $ok) {
    expect(Validator::make(['m' => $input], ['m' => new BdMobile])->passes())->toBe($ok);
    if ($ok) {
        expect(BdMobile::normalize($input))->toBe('+8801712345678');
    }
})->with([
    ['01712345678', true], ['+8801712345678', true], ['8801712345678', true], ['01712-345 678', true],
    ['0171234567', false], ['01212345678', false], ['029123456', false], ['+911712345678', false],
]);

it('accepts nominations only while open and before the deadline', function () {
    expect(AwardCategory::factory()->make(['is_open' => true, 'deadline' => now()->addDay()])->acceptsNominations())->toBeTrue()
        ->and(AwardCategory::factory()->make(['is_open' => false])->acceptsNominations())->toBeFalse()
        ->and(AwardCategory::factory()->make(['is_open' => true, 'deadline' => now()->subDays(2)])->acceptsNominations())->toBeFalse();
});

it('limits uploads to pdf, jpg and png and stores them privately with random names', function () {
    Storage::fake('private');
    $pdf = UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf');

    expect(Validator::make(['f' => $pdf], ['f' => Uploads::rules()])->passes())->toBeTrue()
        ->and(Validator::make(['f' => UploadedFile::fake()->create('x.exe', 10, 'application/x-msdownload')], ['f' => Uploads::rules()])->passes())->toBeFalse()
        ->and(Validator::make(['f' => UploadedFile::fake()->create('big.pdf', Uploads::MAX_KB + 1, 'application/pdf')], ['f' => Uploads::rules()])->passes())->toBeFalse();

    $stored = Uploads::store($pdf, 'awards');
    Storage::disk('private')->assertExists($stored['path']);
    expect($stored['path'])->not->toContain('cv')->and($stored['original_name'])->toBe('cv.pdf');
});

it('rejects honeypot submissions', function () {
    Route::post('/_test/honeypot', fn () => 'ok')->middleware(['web', 'honeypot']);

    $this->post('/_test/honeypot', ['hp_company' => 'bot'])->assertSessionHasErrors('form');
    $this->post('/_test/honeypot', ['hp_company' => ''])->assertOk();
});

it('queues the branded confirmation email', function () {
    Mail::fake();
    Mail::to('a@example.com')->send(new SubmissionReceived('You are registered', 'FSB-REG-2026-00001', ['See you in Dhaka.']));

    Mail::assertQueued(SubmissionReceived::class, fn ($m) => $m->reference === 'FSB-REG-2026-00001');
    expect((new SubmissionReceived('You are registered', 'FSB-REG-2026-00001', ['See you in Dhaka.']))->render())->toContain('FSB-REG-2026-00001')->toContain('#071411');
});

it('shapes query results for pages', function () {
    $p = JourneyProgram::factory()->create(['type' => ProgramType::CampusProgram, 'slug' => 'sample-x']);
    $card = app(ProgramQuery::class)->list(ProgramType::CampusProgram, [])['data'][0];

    expect($card)->toMatchArray(['slug' => 'sample-x', 'href' => '/campus-programs/sample-x', 'sample' => true, 'statusLabel' => 'Upcoming'])
        ->and(app(ProgramQuery::class)->find(ProgramType::DistrictRoadshow, 'sample-x'))->toBeNull()
        ->and(collect(app(ContentQuery::class)->downloads())->pluck('href')->filter())->toBeEmpty();
});

it('only registers the kit preview locally', function () {
    $this->get('/_kit')->assertNotFound();
});
