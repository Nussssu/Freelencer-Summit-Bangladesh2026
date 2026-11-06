<?php

namespace App\Console\Commands;

use App\Content\HomeContent;
use App\Content\InnerPageContent;
use App\Content\Pages\AwardPages;
use App\Enums\PartnerCategory;
use App\Enums\ProgramType;
use App\Enums\SessionType;
use App\Models\AgendaSession;
use App\Models\AwardCategory;
use App\Models\Hall;
use App\Models\JourneyProgram;
use App\Models\Page;
use App\Models\Partner;
use App\Models\Speaker;
use App\Models\Sponsor;
use App\Models\SponsorPackage;
use App\Models\SuccessStory;
use App\Models\Venue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * One-time (and safe to repeat) move of the lists the site used to hard-code or keep in page blocks into their admin
 * modules: speakers, agenda sessions (with venue and halls), success stories, sponsors by tier, partners, award
 * categories and the divisional summits' reported attendance. For each list the source is the published page block
 * when an admin edited one, else the coded list, so the site looks the same after the move. A module that already has
 * content is left alone; nothing existing is overwritten or deleted.
 */
class ImportCmsContent extends Command
{
    protected $signature = 'cms:import-content';

    protected $description = 'Move coded and page-block lists (speakers, agenda, stories, sponsors, partners, award categories) into their admin modules';

    /** Placeholder video for the landing story cards until the real ones are set (Content › Success stories). */
    private const STORY_VIDEO = 'jNQXAC9IVRw';

    public function handle(HomeContent $home, InnerPageContent $inner): int
    {
        DB::transaction(function () use ($home, $inner) {
            $this->speakers($home);
            $this->agenda($home, $inner);
            $this->stories();
            $this->sponsors($home);
            $this->partners($home);
            $this->awardCategories($home);
            $this->attendance();
        });
        $this->info('CMS content import finished.');

        return self::SUCCESS;
    }

    /** A published page's block value (list blocks unwrapped from {items}), or null. */
    private function block(string $slug, string $key): mixed
    {
        $page = Page::query()->where('slug', $slug)->first();
        $block = collect($page?->sections ?? [])->firstWhere('type', $key);
        if (! is_array($block['data'] ?? null)) {
            return null;
        }

        return array_key_exists('items', $block['data']) && count($block['data']) === 1 ? $block['data']['items'] : $block['data'];
    }

    private function speakers(HomeContent $home): void
    {
        $people = $this->block('/speakers', 'speakers') ?: $home->codedSpeakers();
        $created = 0;
        foreach (array_values($people) as $i => $p) {
            $slug = $p['slug'] ?? Str::slug($p['name'] ?? '');
            if (! $slug || Speaker::withTrashed()->where('slug', $slug)->exists()) {
                continue;
            }
            Speaker::create([
                'slug' => $slug, 'name' => $p['name'], 'role_label' => $p['role'] ?? 'Speaker', 'designation' => $p['designation'] ?? null,
                'organisation' => $p['organisation'] ?? null, 'country' => self::countryCode($p['country'] ?? null), 'photo' => $p['photo'] ?? null,
                'bio' => $p['bio'] ?? null, 'expertise' => $p['expertise'] ?? [],
                'links' => array_map(fn ($l) => ['label' => $l['label'] ?? 'Website', 'url' => $l['href'] ?? $l['url'] ?? null], (array) ($p['links'] ?? [])),
                'featured' => (bool) ($p['featured'] ?? false), 'visible' => true, 'sort' => $i + 1, 'is_sample' => false,
            ]);
            $created++;
        }
        // Seeded placeholders ("Sample Speaker 13"…) were never on the site: keep them, hidden.
        Speaker::where('is_sample', true)->where('name', 'like', 'Sample Speaker%')->update(['visible' => false]);
        if ($created) {
            // Speakers added earlier (programme mentors) follow the summit line-up.
            Speaker::query()->where('sort', '<', 100)->whereNotIn('slug', array_map(fn ($p) => $p['slug'] ?? Str::slug($p['name']), $people))
                ->orderBy('id')->get()->each(fn (Speaker $s, int $i) => $s->update(['sort' => 100 + $i]));
        }
        $this->line("Speakers: {$created} imported.");
    }

    /** The speakers table keeps ISO 3166 alpha-2 codes; the coded lists used country names. */
    private static function countryCode(?string $country): string
    {
        $country = trim((string) $country);
        if (preg_match('/^[A-Za-z]{2}$/', $country)) {
            return strtoupper($country);
        }
        $codes = ['bangladesh' => 'BD', 'india' => 'IN', 'united kingdom' => 'GB', 'united states' => 'US', 'australia' => 'AU', 'canada' => 'CA', 'singapore' => 'SG', 'malaysia' => 'MY', 'united arab emirates' => 'AE'];

        return $codes[strtolower($country)] ?? 'BD';
    }

    private function agenda(HomeContent $home, InnerPageContent $inner): void
    {
        if (AgendaSession::where('visible', true)->where('is_sample', false)->exists()) {
            $this->line('Agenda: already has sessions, skipped.');

            return;
        }
        // Seeded sample sessions were never on the site (it read the coded agenda): keep them, hidden.
        AgendaSession::where('is_sample', true)->update(['visible' => false]);
        $sessions = $this->block('/', 'agenda')['sessions'] ?? $home->codedAgenda()['sessions'];
        $details = $inner->codedAgendaSessions();
        $types = array_column(SessionType::options(), 'value', 'label');
        $start = substr((string) config('summit.starts_at'), 0, 10);
        $venue = Venue::firstOrCreate(['slug' => 'grand-summit-venue'], ['name' => config('summit.venue') ?: 'Grand Summit venue', 'city' => config('summit.city')]);

        foreach (array_values($sessions) as $i => $s) {
            $hallName = trim((string) ($s['hall'] ?? '')) ?: 'Main Hall';
            $hall = Hall::firstOrCreate(['venue_id' => $venue->id, 'slug' => Str::slug($hallName)], ['name' => $hallName, 'sort' => Hall::where('venue_id', $venue->id)->count()]);
            [$from, $to] = array_pad(preg_split('/\s*[–-]\s*/u', (string) ($s['time'] ?? '')), 2, null);
            $day = max(1, (int) preg_replace('/\D/', '', (string) ($s['day'] ?? '1')));
            $d = $details[$i] ?? [];
            $slug = Str::slug($s['title']);
            while (AgendaSession::where('slug', $slug)->exists()) {
                $slug .= '-'.($i + 1);
            }
            AgendaSession::create([
                'slug' => $slug, 'title' => $s['title'], 'type' => $types[$s['type'] ?? ''] ?? SessionType::Seminar->value,
                'date' => date('Y-m-d', strtotime($start.' +'.($day - 1).' days')),
                'starts_at' => $from ? $from.':00' : '10:00:00', 'ends_at' => $to ? $to.':00' : null, 'hall_id' => $hall->id,
                'track' => $d['track'] ?? null, 'summary' => $d['summary'] ?? null, 'learn' => $d['learn'] ?? [], 'audience' => $d['audience'] ?? [],
                'speaker_name' => $s['speaker'] ?? $d['speaker'] ?? null, 'sort' => $i, 'visible' => true, 'is_sample' => false,
            ]);
        }
        $this->line('Agenda: '.count($sessions).' sessions imported.');
    }

    private function stories(): void
    {
        if (SuccessStory::query()->exists()) {
            $this->line('Success stories: table has stories, skipped.');

            return;
        }
        $stories = $this->block('/success-stories', 'stories') ?: (app(\App\Content\Pages\MediaPages::class)->stories()['stories'] ?? []);
        foreach (array_values($stories) as $i => $s) {
            SuccessStory::create([
                'slug' => Str::slug($s['name'].' '.$s['title']), 'kind' => $s['kind'] ?? 'Freelancer', 'name' => $s['name'], 'role' => $s['role'] ?? null,
                'headline' => $s['title'], 'body' => $s['quote'] ?? null, 'photo' => $s['image'] ?? null,
                // The landing cards link to YouTube (placeholder until the real videos are set).
                'youtube_id' => $s['youtubeId'] ?? ($i < 3 ? self::STORY_VIDEO : null),
                'published' => true, 'sort' => $i + 1, 'is_sample' => false,
            ]);
        }
        $this->line('Success stories: '.count($stories).' imported.');
    }

    private function sponsors(HomeContent $home): void
    {
        if (SponsorPackage::query()->exists()) {
            $this->line('Sponsors: tiers exist, skipped.');

            return;
        }
        $wall = $this->block('/sponsors', 'tiers') ?: ($this->block('/', 'partners')['tiers'] ?? null) ?: $home->codedPartners()['tiers'];
        $count = 0;
        foreach (array_values($wall) as $i => $tier) {
            $package = SponsorPackage::create(['slug' => Str::slug($tier['name']), 'name' => $tier['name'], 'benefits' => [], 'provisional' => true, 'sort' => $i + 1]);
            foreach (array_values($tier['sponsors'] ?? []) as $j => $s) {
                $slug = Str::slug($s['name']);
                Sponsor::firstOrCreate(['slug' => $slug], ['sponsor_package_id' => $package->id, 'name' => $s['name'], 'logo' => $s['logo'] ?? null, 'confirmed' => true, 'sort' => $j, 'is_sample' => true]);
                $count++;
            }
        }
        $this->line("Sponsors: {$count} in ".count($wall).' tiers imported.');
    }

    private function partners(HomeContent $home): void
    {
        if (Partner::query()->exists()) {
            $this->line('Partners: table has partners, skipped.');

            return;
        }
        $groups = $this->block('/', 'trust')['groups'] ?? $home->trust()['groups'];
        $category = ['Government' => PartnerCategory::Government, 'Strategic' => PartnerCategory::Strategic, 'Supporting' => PartnerCategory::Supporting];
        $count = 0;
        foreach ($groups as $g) {
            $cat = collect($category)->first(fn ($c, $word) => str_contains((string) $g['label'], $word)) ?? PartnerCategory::Supporting;
            foreach (array_values($g['partners'] ?? []) as $j => $p) {
                Partner::firstOrCreate(['slug' => Str::slug($p['name'])], ['name' => $p['name'], 'category' => $cat, 'logo' => $p['logo'] ?? null, 'sort' => $count + $j, 'is_sample' => true]);
            }
            $count += count($g['partners'] ?? []);
        }
        $this->line("Partners: {$count} imported.");
    }

    private function awardCategories(HomeContent $home): void
    {
        $names = $this->block('/awards', 'awards')['categories'] ?? $home->codedAwards()['categories'];
        $eligibility = $home->codedAwards()['eligibility'];
        $created = 0;
        foreach (array_values($names) as $i => $name) {
            $c = AwardCategory::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'sort' => $i, 'is_open' => true]);
            $c->wasRecentlyCreated && $created++;
            // Fill what the coded page showed, never what an admin already wrote.
            $c->fill(array_filter([
                'description' => blank($c->description) ? AwardPages::categoryText($name) : null,
                'sort' => $c->wasRecentlyCreated ? $i : null,
            ], fn ($v) => $v !== null))->save();
        }
        $this->line("Award categories: {$created} imported (eligibility is left for the guidelines: {$eligibility}).");
    }

    /** Reported attendance of the held divisional summits, as the national journey page showed it. */
    private function attendance(): void
    {
        $cards = $this->block('/national-journey', 'divisionSummits') ?? [];
        $n = 0;
        foreach ($cards as $card) {
            $p = JourneyProgram::query()->where('type', ProgramType::DivisionalSummit->value)->whereNull('participants')
                ->whereHas('division', fn ($q) => $q->where('name', $card['name'] ?? ''))->first();
            if ($p && ($card['held'] ?? false) && ($card['participants'] ?? 0) > 0) {
                $p->update(['participants' => (int) $card['participants']]);
                $n++;
            }
        }
        $this->line("Divisional summits: attendance set on {$n}.");
    }
}
