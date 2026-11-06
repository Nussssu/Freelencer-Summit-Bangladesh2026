<?php

namespace App\Content;

use App\Enums\AwardStatus;
use App\Enums\PartnerCategory;
use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Enums\SessionType;
use App\Models\AgendaSession;
use App\Models\AwardApplication;
use App\Models\AwardCategory;
use App\Models\JourneyProgram;
use App\Models\Partner;
use App\Models\Speaker;
use App\Models\SponsorPackage;
use App\Models\SuccessStory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The admin modules, in the shapes the public Vue pages already receive (resources/js/types). Every reader returns
 * null only while its table has no rows at all (a fresh install before `php artisan cms:import-content`); the
 * content classes then keep their coded copy. Once a module has rows, the site shows exactly what the admin holds:
 * hidden, unpublished or unconfirmed rows disappear, and an emptied list shows the page's empty state.
 */
class CmsData
{
    /** @param class-string<Model> $model */
    private static function hasRows(string $model): bool
    {
        return (bool) rescue(fn () => $model::query()->exists(), false, report: false);
    }

    private static function photoUrl(?string $path): ?string
    {
        return $path ?: null;
    }

    // Speakers -------------------------------------------------------------------------------------------------

    /** Event Management › Speakers & experts: visible people in sort order. */
    public static function speakers(): ?array
    {
        if (! self::hasRows(Speaker::class)) {
            return null;
        }

        return Speaker::query()->where('visible', true)->orderBy('sort')->orderBy('id')->get()
            ->map(fn (Speaker $s) => self::speaker($s))->values()->all();
    }

    public static function speaker(Speaker $s): array
    {
        return [
            'slug' => $s->slug,
            'role' => $s->role_label ?: 'Speaker',
            'name' => $s->name,
            'designation' => $s->designation,
            'organisation' => $s->organisation,
            'country' => self::countryName($s->country),
            'photo' => self::photoUrl($s->photo),
            'bio' => $s->bio,
            'credential' => null,
            'expertise' => array_values(array_filter((array) $s->expertise)),
            'links' => collect((array) $s->links)->map(fn ($l) => is_array($l) ? ['label' => $l['label'] ?? $l['platform'] ?? 'Website', 'href' => $l['url'] ?? $l['href'] ?? null] : ['label' => 'Website', 'href' => (string) $l])
                ->filter(fn ($l) => filled($l['href']))->values()->all(),
            'featured' => (bool) $s->featured,
        ];
    }

    /** ISO code (speakers.country) to the English name the site shows. */
    public static function countryName(?string $code): string
    {
        $code = strtoupper(trim((string) $code));
        if ($code === '' || $code === 'BD') {
            return 'Bangladesh';
        }

        return class_exists(\Locale::class) ? (\Locale::getDisplayRegion('-'.$code, 'en') ?: $code) : $code;
    }

    // Agenda ---------------------------------------------------------------------------------------------------

    /** @return Collection<int, AgendaSession> visible sessions in running order */
    public static function sessionRows(): ?Collection
    {
        if (! self::hasRows(AgendaSession::class)) {
            return null;
        }

        return AgendaSession::with(['hall', 'speakers'])->where('visible', true)
            ->orderBy('date')->orderBy('starts_at')->orderBy('sort')->orderBy('id')->get();
    }

    /** The landing / Grand Summit agenda: { sample, types, sessions[{type,title,day,time,hall,speaker}] }. */
    public static function agenda(): ?array
    {
        $rows = self::sessionRows();
        if ($rows === null) {
            return null;
        }

        return [
            'sample' => false,
            'types' => array_column(SessionType::options(), 'label'),
            'sessions' => $rows->map(fn (AgendaSession $s) => self::session($s))->values()->all(),
        ];
    }

    public static function session(AgendaSession $s): array
    {
        $start = Carbon::parse(substr((string) config('summit.starts_at'), 0, 10));
        $day = $s->date ? max(1, (int) $start->diffInDays($s->date, false) + 1) : 1;
        $time = trim(substr((string) $s->starts_at, 0, 5).'–'.substr((string) $s->ends_at, 0, 5), '–');

        return [
            'type' => $s->type?->label() ?? 'Session',
            'title' => $s->title,
            'day' => "Day {$day}",
            'time' => $time,
            'hall' => $s->hall?->name ?? 'To be confirmed',
            'speaker' => $s->speakers->first()?->name ?? ($s->speaker_name ?: null),
        ];
    }

    /** /agenda rows and session pages: the row plus its detail, page link and calendar file. */
    public static function agendaSessions(): ?array
    {
        $rows = self::sessionRows();
        if ($rows === null) {
            return null;
        }

        return $rows->values()->map(fn (AgendaSession $s, int $i) => [
            ...self::session($s),
            'track' => $s->track,
            'summary' => $s->summary,
            'learn' => array_values((array) $s->learn),
            'audience' => array_values((array) $s->audience),
            'slug' => $s->slug,
            'href' => '/agenda/session/'.$s->slug,
            'ics' => "/agenda/sessions/{$i}.ics",
        ])->all();
    }

    // Sponsors & partners --------------------------------------------------------------------------------------

    /** The partner wall: { provisional, tiers[{name, slots, sponsors[{name, logo}]}] } from Content › Sponsors. */
    public static function sponsorTiers(): ?array
    {
        if (! self::hasRows(SponsorPackage::class)) {
            return null;
        }
        $packages = SponsorPackage::with('sponsors')->orderBy('sort')->orderBy('id')->get();

        return [
            'provisional' => $packages->contains('provisional', true),
            'tiers' => $packages->filter(fn ($p) => $p->sponsors->isNotEmpty())->map(fn (SponsorPackage $p) => [
                'name' => $p->name,
                'slots' => $p->sponsors->count(),
                'sponsors' => $p->sponsors->map(fn ($s) => ['name' => $s->name, 'logo' => self::photoUrl($s->logo)])->values()->all(),
            ])->values()->all(),
        ];
    }

    /** Tier names for /sponsorship-opportunity (every package, in order). */
    public static function sponsorPackages(): ?array
    {
        if (! self::hasRows(SponsorPackage::class)) {
            return null;
        }

        return SponsorPackage::orderBy('sort')->orderBy('id')->get()->map(fn ($p) => ['name' => $p->name, 'benefits' => array_values((array) $p->benefits), 'provisional' => (bool) $p->provisional])->all();
    }

    /** The landing's partner strip groups, from Content › Partners (organisers have their own block). */
    public static function partnerGroups(): ?array
    {
        if (! self::hasRows(Partner::class)) {
            return null;
        }
        $labels = [
            PartnerCategory::Government->value => 'Government & development partners',
            PartnerCategory::Strategic->value => 'Strategic partners',
            PartnerCategory::Supporting->value => 'Supporting organisations',
        ];
        $rows = Partner::orderBy('sort')->orderBy('id')->get()->groupBy(fn (Partner $p) => $p->category->value);

        return collect($labels)->map(fn ($label, $cat) => ['label' => $label, 'partners' => ($rows[$cat] ?? collect())->map(fn ($p) => ['name' => $p->name, 'logo' => self::photoUrl($p->logo)])->values()->all()])
            ->filter(fn ($g) => $g['partners'] !== [])->map(fn ($g) => ['label' => $g['label'], 'slots' => count($g['partners']), 'partners' => $g['partners']])->values()->all();
    }

    // Success stories ------------------------------------------------------------------------------------------

    /** /success-stories wall: published stories in sort order. */
    public static function stories(): ?array
    {
        if (! self::hasRows(SuccessStory::class)) {
            return null;
        }

        return SuccessStory::where('published', true)->orderBy('sort')->orderBy('id')->get()->map(fn (SuccessStory $s) => [
            'name' => $s->name, 'role' => $s->role, 'kind' => $s->kind ?: 'Freelancer', 'title' => $s->headline,
            'quote' => filled($s->body) ? \Illuminate\Support\Str::limit(strip_tags((string) $s->body), 140) : null,
            'image' => self::photoUrl($s->photo), 'youtubeId' => $s->youtube_id, 'sample' => false,
        ])->all();
    }

    /** The landing's "Real people, real impact" cards: the first three published stories. */
    public static function storyCards(): ?array
    {
        if (! self::hasRows(SuccessStory::class)) {
            return null;
        }

        return SuccessStory::where('published', true)->orderBy('sort')->orderBy('id')->limit(3)->get()->map(fn (SuccessStory $s) => [
            'id' => 'story-'.$s->id, 'title' => $s->headline, 'name' => $s->name, 'role' => $s->role,
            'image' => self::photoUrl($s->photo), 'url' => $s->youtubeUrl(),
        ])->all();
    }

    // Awards ---------------------------------------------------------------------------------------------------

    /** @return Collection<int, AwardCategory>|null */
    public static function awardCategories(): ?Collection
    {
        return self::hasRows(AwardCategory::class) ? AwardCategory::orderBy('sort')->orderBy('id')->get() : null;
    }

    /** Published shortlist (this edition) for /awards and /awards/nominees: [{name, role, category, photo}]. */
    public static function shortlist(): ?array
    {
        $rows = AwardApplication::with('category')->where('shortlist_published', true)
            ->whereIn('status', [AwardStatus::Shortlisted->value, AwardStatus::Winner->value])
            ->where('edition', (string) config('summit.edition'))->orderBy('award_category_id')->orderBy('nominee_name')->get();

        return $rows->isEmpty() ? null : $rows->map(fn (AwardApplication $a) => [
            'name' => $a->nominee_name, 'role' => $a->headline, 'category' => $a->category?->name, 'photo' => self::photoUrl($a->photo),
        ])->all();
    }

    /** Published winners, every edition, newest first: [{name, category, year, photo, text}]. */
    public static function winners(): ?array
    {
        $rows = AwardApplication::with('category')->where('winner_published', true)->where('status', AwardStatus::Winner->value)
            ->orderByDesc('edition')->orderBy('award_category_id')->get();

        return $rows->isEmpty() ? null : $rows->map(fn (AwardApplication $a) => [
            'name' => $a->nominee_name, 'category' => $a->category?->name, 'year' => (int) $a->edition, 'photo' => self::photoUrl($a->photo), 'text' => $a->bio,
        ])->all();
    }

    // Programmes -----------------------------------------------------------------------------------------------

    /** Journey directory rows (landing and /national-journey), every programme by date: [{type,name,location,date,partner,href,image}]. */
    public static function directoryRows(): ?array
    {
        if (! self::hasRows(JourneyProgram::class)) {
            return null;
        }
        $type = [ProgramType::DivisionalSummit->value => 'division', ProgramType::DistrictRoadshow->value => 'district', ProgramType::CampusProgram->value => 'campus'];

        return JourneyProgram::with(['district', 'division'])->orderByRaw('starts_at is null')->orderBy('starts_at')->orderBy('title')->get()
            ->map(fn (JourneyProgram $p) => [
                'type' => $type[$p->type->value],
                'name' => $p->title,
                'location' => $p->location ?? $p->district?->name ?? $p->division?->name,
                'date' => $p->starts_at?->timezone(config('summit.timezone'))->format('j M Y'),
                'partner' => $p->partner,
                'href' => '/'.$p->type->pathSegment().'/'.$p->slug,
                'image' => self::photoUrl($p->cover_image),
            ])->all();
    }

    /** /national-journey division cards: one per divisional summit, with its division's roadshow and campus counts. */
    public static function divisionSummits(): ?array
    {
        $summits = JourneyProgram::with('division')->where('type', ProgramType::DivisionalSummit->value)->orderByRaw('starts_at is null')->orderBy('starts_at')->get();
        if ($summits->isEmpty()) {
            return null;
        }
        $counts = JourneyProgram::query()->toBase()->selectRaw('division_id, type, count(*) as n')->groupBy('division_id', 'type')->get()
            ->groupBy('division_id')->map(fn ($g) => $g->pluck('n', 'type'));

        return $summits->map(fn (JourneyProgram $p) => [
            'name' => $p->division?->name ?? $p->title,
            'date' => $p->starts_at?->timezone(config('summit.timezone'))->format('j M Y') ?? 'To be confirmed',
            'venue' => $p->location ?? 'Venue to be confirmed',
            'districts' => (int) ($counts[$p->division_id][ProgramType::DistrictRoadshow->value] ?? 0),
            'campuses' => (int) ($counts[$p->division_id][ProgramType::CampusProgram->value] ?? 0),
            'participants' => (int) ($p->participants ?? 0) ?: $p->registrations()->count(),
            'held' => $p->status === ProgramStatus::Completed,
            'image' => self::photoUrl($p->cover_image),
            'href' => '/'.$p->type->pathSegment().'/'.$p->slug,
        ])->all();
    }

    /**
     * Journey counters from the programmes themselves (completed ones): divisions and districts covered,
     * institutions activated, participants reached (reported attendance, else registrations). A figure saved in
     * Event Management › Grand Summit (above 0) overrides its calculated value.
     */
    public static function journeyCounters(array $configured): ?array
    {
        if (! self::hasRows(JourneyProgram::class)) {
            return null;
        }
        $done = JourneyProgram::query()->where('status', ProgramStatus::Completed->value);
        $calculated = [
            'divisions_covered' => (clone $done)->whereNotNull('division_id')->distinct()->count('division_id'),
            'districts_covered' => (clone $done)->whereNotNull('district_id')->distinct()->count('district_id'),
            'institutions_activated' => (clone $done)->whereNotNull('campus_id')->distinct()->count('campus_id'),
            'participants_reached' => (int) (clone $done)->sum('participants')
                + \App\Models\ProgramRegistration::whereIn('journey_program_id', (clone $done)->whereNull('participants')->select('id'))->count(),
        ];

        return array_map(fn ($key) => (int) ($configured[$key] ?? 0) > 0 ? (int) $configured[$key] : $calculated[$key], array_combine(array_keys($calculated), array_keys($calculated)));
    }
}
