<?php

namespace Tests\Feature;

use App\Content\InnerPageContent;
use App\Content\PageContent;
use App\Models\ExhibitEnquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['inertia.ssr.enabled' => false]);
        $this->withoutVite();
    }

    /** "/" plus the 27 brief URLs (slug routes with a sample slug). */
    public static function routes(): array
    {
        $paths = array_map(fn ($p) => str_replace('{slug}', 'sample-slug', $p), array_keys(PageContent::PAGES));

        return array_combine(['/', ...$paths], array_map(fn ($p) => [$p], ['/', ...$paths]));
    }

    #[DataProvider('routes')]
    public function test_route_returns_200(string $path): void
    {
        $this->get($path)->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component($path === '/' ? 'Home' : (InnerPageContent::PAGES[$path]['component'] ?? 'ComingNext'))
            ->has('site.navigation', 16)
            ->has('site.ctas.primary', 5)
            ->has('site.ctas.secondary', 2)
            ->has('summit.startsAt'));
    }

    public function test_inner_pages_render_hero_and_breadcrumbs(): void
    {
        foreach (InnerPageContent::PAGES as $path => $content) {
            $this->get($path)->assertOk()->assertInertia(fn (Assert $page) => $page
                ->component($content['component'])
                ->where('page.path', $path)
                ->where('page.title', PageContent::PAGES[$path][0])
                ->where('page.hero.title', $content['title'])
                ->missing('page.hero.component')
                ->has('page.hero.actions', 2));
            $this->assertStringContainsString($content['emphasis'], $content['title'], "$path emphasis");
            $this->assertCount(1, array_filter($content['actions'], fn ($a) => $a['tone'] === 'gold'), "$path one gold CTA");
        }
    }

    public function test_route_count_matches_brief(): void
    {
        $this->assertCount(28, self::routes(), '"/" + 27 brief URLs');
    }

    public function test_home_props_contain_every_section(): void
    {
        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('content', fn (Assert $c) => $c
                ->has('headings', 11)
                ->has('hero.messages', 4)
                ->has('trust.ticker')
                ->has('trust.groups', 3)
                ->has('about.paragraphs', 3)
                ->has('journey.steps', 4)
                ->has('journey.divisions', 8)
                ->has('journey.counters', 4)
                ->has('journey.directory.rows', 6)
                ->has('audiences', 9)
                ->has('highlights', 9)
                ->has('speakers')
                ->has('agenda.sessions', 5)
                ->has('awards.categories', 6)
                ->has('exhibition.booths')
                ->has('sponsorship.tiers', 8)
                ->has('forum.faq', 3)
                ->has('forum.benefits', 4)
                ->has('stories.tiles')
                ->has('partners.tiers', 8)
                ->has('news', 4)
                ->has('media.tiles')
                ->has('finalCta')
                ->etc()));
    }

    public function test_home_counts_come_from_config(): void
    {
        config(['summit.districts' => 20]);

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->where('summit.districts', 20)
            ->where('content.journey.steps.1.count', 20)
            ->where('content.journey.counters.0.value', 0));
    }

    public function test_home_has_seo_structured_data(): void
    {
        $this->get('/')->assertSee('application/ld+json', false)->assertSee('"@type":"Event"', false);
        $this->get('/sitemap.xml')->assertOk()->assertSee('/national-journey');
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap:');
    }

    public function test_exhibit_enquiry_validates_and_stores(): void
    {
        $this->from('/')->post('/exhibition/enquiries', ['company_name' => ''])
            ->assertRedirect('/')
            ->assertSessionHasErrors(['company_name', 'industry', 'head_office', 'full_name', 'designation', 'email', 'phone']);

        $this->from('/')->post('/exhibition/enquiries', [
            'company_name' => 'Acme Ltd', 'website' => 'https://acme.test', 'industry' => 'BPO', 'head_office' => 'Dhaka',
            'full_name' => 'A Person', 'designation' => 'Manager', 'email' => 'a@acme.test', 'phone' => '+880 1700 000000',
            'utm' => ['utm_source' => 'newsletter'],
        ])->assertRedirect('/')->assertSessionHas('success');

        $this->assertSame('newsletter', ExhibitEnquiry::first()->utm['utm_source']);
    }

    public function test_agenda_session_calendar_file(): void
    {
        $res = $this->get('/agenda/sessions/0.ics')->assertOk()->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $body = $res->getContent();
        $this->assertStringContainsString('BEGIN:VEVENT', $body);
        $this->assertStringContainsString('DTSTART:20261128T040000Z', $body); // 10:00 Dhaka = 04:00 UTC
        $this->assertStringContainsString('Venue to be confirmed', $body);
        $this->get('/agenda/sessions/99.ics')->assertNotFound();
    }

    public function test_locale_switch_persists_in_session(): void
    {
        $this->from('/')->post('/locale', ['locale' => 'bn'])->assertRedirect('/');
        $this->get('/')->assertInertia(fn (Assert $page) => $page->where('locale', 'bn'));
        $this->post('/locale', ['locale' => 'fr'])->assertSessionHasErrors('locale');
    }
}
