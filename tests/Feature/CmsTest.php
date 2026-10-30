<?php

namespace Tests\Feature;

use App\Mail\LeadMail;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Models\Media;
use App\Models\Page;
use App\Models\User;
use App\Support\Access;
use App\Support\Settings;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CmsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['inertia.ssr.enabled' => false]);
        $this->withoutVite();
        $this->seed(CmsSeeder::class);
    }

    private function enquiry(array $extra = []): array
    {
        return [
            'company_name' => 'Acme Ltd', 'industry' => 'BPO', 'head_office' => 'Dhaka', 'full_name' => 'A Person',
            'designation' => 'Manager', 'email' => 'a@acme.test', 'phone' => '+880 1700 000000', ...$extra,
        ];
    }

    public function test_admin_screens_need_login_and_load(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');

        $this->actingAs(User::factory()->create()->assignRole(Access::SUPER_ADMIN));
        foreach (['/admin', '/admin/media', '/admin/media?display=list', '/admin/leads', '/admin/pages', '/admin/email-templates', '/admin/site-settings', '/admin/header-footer',
            '/admin/users', '/admin/users/create', '/admin/roles', '/admin/audit-logs', '/admin/submissions/summit-registrations',
            '/admin/submissions/award-nominations', '/admin/submissions/exhibition-inquiries', '/admin/submissions/booth-applications'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/admin/media/'.Media::first()->id.'/edit')->assertOk();
        $this->get('/admin/pages/'.Page::first()->id.'/edit')->assertOk();
        $this->get('/admin/pages/'.Page::where('slug', '/about')->value('id').'/edit')->assertOk();
        $this->get('/admin/roles/'.Role::findByName('Manager')->id.'/edit')->assertOk();
    }

    public function test_seeder_imports_the_media_folder_and_one_page_per_route(): void
    {
        $this->assertTrue(Media::where('path', 'photos/gallery-01.jpg')->exists());
        $this->assertSame('/media/photos/gallery-01.jpg', Media::where('path', 'photos/gallery-01.jpg')->first()->url);
        $this->assertTrue(Page::where('slug', '/national-journey')->exists());
        $this->assertSame(3, EmailTemplate::count());
    }

    public function test_form_submission_becomes_a_lead_and_queues_both_emails(): void
    {
        Mail::fake();
        Settings::put('mail', ['admin_emails' => ['team@summit.test']]);

        $this->from('/')->post('/exhibition/enquiries', $this->enquiry())->assertSessionHas('success');

        $lead = Lead::sole();
        $this->assertSame(['exhibit_enquiry', 'A Person', 'a@acme.test', 'new'], [$lead->type, $lead->name, $lead->email, $lead->status]);
        $this->assertSame('Acme Ltd', $lead->data['company_name']);
        Mail::assertQueued(LeadMail::class, fn (LeadMail $m) => $m->hasTo('team@summit.test') && $m->template->key === 'admin_notification');
        Mail::assertQueued(LeadMail::class, fn (LeadMail $m) => $m->hasTo('a@acme.test') && $m->template->key === 'user_confirmation');
    }

    public function test_no_admin_inbox_means_only_the_user_email(): void
    {
        Mail::fake();
        $this->from('/')->post('/exhibition/enquiries', $this->enquiry());
        Mail::assertQueuedCount(1);
    }

    public function test_lead_email_escapes_submitted_text(): void
    {
        $lead = Lead::create(['type' => 'contact_message', 'name' => '<b>Eve</b>', 'email' => 'e@x.test', 'data' => ['message' => '<script>alert(1)</script>']]);
        $html = (new LeadMail($lead, EmailTemplate::where('key', 'admin_notification')->first()))->render();

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<b>Eve</b>', $html);
    }

    public function test_header_and_footer_read_settings_with_coded_defaults(): void
    {
        $this->get('/')->assertInertia(fn (Assert $p) => $p->where('site.headerLinks.0.href', '/about'));

        Settings::put('headerLinks', [['label' => 'Programme', 'href' => '/agenda']]);
        Settings::put('social', ['facebook' => 'https://facebook.com/fsbd', 'linkedin' => null, 'youtube' => null, 'x' => null]);

        $this->get('/agenda')->assertInertia(fn (Assert $p) => $p
            ->has('site.headerLinks', 1)
            ->where('site.headerLinks.0.label', 'Programme')
            ->where('summit.social.facebook', 'https://facebook.com/fsbd'));
    }

    public function test_page_seo_is_shared_only_when_set(): void
    {
        $this->get('/agenda')->assertInertia(fn (Assert $p) => $p->where('seo', null));

        Page::where('slug', '/agenda')->update(['meta_title' => 'Summit agenda 2026', 'keywords' => 'agenda, sessions', 'og_image' => '/media/photos/gallery-02.jpg']);

        $this->get('/agenda')->assertInertia(fn (Assert $p) => $p
            ->where('seo.title', 'Summit agenda 2026')
            ->where('seo.keywords', 'agenda, sessions')
            ->where('seo.image', '/media/photos/gallery-02.jpg')
            ->missing('seo.description'));
    }

    public function test_built_in_and_referenced_media_cannot_be_deleted(): void
    {
        $this->assertSame(['Built-in site asset', 'Page content: Home'], Media::where('path', 'photos/gallery-01.jpg')->first()->usages());

        $upload = Media::create(['path' => 'uploads/2026/10/test.jpg', 'name' => 'test', 'mime' => 'image/jpeg']);
        $this->assertSame([], $upload->usages());

        Page::where('slug', '/')->update(['og_image' => $upload->url]);
        Settings::put('organisers', [['short' => 'X', 'name' => 'X', 'role' => 'Y', 'logo' => $upload->url]]);
        $this->assertSame(['Site settings', 'Page SEO'], $upload->fresh()->usages());
    }

    public function test_smtp_saved_in_the_cms_overrides_env(): void
    {
        Settings::put('mail', ['from_address' => 'noreply@summit.test', 'smtp' => ['host' => 'smtp.summit.test', 'port' => '465', 'scheme' => 'smtps', 'username' => 'u', 'password' => encrypt('secret')]]);

        Settings::applyMail();

        $this->assertSame(['smtp', 'smtp.summit.test', 465, 'secret', 'noreply@summit.test'], [
            config('mail.default'), config('mail.mailers.smtp.host'), config('mail.mailers.smtp.port'), config('mail.mailers.smtp.password'), config('mail.from.address'),
        ]);
    }
}
