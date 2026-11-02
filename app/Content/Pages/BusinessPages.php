<?php

namespace App\Content\Pages;

use App\Content\HomeContent;
use App\Content\SiteContent;
use App\Enums\DownloadKind;
use App\Http\Controllers\SubmissionController;
use App\Queries\ContentQuery;
use App\Queries\ExhibitorQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Brief §10, §11, §12 and partners: sponsorship, sponsors, exhibition, exhibitors, the forum. */
class BusinessPages
{
    use FormOptions;

    private const TONES = ['tone-sun', 'tone-teal', 'tone-sky', 'tone-violet', 'tone-mint', 'tone-rose', 'tone-blue', 'tone-sun'];

    public function __construct(private HomeContent $home, private ExhibitorQuery $exhibitors) {}

    /** Confirmed sponsors by tier (provisional until agreements are signed, as on the landing). */
    public function sponsors(): array
    {
        $partners = $this->home->partners();

        return [
            'headings' => ['tiers' => self::h('Sponsors by category', 'by category'), 'become' => self::h('Your logo here', 'here')],
            'provisional' => $partners['provisional'],
            'tiers' => $partners['tiers'],
            'note' => 'Sponsor names and logos shown here are placeholders until sponsorships are confirmed.',
        ];
    }

    public function sponsorship(ContentQuery $content): array
    {
        $s = $this->home->sponsorship();

        return [
            'headings' => [
                'why' => self::h('Why sponsor the summit', 'sponsor'),
                'tiers' => self::h('Sponsorship tiers', 'tiers'),
                'enquiry' => self::h('Sponsorship Enquiry', 'Enquiry'),
            ],
            'why' => [
                ['icon' => 'map', 'tone' => 'tone-teal', 'title' => 'National reach', 'text' => 'Visibility across campus programs, district roadshows and divisional summits in all 8 divisions.'],
                ['icon' => 'mic', 'tone' => 'tone-sun', 'title' => 'On stage in Dhaka', 'text' => 'Category-based visibility at the Grand Summit, on site and on stage.'],
                ['icon' => 'people', 'tone' => 'tone-sky', 'title' => 'Meet the talent', 'text' => 'Connect with freelancers, students, companies and marketplaces in person.'],
                ['icon' => 'globe', 'tone' => 'tone-violet', 'title' => 'On the website', 'text' => 'Confirmed sponsor logos appear automatically in the sponsor areas of this website.'],
            ],
            'tiers' => array_map(fn ($t, $i) => ['slug' => Str::slug($t), 'name' => $t, 'benefits' => [], 'provisional' => $s['provisional'], 'tone' => self::TONES[$i % 8]], $s['tiers'], array_keys($s['tiers'])),
            'tiersNote' => 'Final tier names, benefits and commercial terms are in the sponsorship prospectus.',
            'prospectus' => $content->downloads([DownloadKind::Prospectus])[0],
            'form' => [
                'interests' => [...array_map(fn ($t) => ['value' => Str::slug($t), 'label' => $t], $s['tiers']), ['value' => 'not-sure', 'label' => 'Not sure yet']],
                'budgets' => self::options(SubmissionController::BUDGETS),
                'consent' => $this->consent(),
            ],
            'enquiryIntro' => "Partner with Freelancer Summit Bangladesh 2026 and position your brand at the center of Bangladesh's growing freelance economy.",
        ];
    }

    public function exhibition(Request $request): array
    {
        $x = $this->home->exhibition();
        $filters = ['q' => (string) $request->query('q', ''), 'sector' => (string) $request->query('sector', '')];

        return [
            'headings' => [
                'why' => self::h('Why exhibit', 'exhibit'),
                'who' => self::h('Who should exhibit', 'exhibit'),
                'floor' => self::h('Floor plan', 'plan'),
                'directory' => self::h('Exhibitor directory', 'directory'),
                'visit' => self::h('Visiting the exhibition', 'exhibition'),
            ],
            'why' => array_map(fn ($t, $i) => ['title' => $t, 'icon' => ['people', 'briefcase', 'rocket'][$i], 'tone' => ['tone-teal', 'tone-sun', 'tone-violet'][$i]], $x['why'], array_keys($x['why'])),
            'who' => array_map(fn ($t, $i) => ['title' => $t, 'icon' => ['globe', 'building', 'cap', 'chart'][$i] ?? 'store'], $x['who'], array_keys($x['who'])),
            'booths' => $this->exhibitors->booths(),
            'boothsNote' => 'Illustrative layout until the venue floor plan is final. Booth status updates as allocations are confirmed.',
            'legend' => [['value' => 'available', 'label' => 'Available'], ['value' => 'reserved', 'label' => 'Reserved'], ['value' => 'confirmed', 'label' => 'Confirmed']],
            'filters' => $filters,
            'exhibitors' => fn () => $this->exhibitors->directory($filters, 12),
            'sectors' => array_map(fn ($s) => ['value' => $s, 'label' => $s], $this->exhibitors->sectors()),
            'visit' => [
                ['icon' => 'ticket', 'title' => 'Free with registration', 'text' => 'The exhibition hall is open to every registered summit attendee.'],
                ['icon' => 'map', 'title' => 'Exhibition map', 'text' => 'A printable hall map with booth numbers is published with the final floor plan.'],
                ['icon' => 'search', 'title' => 'Plan your visit', 'text' => 'Search the directory by name or sector and note the booths you want to see.'],
            ],
        ];
    }

    public function exhibitor(string $slug): ?array
    {
        $e = $this->exhibitors->find($slug);
        if (! $e) {
            return null;
        }

        return [
            '_page' => [
                'title' => $e['name'],
                'description' => "{$e['name']} ({$e['sector']}) at the Freelancer Summit Bangladesh exhibition.",
                'crumbs' => [['label' => 'Exhibition', 'href' => '/exhibition'], ['label' => $e['name']]],
                'hero' => ['title' => $e['name'], 'emphasis' => '', 'intro' => $e['sector'], 'image' => '/media/photos/exhibit-booth.jpg', 'actions' => []],
            ],
            'exhibitor' => $e,
            'facts' => [
                ['icon' => 'store', 'label' => 'Booth', 'value' => $e['booth'] ?? 'To be allocated'],
                ['icon' => 'grid', 'label' => 'Sector', 'value' => $e['sector']],
            ],
        ];
    }

    public function exhibitionApply(): array
    {
        return [
            'sectors' => self::options(SubmissionController::SECTORS),
            'booths' => self::options(SubmissionController::BOOTHS),
            'consent' => $this->consent(),
            'steps' => ['Organization Details', 'Booth & Requirements', 'Review & Submit'],
        ];
    }

    public function forum(ContentQuery $content): array
    {
        $f = $this->home->forum();

        return [
            'headings' => [
                'purpose' => self::h('A community that outlasts the summit', 'outlasts the summit'),
                'benefits' => self::h('Member benefits', 'benefits'),
                'how' => self::h('How to join', 'join'),
                'activities' => self::h('Community and activities', 'activities'),
                'faq' => self::h('Questions about membership', 'membership'),
            ],
            'forum' => $f,
            'benefits' => array_map(fn ($b, $i) => [...$b, 'icon' => ['id', 'people', 'cap', 'megaphone'][$i] ?? 'star', 'tone' => ['tone-blue', 'tone-teal', 'tone-sun', 'tone-rose'][$i] ?? 'tone-teal'], $f['benefits'], array_keys($f['benefits'])),
            'steps' => [
                ['title' => 'Apply online', 'text' => 'Fill in your profile, skills and marketplace links. It takes a few minutes.'],
                ['title' => 'BACCO reviews', 'text' => 'Applications are reviewed once the membership rules are final.'],
                ['title' => 'Welcome to the forum', 'text' => 'You get your membership status by email and an invitation to forum activities.'],
            ],
            'activities' => [
                ['icon' => 'map', 'title' => 'Programmes near you', 'text' => 'Forum meet-ups at campus programs, district roadshows and divisional summits.'],
                ['icon' => 'star', 'title' => 'Member stories', 'text' => 'Members share how they built their careers, published on the success stories page.', 'href' => '/success-stories'],
                ['icon' => 'id', 'title' => 'Member portal', 'text' => $f['portalNote'].': a profile and login for members.'],
            ],
            'faq' => [...$content->faqs('forum'), ...$f['faq']],
        ];
    }

    public function forumRegister(): array
    {
        return [
            'districts' => $this->districts(),
            'experience' => self::options(SubmissionController::EXPERIENCE),
            'availability' => self::options(SubmissionController::AVAILABILITY),
            'skills' => self::options(SubmissionController::SKILLS),
            'platforms' => self::options(SubmissionController::PLATFORMS),
            'nationalities' => self::options(SubmissionController::NATIONALITIES),
            'profileTypes' => self::options(SubmissionController::PROFILE_TYPES),
            'consent' => $this->consent(),
            'steps' => ['Personal Profile', 'Freelancing Details', 'Review & Submit'],
        ];
    }

    public function partners(SiteContent $site): array
    {
        $trust = $this->home->trust();
        $partners = $this->home->partners();

        return [
            'headings' => [
                'organisers' => self::h('Jointly organised by', 'organised by'),
                'groups' => self::h('Partners and supporters', 'supporters'),
                'sponsors' => self::h('Sponsors', 'Sponsors'),
                'become' => self::h('Partner with the summit', 'Partner'),
            ],
            'organisers' => $site->organisers(),
            'groups' => $trust['groups'],
            'tiers' => $partners['tiers'],
            'provisional' => $partners['provisional'],
            'note' => 'Partner and sponsor names shown here are placeholders until partnerships are confirmed.',
        ];
    }
}
