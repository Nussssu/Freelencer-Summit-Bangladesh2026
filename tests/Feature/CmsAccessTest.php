<?php

namespace Tests\Feature;

use App\Content\HomeContent;
use App\Filament\Pages\HeaderFooter;
use App\Filament\Resources\Media\Pages\ListMedia;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Submissions\Pages\ListSummitRegistrations;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Mail\LeadMail;
use App\Models\AuditLog;
use App\Models\ExhibitEnquiry;
use App\Models\Lead;
use App\Models\Media;
use App\Models\Page;
use App\Models\SummitRegistration;
use App\Models\User;
use App\Support\Access;
use App\Support\PageBlocks;
use App\Support\Settings;
use Database\Seeders\CmsSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use Tests\TestCase;

/** CMS phase 2: encrypted lead contact data, page blocks, roles, audit log, bulk media delete, reCAPTCHA. */
class CmsAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['inertia.ssr.enabled' => false]);
        $this->withoutVite();
        $this->seed(CmsSeeder::class);
    }

    private function user(string $role): User
    {
        return User::factory()->create()->assignRole($role);
    }

    private function enquiry(): array
    {
        return ['company_name' => 'Acme Ltd', 'industry' => 'BPO', 'head_office' => 'Dhaka', 'full_name' => 'A Person',
            'designation' => 'Manager', 'email' => 'a@acme.test', 'phone' => '+880 1700 000000'];
    }

    public function test_phone_numbers_are_encrypted_at_rest_in_leads_and_enquiries(): void
    {
        Mail::fake();
        $this->from('/')->post('/exhibition/enquiries', $this->enquiry())->assertSessionHas('success');

        foreach ([DB::table('leads')->first()->phone, DB::table('leads')->first()->data, DB::table('exhibit_enquiries')->first()->phone] as $raw) {
            $this->assertStringNotContainsString('1700', $raw);
        }
        $this->assertSame('+880 1700 000000', Lead::sole()->phone);
        $this->assertSame('+880 1700 000000', Lead::sole()->data['phone']);
        $this->assertSame('+880 1700 000000', ExhibitEnquiry::sole()->phone);
    }

    public function test_seeded_blocks_render_the_coded_props_unchanged(): void
    {
        foreach (['/', '/about', '/national-journey', '/agenda', '/speakers', '/awards'] as $slug) {
            $coded = PageBlocks::coded($slug);
            $this->assertSame(json_encode($coded), json_encode(PageBlocks::apply($slug, $coded)), $slug);
        }
    }

    public function test_saving_a_page_in_the_editor_keeps_the_exact_prop_shape(): void
    {
        $this->actingAs($this->user(Access::SUPER_ADMIN));
        foreach (['/', '/about', '/national-journey', '/speakers', '/awards'] as $slug) {
            Livewire::test(EditPage::class, ['record' => Page::where('slug', $slug)->value('id')])->call('save')->assertHasNoFormErrors();
            $coded = PageBlocks::coded($slug);
            $this->assertSame(json_encode($coded), json_encode(PageBlocks::apply($slug, $coded)), $slug);
        }
    }

    public function test_block_edits_reach_the_page_and_drafts_fall_back_to_code(): void
    {
        $page = Page::where('slug', '/')->first();
        $sections = collect($page->sections)->map(function ($b) {
            if ($b['type'] === 'hero') {
                $b['data']['eyebrow'] = 'From the CMS';
            }
            if ($b['type'] === 'news') {
                $b['data']['items'] = array_slice($b['data']['items'], 0, 1);
            }

            return $b;
        })->push(['type' => 'exhibition', 'data' => ['enquiryFields' => ['hacked']]])->all(); // code-only key: ignored
        $page->update(['sections' => $sections]);

        $this->get('/')->assertInertia(fn (Assert $p) => $p
            ->where('content.hero.eyebrow', 'From the CMS')
            ->where('content.hero.video.src', '/media/delta-flow.webm')
            ->has('content.news', 1)
            ->where('content.exhibition.enquiryFields', app(HomeContent::class)->exhibition()['enquiryFields']));

        $page->update(['is_draft' => true]);
        $this->get('/')->assertInertia(fn (Assert $p) => $p->where('content.hero.eyebrow', app(HomeContent::class)->hero()['eyebrow']));
    }

    public function test_roles_limit_what_staff_can_open(): void
    {
        $this->actingAs($this->user('Staff'));
        $this->get('/admin/leads')->assertOk();
        $this->get('/admin/submissions/summit-registrations')->assertOk();
        foreach (['/admin/site-settings', '/admin/users', '/admin/roles', '/admin/audit-logs', '/admin/email-templates'] as $url) {
            $this->get($url)->assertForbidden();
        }

        $this->actingAs($this->user('Admin'));
        $this->get('/admin/users')->assertOk();
        $this->get('/admin/roles')->assertForbidden();
    }

    public function test_submission_rows_follow_role_permissions_and_delete_softly(): void
    {
        $reg = SummitRegistration::factory()->create();

        $this->actingAs($this->user('Staff'));
        Livewire::test(ListSummitRegistrations::class)
            ->assertActionVisible(TestAction::make('view')->table($reg))
            ->assertActionHidden(TestAction::make('edit')->table($reg))
            ->assertActionHidden(TestAction::make('delete')->table($reg));

        $this->actingAs($this->user('Admin'));
        Livewire::test(ListSummitRegistrations::class)
            ->callAction(TestAction::make('edit')->table($reg), ['name' => 'Corrected Name', 'phone' => '+880 1711 000000'])
            ->callAction(TestAction::make('delete')->table($reg));

        $reg = SummitRegistration::withTrashed()->find($reg->id);
        $this->assertSame(['Corrected Name', '+880 1711 000000'], [$reg->name, $reg->phone]);
        $this->assertSoftDeleted($reg);
    }

    public function test_bulk_delete_on_registrations_and_users(): void
    {
        $regs = SummitRegistration::factory()->count(3)->create();
        $me = $this->user('Admin');
        $boss = $this->user(Access::SUPER_ADMIN);
        $staff = $this->user('Staff');

        $this->actingAs($me);
        Livewire::test(ListSummitRegistrations::class)->selectTableRecords($regs->pluck('id')->all())->callAction(TestAction::make('delete')->table()->bulk());
        $regs->each(fn ($r) => $this->assertSoftDeleted($r));

        Livewire::test(ListUsers::class)->selectTableRecords([$me->id, $boss->id, $staff->id])->callAction(TestAction::make('delete')->table()->bulk());
        $this->assertModelMissing($staff);
        $this->assertModelExists($me);   // never yourself
        $this->assertModelExists($boss); // an Admin can't remove a Super Admin
    }

    public function test_header_and_footer_save_from_their_own_page(): void
    {
        $this->actingAs($this->user(Access::SUPER_ADMIN));
        Livewire::test(HeaderFooter::class)
            ->set('data.headerLinks', [['label' => 'Programme', 'href' => '/agenda']])
            ->call('save')->assertHasNoFormErrors();

        $this->assertSame([['label' => 'Programme', 'href' => '/agenda']], Settings::get('headerLinks'));
        $this->assertNotNull(Settings::get('organisers')); // the rest of the page saved in its usual shape
    }

    public function test_suspended_users_are_locked_out(): void
    {
        $user = $this->user('Admin');
        $user->forceFill(['suspended_at' => now()])->save();
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_staff_actions_and_sign_ins_are_audited_without_values(): void
    {
        $user = $this->user('Manager');
        $updated = $user->updated_at;
        $this->travel(5)->minutes();
        event(new Login('web', $user, false));
        $this->assertNotNull($user->fresh()->last_login_at);
        $this->assertEquals($updated, $user->fresh()->updated_at); // last login is a quiet write

        $this->actingAs($user);
        Page::where('slug', '/about')->first()->update(['meta_title' => 'Secret value']);

        $log = AuditLog::where('action', 'Updated')->sole();
        $this->assertSame([$user->id, 'Page'], [$log->user_id, $log->module]);
        $this->assertStringContainsString('meta_title', $log->description);
        $this->assertStringNotContainsString('Secret value', $log->description);
        $this->assertTrue(AuditLog::where('action', 'Logged in')->exists());

        auth()->forgetUser();
        SummitRegistration::factory()->create(); // anonymous visitor submissions are not staff actions
        $this->assertSame(0, AuditLog::where('module', 'Summit Registration')->count());
    }

    public function test_bulk_delete_keeps_files_in_use(): void
    {
        $free = Media::create(['path' => 'uploads/2026/10/free.jpg', 'name' => 'free', 'mime' => 'image/jpeg']);
        $used = Media::create(['path' => 'uploads/2026/10/used.jpg', 'name' => 'used', 'mime' => 'image/jpeg']);
        Page::where('slug', '/about')->update(['og_image' => $used->url]);

        $this->actingAs($this->user(Access::SUPER_ADMIN));
        Livewire::test(ListMedia::class)->selectTableRecords([$free->id, $used->id])->callAction(TestAction::make('delete')->table()->bulk());

        $this->assertModelMissing($free);
        $this->assertModelExists($used);
    }

    public function test_recaptcha_blocks_missing_or_low_score_tokens_and_fails_open_when_google_is_down(): void
    {
        Mail::fake();
        Settings::put('recaptcha', ['enabled' => true, 'version' => 'v3', 'threshold' => 0.5, 'site_key' => 'site', 'secret_key' => encrypt('secret')]);

        $this->from('/')->post('/exhibition/enquiries', $this->enquiry())->assertSessionHasErrors('recaptcha');

        Http::fake(['www.google.com/*' => Http::sequence()->push(['success' => true, 'score' => 0.2])->push(['success' => true, 'score' => 0.9])->pushStatus(503)]);
        $this->from('/')->post('/exhibition/enquiries', $this->enquiry(), ['X-Recaptcha-Token' => 't'])->assertSessionHasErrors('recaptcha');
        $this->assertSame(0, ExhibitEnquiry::count());
        $this->from('/')->post('/exhibition/enquiries', $this->enquiry(), ['X-Recaptcha-Token' => 't'])->assertSessionHas('success');
        $this->from('/')->post('/exhibition/enquiries', $this->enquiry(), ['X-Recaptcha-Token' => 't'])->assertSessionHas('success');
        $this->assertSame(2, ExhibitEnquiry::count());

        $this->get('/')->assertSee('recaptcha/api.js?render=site', false);
    }

    public function test_registrations_get_the_registration_confirmed_email(): void
    {
        Mail::fake();
        SummitRegistration::factory()->create(['email' => 'r@x.test']);
        Mail::assertQueued(LeadMail::class, fn (LeadMail $m) => $m->hasTo('r@x.test') && $m->template->key === 'registration_confirmed');
    }

    public function test_identity_and_seo_defaults_reach_the_site(): void
    {
        Settings::put('identity', ['site_name' => 'FSBD', 'favicon' => '/media/uploads/fav.png']);
        Settings::put('seo', ['title' => 'Home default', 'og_image' => '/media/og.jpg']);

        $this->get('/')->assertSee('href="/media/uploads/fav.png"', false)->assertInertia(fn (Assert $p) => $p
            ->where('summit.name', 'FSBD')->where('seo.title', 'Home default')->where('seo.image', '/media/og.jpg'));
        $this->get('/agenda')->assertInertia(fn (Assert $p) => $p->missing('seo.title')->where('seo.image', '/media/og.jpg'));
    }
}
