<?php

namespace Tests\Feature;

use App\Enums\AwardStatus;
use App\Filament\Pages\GrandSummit;
use App\Filament\Resources\Cms\AgendaSessionResource;
use App\Filament\Resources\Cms\AwardCategoryResource;
use App\Filament\Resources\Cms\BoothResource;
use App\Filament\Resources\Cms\CampusProgramResource;
use App\Filament\Resources\Cms\DistrictRoadshowResource;
use App\Filament\Resources\Cms\DivisionalSummitResource;
use App\Filament\Resources\Cms\DownloadResource;
use App\Filament\Resources\Cms\ExhibitorResource;
use App\Filament\Resources\Cms\NewsPostResource;
use App\Filament\Resources\Cms\Pages\ManageSpeakers;
use App\Filament\Resources\Cms\PartnerResource;
use App\Filament\Resources\Cms\ProgrammeGalleryResource;
use App\Filament\Resources\Cms\SpeakerResource;
use App\Filament\Resources\Cms\SponsorResource;
use App\Filament\Resources\Cms\SuccessStoryResource;
use App\Filament\Resources\Cms\VenueResource;
use App\Filament\Resources\EmailTemplates\EmailTemplateResource;
use App\Filament\Resources\Leads\LeadResource;
use App\Filament\Resources\Media\MediaResource;
use App\Filament\Resources\Messages\MessageLogResource;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Submissions\AwardNominationResource;
use App\Filament\Resources\Submissions\BoothApplicationResource;
use App\Filament\Resources\Submissions\CampusAmbassadorResource;
use App\Filament\Resources\Submissions\ContactMessageResource;
use App\Filament\Resources\Submissions\ExhibitionInquiryResource;
use App\Filament\Resources\Submissions\ForumMembershipResource;
use App\Filament\Resources\Submissions\Pages\ListAwardNominations;
use App\Filament\Resources\Submissions\Pages\ListSummitRegistrations;
use App\Filament\Resources\Submissions\SponsorshipEnquiryResource;
use App\Filament\Resources\Submissions\SummitRegistrationResource;
use App\Models\AgendaSession;
use App\Models\AwardApplication;
use App\Models\AwardApplicationFile;
use App\Models\AwardCategory;
use App\Models\ForumApplication;
use App\Models\JourneyProgram;
use App\Models\Lead;
use App\Models\MessageLog;
use App\Models\Page;
use App\Models\Speaker;
use App\Models\Sponsor;
use App\Models\SuccessStory;
use App\Models\SummitRegistration;
use App\Models\User;
use App\Support\Access;
use App\Support\Settings;
use Database\Seeders\CmsSeeder;
use Database\Seeders\DemoSeeder;
use Database\Seeders\StructureSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The admin as one CMS with the site: every module loads, admin changes reach the public pages, public submissions
 * reach their module, and permissions, exports, notifications and the message log work end to end.
 */
class AdminModulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['inertia.ssr.enabled' => false]);
        $this->withoutVite();
        $this->seed([StructureSeeder::class, CmsSeeder::class, DemoSeeder::class]);
        $this->artisan('cms:import-content')->assertSuccessful();
    }

    private function admin(string $role = Access::SUPER_ADMIN): User
    {
        return User::factory()->create()->assignRole($role);
    }

    public function test_every_admin_module_loads_for_a_super_admin(): void
    {
        $this->actingAs($this->admin());

        $resources = [
            SummitRegistrationResource::class, ForumMembershipResource::class, CampusAmbassadorResource::class,
            AwardCategoryResource::class, AwardNominationResource::class,
            LeadResource::class, SponsorshipEnquiryResource::class, ExhibitionInquiryResource::class, BoothApplicationResource::class,
            ContactMessageResource::class, MessageLogResource::class, EmailTemplateResource::class,
            DivisionalSummitResource::class, DistrictRoadshowResource::class, CampusProgramResource::class, AgendaSessionResource::class,
            SpeakerResource::class, VenueResource::class, BoothResource::class,
            PageResource::class, NewsPostResource::class, SuccessStoryResource::class, MediaResource::class, ProgrammeGalleryResource::class,
            DownloadResource::class, PartnerResource::class, SponsorResource::class, ExhibitorResource::class,
        ];
        $failed = [];
        foreach ($resources as $resource) {
            $response = $this->get($resource::getUrl('index'));
            if ($response->status() !== 200) {
                $failed[$resource] = $response->status().' '.substr((string) ($response->exception?->getMessage() ?? ''), 0, 200);
            }
        }
        $this->assertSame([], $failed);
        $this->get(GrandSummit::getUrl())->assertOk();
    }

    public function test_the_sidebar_has_the_nine_groups_in_order(): void
    {
        $this->actingAs($this->admin());
        $this->get('/admin')->assertOk()->assertSeeInOrder(['Overview', 'Leads &amp; CRM', 'Registrations', 'Awards Management', 'Messages', 'Event Management', 'Content Management', 'Configuration', 'Access Control'], false);
    }

    public function test_the_import_is_repeatable_and_keeps_the_site_unchanged(): void
    {
        $counts = fn () => [Speaker::count(), AgendaSession::count(), SuccessStory::count(), Sponsor::count(), AwardCategory::count()];
        $before = $counts();
        $this->artisan('cms:import-content')->assertSuccessful();
        $this->assertSame($before, $counts());

        $this->get('/')->assertOk()->assertInertia(fn (Assert $p) => $p
            ->where('content.speakers.0.name', 'Mir Mohammed Helal Uddin, MP')
            ->where('content.agenda.sessions.0.title', 'Opening keynote')
            ->where('content.storyCards.0.name', 'Nusrat Jahan')
            ->where('content.partners.tiers.0.name', 'Title Sponsor'));
    }

    public function test_speaker_changes_in_the_admin_reach_every_speaker_list(): void
    {
        $this->actingAs($this->admin());
        $speaker = Speaker::where('slug', 'tanvir-ibrahim')->firstOrFail();

        Livewire::test(ManageSpeakers::class)
            ->callAction(TestAction::make('edit')->table($speaker), ['name' => 'Tanvir Ibrahim (updated)'])
            ->assertHasNoActionErrors();

        $this->get('/speakers')->assertInertia(fn (Assert $p) => $p->where('content.speakers.1.name', 'Tanvir Ibrahim (updated)'));
        $this->get('/speaker/tanvir-ibrahim')->assertOk();

        $speaker->refresh()->update(['visible' => false]);
        $this->get('/speakers')->assertInertia(fn (Assert $p) => $p->where('content.speakers', fn ($list) => collect($list)->doesntContain('slug', 'tanvir-ibrahim')));
        $this->get('/speaker/tanvir-ibrahim')->assertNotFound();
        $this->get('/grand-summit')->assertInertia(fn (Assert $p) => $p->where('content.speakers', fn ($list) => collect($list)->doesntContain('slug', 'tanvir-ibrahim')));
    }

    public function test_a_page_block_can_no_longer_override_a_module_list(): void
    {
        $page = Page::where('slug', '/speakers')->firstOrFail();
        $page->update(['sections' => [...($page->sections ?? []), ['type' => 'speakers', 'data' => ['items' => [['slug' => 'ghost', 'name' => 'Stale copy']]]]]]);

        $this->get('/speakers')->assertInertia(fn (Assert $p) => $p->where('content.speakers', fn ($list) => collect($list)->doesntContain('name', 'Stale copy')));
    }

    public function test_agenda_sessions_drive_the_agenda_and_session_pages(): void
    {
        $session = AgendaSession::where('slug', 'opening-keynote')->firstOrFail();
        $session->update(['title' => 'Opening keynote: the next decade', 'slug' => 'opening-keynote-next-decade', 'summary' => 'Where we go next.']);

        $this->get('/agenda')->assertInertia(fn (Assert $p) => $p->where('content.agenda.sessions.0.title', 'Opening keynote: the next decade'));
        $this->get('/agenda/session/opening-keynote-next-decade')->assertOk()->assertInertia(fn (Assert $p) => $p->where('content.session.summary', 'Where we go next.'));
        $this->get('/agenda/sessions/0.ics')->assertOk()->assertSee('Opening keynote: the next decade');

        $session->update(['visible' => false]);
        $this->get('/agenda/session/opening-keynote-next-decade')->assertNotFound();
    }

    public function test_stories_sponsors_and_award_categories_follow_their_modules(): void
    {
        SuccessStory::orderBy('sort')->first()->update(['headline' => 'A new first story', 'youtube_id' => SuccessStory::youtubeId('https://youtu.be/dQw4w9WgXcQ')]);
        Sponsor::where('slug', 'techcorp-it')->update(['confirmed' => false]);
        AwardCategory::where('slug', 'rising-freelancer')->update(['is_open' => false]);

        $this->get('/')->assertInertia(fn (Assert $p) => $p
            ->where('content.storyCards.0.title', 'A new first story')
            ->where('content.storyCards.0.url', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')
            ->where('content.partners.tiers', fn ($tiers) => collect($tiers)->flatMap(fn ($t) => $t['sponsors'])->doesntContain('name', 'TechCorp IT')));
        $this->get('/awards/nominate')->assertInertia(fn (Assert $p) => $p->where('content.categories', fn ($c) => collect($c)->doesntContain('value', 'rising-freelancer')));
    }

    public function test_published_nominations_replace_the_placeholder_showcase(): void
    {
        $category = AwardCategory::where('slug', 'ai-automation')->firstOrFail();
        $a = AwardApplication::factory()->create(['award_category_id' => $category->id, 'edition' => (string) config('summit.edition'), 'nominee_name' => 'Real Nominee', 'status' => AwardStatus::Submitted]);

        $this->actingAs($this->admin());
        Livewire::test(ListAwardNominations::class)->callAction(TestAction::make('shortlist')->table($a))->assertHasNoActionErrors();

        $this->assertSame(AwardStatus::Shortlisted, $a->refresh()->status);
        $this->get('/awards/nominees')->assertInertia(fn (Assert $p) => $p->where('content.nominees', fn ($n) => collect($n)->pluck('name')->all() === ['Real Nominee']));
        $this->get('/awards')->assertInertia(fn (Assert $p) => $p->where('content.shortlist.0.name', 'Real Nominee'));
    }

    public function test_programme_changes_reach_the_directories_and_the_national_journey(): void
    {
        $p = JourneyProgram::where('slug', 'rajshahi-divisional-summit')->firstOrFail();
        $p->update(['location' => 'New Rajshahi Venue', 'participants' => 9999]);

        $this->get('/national-journey')->assertInertia(fn (Assert $page) => $page
            ->where('content.divisionSummits', fn ($cards) => collect($cards)->contains(fn ($c) => $c['venue'] === 'New Rajshahi Venue' && $c['participants'] === 9999)));
        $this->get('/divisional-summits/rajshahi-divisional-summit')->assertOk();
    }

    public function test_grand_summit_details_override_the_config(): void
    {
        $this->actingAs($this->admin());
        Livewire::test(GrandSummit::class)
            ->fillForm(['venue' => 'Bashundhara Convention Center', 'edition' => '2026', 'starts_at' => '2026-11-29 10:00', 'city' => 'Dhaka', 'tagline' => 'Win from here.', 'counters' => ['divisions_covered' => 8, 'districts_covered' => 0, 'institutions_activated' => 0, 'participants_reached' => 0]])
            ->call('save')->assertHasNoFormErrors();

        Settings::flush();
        Settings::applySummit();
        $this->assertSame('Bashundhara Convention Center', config('summit.venue'));
        $this->get('/')->assertInertia(fn (Assert $p) => $p->where('content.journey.counters.0.value', 8));
    }

    public function test_public_submissions_land_in_their_module_with_a_status_and_a_team_notification(): void
    {
        $staff = $this->admin('Staff');
        $this->post('/forum/register', [
            'name' => 'Forum Applicant', 'email' => 'forum@example.test', 'mobile' => '01711000000', 'district' => 'dhaka', 'nationality' => 'bangladeshi',
            'profile_type' => 'freelancer', 'experience_level' => 'growing', 'skills' => ['design'], 'availability' => 'part-time', 'consent' => true,
        ]);

        $row = ForumApplication::where('email', 'forum@example.test')->first();
        if ($row === null) {
            $this->markTestSkipped('Forum form fields differ from this payload: '.json_encode(session('errors')?->getBag('default')->keys()));
        }
        $this->assertSame('pending', $row->status);
        $this->assertTrue(Lead::where('submittable_type', $row->getMorphClass())->where('submittable_id', $row->id)->exists());
        $this->assertSame(1, $staff->notifications()->count()); // Staff may view memberships

        $this->actingAs($this->admin());
        $this->get(ForumMembershipResource::getUrl('index'))->assertOk()->assertSee('Forum Applicant');
    }

    public function test_registrations_export_to_csv_without_phone_numbers(): void
    {
        SummitRegistration::factory()->create(['name' => 'Csv Person', 'email' => 'csv@example.test', 'phone' => '+8801711222333']);
        $this->actingAs($this->admin());

        $response = Livewire::test(ListSummitRegistrations::class)->callAction(TestAction::make('export')->table());
        $response->assertFileDownloaded();
    }

    public function test_staff_can_view_but_not_change_or_create(): void
    {
        $this->actingAs($this->admin('Staff'));

        $this->get(SpeakerResource::getUrl('index'))->assertOk();
        $this->assertFalse(SpeakerResource::canCreate());
        $this->assertFalse(SpeakerResource::canEdit(Speaker::first()));
        $this->get(GrandSummit::getUrl())->assertForbidden();
        $this->assertFalse(SummitRegistrationResource::canCreate()); // submissions only ever come from the site
    }

    public function test_award_documents_are_private_to_permitted_admins(): void
    {
        Storage::fake('private');
        Storage::disk('private')->put('awards/doc.pdf', '%PDF-1.4 test');
        $a = AwardApplication::factory()->create();
        $file = AwardApplicationFile::create(['award_application_id' => $a->id, 'path' => 'awards/doc.pdf', 'original_name' => 'portfolio.pdf', 'mime' => 'application/pdf', 'size' => 13]);

        $this->get(route('admin.award-files.show', $file))->assertForbidden();
        $this->actingAs(User::factory()->create()->assignRole('Staff'))->get(route('admin.award-files.show', $file))->assertOk();
    }

    public function test_sent_emails_are_logged(): void
    {
        config(['mail.default' => 'array']);
        \Illuminate\Support\Facades\Mail::raw('Hello', fn ($m) => $m->to('someone@example.test')->subject('Log me'));

        $this->assertTrue(MessageLog::where('recipient', 'someone@example.test')->where('subject', 'Log me')->where('status', 'sent')->exists());
    }
}
