<?php

namespace App\Content\Pages;

use App\Content\CmsData;
use App\Content\HomeContent;
use App\Content\SiteContent;
use App\Enums\DownloadKind;
use App\Http\Controllers\SubmissionController;
use App\Queries\ContentQuery;
use App\Queries\ExhibitorQuery;
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
            // Content Management › Sponsors › tiers (name, benefits, provisional), else the coded tier names.
            'tiers' => array_map(fn ($t, $i) => ['slug' => Str::slug($t['name']), 'name' => $t['name'], 'benefits' => $t['benefits'], 'provisional' => $t['provisional'], 'tone' => self::TONES[$i % 8]],
                $packages = CmsData::sponsorPackages() ?? array_map(fn ($t) => ['name' => $t, 'benefits' => [], 'provisional' => $s['provisional']], $s['tiers']), array_keys($packages)),
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

    public function exhibition(): array
    {
        $x = $this->home->exhibition();

        return [
            'headings' => [
                'audience' => self::h('The right audience for your brand', 'right audience'),
                'why' => self::h('Why exhibit?', 'exhibit?'),
                'who' => self::h('Who should exhibit', 'exhibit'),
                'floor' => self::h('Find your spot on the floor', 'floor'),
                'cta' => self::h('Book your stand at the summit', 'the summit'),
            ],
            'why' => array_map(fn ($t, $i) => ['title' => $t, 'icon' => ['people', 'briefcase', 'rocket'][$i], 'tone' => ['tone-teal', 'tone-sun', 'tone-violet'][$i], 'photo' => ['/media/photos/highlight-networking.jpg', '/media/photos/highlight-career.jpg', '/media/photos/highlight-marketplace.jpg'][$i] ?? '/media/photos/exhibit-booth.jpg'], $x['why'], array_keys($x['why'])),
            'who' => array_map(fn ($t, $i) => ['title' => $t, 'icon' => ['globe', 'building', 'cap', 'chart'][$i] ?? 'store'], $x['who'], array_keys($x['who'])),
            'booths' => $booths = $this->exhibitors->booths(),
            // Audience band: counts straight from the booth table plus the summit's reach, nothing estimated.
            'stats' => [
                ['value' => (string) count($booths), 'label' => 'Exhibition booths'],
                ['value' => (string) count(array_filter($booths, fn ($b) => $b['status'] === 'available')), 'label' => 'Booths still available'],
                ['value' => '8', 'label' => 'Divisions represented'],
            ],
            'legend' => [['value' => 'available', 'label' => 'Available'], ['value' => 'reserved', 'label' => 'Reserved'], ['value' => 'confirmed', 'label' => 'Confirmed']],
        ];
    }

    /** The full exhibitor list: every exhibitor in sequence, no search or filters. */
    public function exhibitors(): array
    {
        return [
            'exhibitors' => $this->exhibitors->all(),
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
                'crumbs' => [['label' => 'Exhibition', 'href' => '/exhibition'], ['label' => 'Exhibitors', 'href' => '/exhibition/exhibitors'], ['label' => $e['name']]],
                'hero' => ['title' => $e['name'], 'emphasis' => '', 'intro' => $e['sector'], 'image' => '/media/photos/exhibit-booth.jpg', 'actions' => []],
            ],
            'exhibitor' => $e,
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
                'benefits' => self::h('Member benefits', 'benefits'),
                'how' => self::h('How to join', 'join'),
                'activities' => self::h('Community and activities', 'activities'),
                'faq' => self::h('Questions about membership', 'membership'),
                'cta' => self::h('Join the BACCO Freelancer Forum', 'Freelancer Forum'),
            ],
            'forum' => $f,
            // About-page style intro under the hero: BACCO's own description of the forum.
            'intro' => [
                'title' => 'BACCO Freelancer Forum',
                'emphasis' => 'Freelancer Forum',
                'paragraphs' => [
                    'Bangladesh Association of Contact Center and Outsourcing (BACCO) has recently launched “BACCO Online Professionals Forum (BOPF)” in October, 2020 to support the talented online professional community in the journey of being an entrepreneur from solopreneur. The online professional’s community and BACCO will work together to eventually develop the overall BPO industry as outsourcing has been a major functional part of this large community and this collaboration will excavate numerous openings.',
                    'Honorable Minister of State for the ICT Division, Mr. Zunaid Ahmed Palak, MP inaugurated the forum through a virtual launching ceremony with the presence of BACCO Executive Committee, top freelancers and BPO stakeholders.',
                    'Mission: Development of the online professionals’ community of Bangladesh by assisting the established and potential freelancers for building entrepreneurial projects.',
                    'Vision: To make a core platform where proficient freelancers will come together to share knowledge and skills to create jobs, earn foreign remittance and work together for a viable journey.',
                ],
            ],
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
        $partners = $this->home->partners();

        return [
            'headings' => [
                'organisers' => self::h('Organised by', 'by'),
                // The landing's partner wall, under its own title (the hero already says "Built with partners")
                'wall' => ['title' => 'Our partners and sponsors', 'emphasis' => 'partners and sponsors'],
            ],
            'organisers' => $site->organisers(),
            'partners' => $partners,
        ];
    }
}
