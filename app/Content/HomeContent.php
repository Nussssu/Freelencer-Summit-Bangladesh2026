<?php

namespace App\Content;

use Illuminate\Support\Str;

/**
 * Homepage section content. Each public method feeds one section, keeps the
 * shape the Vue types expect (resources/js/types/content.ts) and can later be
 * replaced by an Eloquent-backed implementation without touching components.
 *
 * Rules: no invented people, quotes, statistics or sponsors. Missing data is
 * null and the UI renders "TBA" / "Revealing soon" / "Story coming soon".
 * Anything illustrative carries 'sample' => true. Temporary exceptions are
 * marked `shortcut:` (speakers, sponsors, photos).
 */
class HomeContent
{
    // shortcut: placeholder photos of other events, source/licence unrecorded; replace with real summit photos before launch.
    private const PHOTOS = '/media/photos/';

    private static function sponsorLogo(string $name): string
    {
        return '/media/sponsors/'.Str::slug($name).'.svg';
    }

    public function __construct(private array $summit) {}

    public static function fromConfig(): self
    {
        return new self(config('summit'));
    }

    private function counts(): array
    {
        return [$this->summit['divisions'], $this->summit['districts'], $this->summit['campuses']];
    }

    public function hero(): array
    {
        [$div, $dist, $camp] = $this->counts();

        return [
            'eyebrow' => "National flagship · {$div} divisions · {$dist} districts · {$camp} campuses",
            'messages' => [
                "Meet the people building Bangladesh's digital economy.",
                'A hundred campuses. Twelve districts. Eight divisions. One stage.',
                'From your first gig to your first company.',
                'One national platform for every freelancer.',
            ],
            'marquee' => ['Freelancing', 'AI services', 'BPO', 'ITES', 'Startups', 'Marketplaces', 'Skills'],
            // Pre-rendered from the Delta Flow scene (see docs/PROGRESS.md): fallback for mobile, reduced motion and no WebGL.
            'video' => [
                'src' => '/media/delta-flow.webm', 'poster' => '/media/delta-flow-poster.jpg',
                'srcMobile' => '/media/delta-flow-m.webm', 'posterMobile' => '/media/delta-flow-poster-m.jpg',
            ],
        ];
    }

    public function trust(): array
    {
        return [
            'ticker' => [
                'Date & venue: to be confirmed',
                'Speaker announcements: coming soon',
                'District roadshow schedule: coming soon',
                'Award nominations: opening soon',
                'Exhibition enquiries: open now',
            ],
            // Content › Partners; the coded names below only stand in while that table is empty.
            'groups' => CmsData::partnerGroups() ?? array_map(fn ($label, $names) => ['label' => $label, 'slots' => count($names), 'partners' => array_map(fn ($n) => ['name' => $n, 'logo' => self::sponsorLogo($n)], $names)],
                ['Government & development partners', 'Strategic partners', 'Supporting organisations'],
                [['Digital Growth Agency', 'Skills for Jobs Programme', 'Asia Development Fund'], ['Global BPO Partners', 'TechCorp IT', 'Bangla Talent Hub'], ['Freelance Pro Network', 'CodeNest Labs', 'Future Skills Academy', 'PayLink BD']],
            ),
        ];
    }

    public function about(): array
    {
        return [
            'eyebrow' => 'About the Summit',
            'headline' => "What is Freelancer Summit Bangladesh {$this->summit['edition']}?",
            'emphasis' => 'Freelancer Summit',
            'intro' => "Bangladesh's national flagship platform bringing together freelancers, students, digital professionals, industry leaders, and global partners to advance digital skills, AI, and global opportunities.",
            'points' => [
                ['title' => 'National flagship event', 'icon' => 'people'],
                ['title' => 'Skills, AI & opportunities', 'icon' => 'ai'],
                ['title' => 'Connecting talent to the world', 'icon' => 'globe'],
            ],
            'cta' => ['label' => 'Explore the Summit', 'href' => '/about'],
            // shortcut: the photo half of the client's design mock-up (a rendered visual, not a real event photo); replace with summit photography.
            'image' => self::PHOTOS.'about-summit-light.jpg',
            'video' => null, // overview film not produced yet; the button opens a "coming soon" notice
            'baccoStats' => ($this->summit['show_bacco_stats'] ?? false) ? [
                ['value' => '2011', 'label' => 'BACCO founded'],
                ['value' => '500+', 'label' => 'Member organisations'],
                ['value' => '100,000', 'label' => 'Jobs'],
                ['value' => '$1B', 'label' => 'Annual revenue'],
            ] : null,
            'baccoStatsSource' => 'As published on bposummit.org.bd',
        ];
    }

    public function journey(): array
    {
        $journey = $this->codedJourney();
        // Counters from the held programmes (Event Management), a Grand Summit figure above 0 overriding.
        if ($counts = CmsData::journeyCounters($this->summit['counters'])) {
            foreach ($journey['counters'] as &$c) {
                $c['value'] = $counts[$c['id']];
            }
            unset($c);
        }
        if (($rows = CmsData::directoryRows()) !== null) {
            $journey['directory'] = ['sample' => false, 'rows' => $rows];
        }

        return $journey;
    }

    private function codedJourney(): array
    {
        [$div, $dist, $camp] = $this->counts();
        $edition = $this->summit['edition'];

        return [
            'headline' => "{$div} divisions. {$dist} districts. {$camp} campuses. One Dhaka.",
            'headlineEmphasis' => 'One Dhaka.',
            'steps' => [
                ['count' => $camp, 'title' => "{$camp} Campuses", 'tag' => 'Campus Programs', 'href' => '/campus-programs',
                    'text' => 'Seminars, workshops, career talks, AI awareness and mentor sessions at universities and polytechnics.'],
                ['count' => $dist, 'title' => "{$dist} Districts", 'tag' => 'District Roadshows', 'href' => '/district-roadshows',
                    'text' => 'Roadshows with skill development, success stories, an AI showcase and marketplace sessions.'],
                ['count' => $div, 'title' => "{$div} Divisions", 'tag' => 'Divisional Summits', 'href' => '/divisional-summits',
                    'text' => 'One summit in every division brings its districts and campuses together.'],
                ['count' => 1, 'title' => 'Grand Summit, Dhaka', 'tag' => 'Grand Summit', 'href' => '/grand-summit',
                    'text' => "Everything meets in Dhaka in late November {$edition}: keynotes, awards, exhibition and networking."],
            ],
            'beaconLabel' => "Nov {$edition}",
            // Capital coordinates (lat, lng) for the 8 divisions.
            'divisions' => [
                ['name' => 'Dhaka', 'lat' => 23.8103, 'lng' => 90.4125],
                ['name' => 'Chattogram', 'lat' => 22.3569, 'lng' => 91.7832],
                ['name' => 'Rajshahi', 'lat' => 24.3745, 'lng' => 88.6042],
                ['name' => 'Khulna', 'lat' => 22.8456, 'lng' => 89.5403],
                ['name' => 'Barishal', 'lat' => 22.7010, 'lng' => 90.3535],
                ['name' => 'Sylhet', 'lat' => 24.8949, 'lng' => 91.8687],
                ['name' => 'Rangpur', 'lat' => 25.7439, 'lng' => 89.2752],
                ['name' => 'Mymensingh', 'lat' => 24.7471, 'lng' => 90.4203],
            ],
            'counters' => [
                ['id' => 'divisions_covered', 'label' => 'Divisions covered', 'value' => $this->summit['counters']['divisions_covered'], 'of' => $div],
                ['id' => 'districts_covered', 'label' => 'Districts covered', 'value' => $this->summit['counters']['districts_covered'], 'of' => $dist],
                ['id' => 'institutions_activated', 'label' => 'Institutions activated', 'value' => $this->summit['counters']['institutions_activated'], 'of' => $camp],
                ['id' => 'participants_reached', 'label' => 'Participants reached', 'value' => $this->summit['counters']['participants_reached'], 'of' => null],
            ],
            'directory' => [
                'sample' => true,
                'rows' => [
                    ['type' => 'division', 'name' => 'Chattogram Divisional Summit', 'location' => 'Chattogram', 'date' => null, 'partner' => null, 'href' => '/divisional-summits'],
                    ['type' => 'division', 'name' => 'Rajshahi Divisional Summit', 'location' => 'Rajshahi', 'date' => null, 'partner' => null, 'href' => '/divisional-summits'],
                    ['type' => 'district', 'name' => 'District Roadshow 01', 'location' => null, 'date' => null, 'partner' => null, 'href' => '/district-roadshows'],
                    ['type' => 'district', 'name' => 'District Roadshow 02', 'location' => null, 'date' => null, 'partner' => null, 'href' => '/district-roadshows'],
                    ['type' => 'campus', 'name' => 'Campus Program 001', 'location' => null, 'date' => null, 'partner' => null, 'href' => '/campus-programs'],
                    ['type' => 'campus', 'name' => 'Campus Program 002', 'location' => null, 'date' => null, 'partner' => null, 'href' => '/campus-programs'],
                ],
            ],
        ];
    }

    public function audiences(): array
    {
        // summary + icon feed the homepage cards; benefits/cta are used by the inner About page.
        $row = fn (string $title, string $summary, string $icon, array $benefits, string $cta, string $href) => compact('title', 'summary', 'icon', 'benefits', 'cta', 'href');

        return [
            $row('Freelancers', 'Expand your skills, meet clients, and grow your freelancing career.', 'people', ['Meet marketplace representatives in person', 'Pricing, contracts and getting paid from abroad', 'Be seen by buyers and the national awards'], 'Register for Summit', '/register'),
            $row('Aspiring Freelancers', 'Learn from experts and get guidance to start your journey.', 'rocket', ['Learn which skills clients pay for now', 'Workshops on landing a first client', 'Mentors who started where you are'], 'Register for Summit', '/register'),
            $row('Students', 'Explore digital careers, gain industry insights, and find mentorship.', 'cap', ['A campus program may come to your institution', 'Career talks on ITES, BPO and AI services', 'Become a campus ambassador'], 'See campus programs', '/campus-programs'),
            $row('Entrepreneurs', 'Discover new partnerships and opportunities to scale your business.', 'bulb', ['Freelancer-to-entrepreneur sessions', 'Meet investors and partner companies', 'Build a team from the talent you meet'], 'Register for Summit', '/register'),
            $row('Companies', 'Connect with skilled talent and reliable service providers.', 'building', ['Hire skilled freelancers and graduates', 'Exhibit your services to thousands', 'Shape the next generation of talent'], 'Exhibit at Summit', '/exhibition/apply'),
            $row('Buyers', 'Find trusted freelancers and digital teams for your next project.', 'briefcase', ['Find vetted Bangladeshi talent', 'See work across design, development and AI', 'Meet service providers face to face'], 'Register for Summit', '/register'),
            $row('Marketplaces', "Engage with Bangladesh's growing talent ecosystem.", 'globe', ['Reach freelancers in every division', 'Run onboarding and marketplace sessions', 'Partner with the national campaign'], 'Become a Sponsor', '/sponsorship-opportunity'),
            $row('Investors', 'Explore emerging talent, innovative ventures, and growth opportunities.', 'chart', ['Meet founders who grew out of freelancing', 'See the AI and services startups forming', 'Connect with BACCO members'], 'Register for Summit', '/register'),
            $row('Partners', 'Build meaningful collaborations and drive collective impact.', 'handshake', ['Put your name on a national campaign', 'Support campus and district programmes', 'Category-based visibility on site and stage'], 'Become a Sponsor', '/sponsorship-opportunity'),
        ];
    }

    public function highlights(): array
    {
        $h = fn (string $title, string $text, string $image) => ['title' => $title, 'text' => $text, 'image' => self::PHOTOS."highlight-{$image}.jpg"];

        return [
            $h('Freelancer Awards', 'National recognition for the best work across categories.', 'awards'),
            $h('Keynote sessions', 'Policy makers, global experts and marketplace leaders on one stage.', 'keynote'),
            $h('AI showcase', 'Live demonstrations of AI tools freelancers can use today.', 'ai'),
            $h('Marketplace sessions', 'Platform representatives on profiles, ranking and payments.', 'marketplace'),
            $h('Workshops', 'Hands-on skill sessions in small groups.', 'workshops'),
            $h('Career talks', 'Routes into ITES, BPO and digital services careers.', 'career'),
            $h('Success stories', 'Bangladeshi freelancers and founders tell how they did it.', 'stories'),
            $h('Exhibition', 'Companies, marketplaces and training providers in one hall.', 'exhibition'),
            $h('Networking', 'Meet buyers, mentors, investors and each other.', 'networking'),
        ];
    }

    /** Event Management › Speakers & experts (the coded list below only stands in while that table is empty). */
    public function speakers(): array
    {
        return CmsData::speakers() ?? $this->codedSpeakers();
    }

    // shortcut: guests and speakers of BPO Summit 2026 (bposummit.org.bd/speakers), not confirmed for this summit; imported into the speakers table.
    public function codedSpeakers(): array
    {
        $people = [
            ['Chief Guest', 'Mir Mohammed Helal Uddin, MP', 'Honorable State Minister', 'Ministry of Land and Ministry of Chittagong Hill Tracts Affairs', 'helal-uddin.jpg'],
            ['Special Guest', 'Tanvir Ibrahim', 'President', 'BACCO', 'tanvir-ibrahim.jpg'],
            ['Special Guest', 'Faisal Alim', 'Secretary General', 'BACCO', 'faisal-alim.jpg'],
            ['Guest', 'Sayma Shawkat', 'Director', 'BACCO', 'sayma-shawkat.jpg'],
            ['Speaker', 'Md Musnad E Ahmed', 'Founder and CEO', 'SkyTech Solutions', 'musnad-e-ahmed.png'],
            ['Speaker', 'Shuvasish Bhowmick', 'Managing Director', 'ATEC Australia International Ltd', 'shuvasish-bhowmick.jpg'],
            ['Speaker', 'Emrazina Islam', 'Founder', 'Kaz360', 'emrazina-islam.jpg'],
            ['Speaker', 'Abdur Rashid Sohag', 'Founder and CEO, School Bus', 'President, Startup Association of Bangladesh', 'abdur-rashid-sohag.jpg'],
            ['Speaker', 'Saber Shah', 'Founder', 'AW Communication', 'saber-shah.jpg'],
        ];

        return array_map(fn ($p, $i) => [
            'slug' => Str::slug($p[1]),
            'role' => $p[0],
            'name' => $p[1],
            'designation' => $p[2],
            'organisation' => $p[3],
            'country' => 'Bangladesh',
            'photo' => '/media/speakers/'.$p[4],
            'bio' => null,
            'credential' => null,
            'expertise' => [],
            'links' => [],
            'featured' => $i === 0,
        ], $people, array_keys($people));
    }

    /** Event Management › Agenda & sessions (the coded rows below only stand in while that table is empty). */
    public function agenda(): array
    {
        return CmsData::agenda() ?? $this->codedAgenda();
    }

    public function codedAgenda(): array
    {
        $s = fn (string $type, string $title, string $time, string $hall) => [
            'type' => $type, 'title' => $title, 'day' => 'Day 1', 'time' => $time, 'hall' => $hall, 'speaker' => null,
        ];

        return [
            'sample' => true,
            'types' => ['Keynote', 'Seminar', 'Workshop', 'Career Talk', 'Panel', 'AI Awareness', 'Success Story', 'Marketplace Session', 'Freelancer-to-Entrepreneur', 'Award Session', 'Networking'],
            'sessions' => [
                $s('Keynote', 'Opening keynote', '10:00–10:45', 'Main Hall'),
                $s('Marketplace Session', 'Inside the marketplaces: profiles, ranking and payments', '11:00–11:45', 'Hall B'),
                $s('Workshop', 'Hands-on: AI tools for client work', '12:00–13:30', 'Workshop Room 1'),
                $s('Freelancer-to-Entrepreneur', 'From solo freelancer to a registered company', '14:30–15:15', 'Hall B'),
                $s('Award Session', 'Freelancer Awards ceremony', '17:00–18:30', 'Main Hall'),
            ],
        ];
    }

    public function awards(): array
    {
        $awards = $this->codedAwards();
        if ($categories = CmsData::awardCategories()) {
            $open = $categories->where('is_open', true);
            $deadline = $open->pluck('deadline')->filter()->sort()->first();
            $awards['categories'] = $categories->pluck('name')->all();
            // Provisional until every category has its published eligibility.
            $awards['categoriesProvisional'] = $categories->contains(fn ($c) => blank($c->eligibility));
            $awards['deadline'] = $deadline?->format('j F Y');
        }

        return $awards;
    }

    public function codedAwards(): array
    {
        return [
            'headline' => 'Your work deserves a stage.',
            'emphasis' => 'stage.',
            'badge' => "Freelancer Awards {$this->summit['edition']} · Nominations opening soon · ",
            'categories' => ['Rising Freelancer', 'Woman Freelancer', 'AI & Automation', 'Creative & Design', 'Freelancer to Entrepreneur', 'Campus Freelancer'],
            'categoriesProvisional' => true,
            'eligibility' => 'Open to freelancers working from Bangladesh. Final categories, criteria and documents are published with the award guidelines.',
            'statuses' => ['Submitted', 'Under Review', 'Shortlisted', 'Winner'],
            'deadline' => null,
        ];
    }

    public function exhibition(): array
    {
        return [
            'why' => ['Meet freelancers, students and companies from all 8 divisions', 'Recruit skilled talent on the spot', 'Launch services to an audience that buys them'],
            'who' => ['Marketplaces and platforms', 'BPO and ITES companies', 'Training institutes and universities', 'Fintech and payment providers'],
            // Illustrative layout until the venue floor plan exists; every booth is available.
            'booths' => array_map(fn ($i) => ['id' => sprintf('B%02d', $i), 'state' => 'available'], range(1, 24)),
            'boothsIllustrative' => true,
            'enquiryFields' => ['company_name', 'website', 'industry', 'head_office', 'full_name', 'designation', 'email', 'phone'],
        ];
    }

    public function sponsorship(): array
    {
        if ($packages = CmsData::sponsorPackages()) {
            return ['tiers' => array_column($packages, 'name'), 'provisional' => in_array(true, array_column($packages, 'provisional'), true), 'prospectus' => null];
        }

        return [
            'tiers' => ['Title', 'Platinum', 'Gold', 'Silver', 'Strategic Partner', 'Technology Partner', 'Knowledge Partner', 'Media Partner'],
            'provisional' => true,
            'prospectus' => null,
        ];
    }

    public function forum(): array
    {
        return [
            'intro' => 'The BACCO Freelancer Forum is a long-term home for Bangladeshi freelancers, run by the industry body that represents the country\'s contact centre and outsourcing sector.',
            'purpose' => 'Give freelancers a recognised professional community, a voice with industry and government, and support that lasts beyond one event.',
            'eligibility' => 'Membership rules are being finalised by BACCO and will be published on the registration page before applications open.',
            'benefits' => [
                ['title' => 'Recognition', 'text' => 'A professional identity backed by BACCO, including help with Freelancer ID registration.'],
                ['title' => 'Community', 'text' => 'Meet freelancers in your division and field, online and at programmes across the country.'],
                ['title' => 'Skills', 'text' => 'Priority access to workshops, mentor sessions and marketplace briefings.'],
                ['title' => 'Voice', 'text' => 'Bring freelancer issues to industry and policy discussions.'],
            ],
            'faq' => [
                ['q' => 'Who can join the BACCO Freelancer Forum?', 'a' => 'Eligibility is being finalised by BACCO. The registration page will list it before applications open.'],
                ['q' => 'Is there a membership fee?', 'a' => 'Any fees will be announced together with the membership rules.'],
                ['q' => 'Which documents will I need?', 'a' => 'Only what is officially required. The list will be on the registration page; NID is requested only if it is mandatory.'],
            ],
            'portalNote' => 'Member portal coming soon',
        ];
    }

    public function stories(): array
    {
        return [
            'sample' => true,
            'tiles' => [
                ['kind' => 'video', 'role' => 'Freelance video editor', 'youtubeId' => null, 'poster' => self::PHOTOS.'story-video-editor.jpg'],
                ['kind' => 'quote', 'role' => 'Full-stack developer', 'quote' => null],
                ['kind' => 'stat', 'role' => 'Campus ambassador', 'value' => null, 'label' => 'Programmes attended'],
                ['kind' => 'quote', 'role' => 'Graphic designer', 'quote' => null],
                ['kind' => 'quote', 'role' => 'Content writer', 'quote' => null],
                ['kind' => 'stat', 'role' => 'Founder, digital agency', 'value' => null, 'label' => 'Team members hired'],
            ],
            'carousel' => [
                ['programme' => 'Campus Program', 'role' => 'Student, polytechnic', 'quote' => null],
                ['programme' => 'District Roadshow', 'role' => 'First-time freelancer', 'quote' => null],
                ['programme' => 'Divisional Summit', 'role' => 'Agency founder', 'quote' => null],
            ],
        ];
    }

    /** Content › Sponsors by tier (the coded wall below only stands in while that table is empty). */
    public function partners(): array
    {
        return CmsData::sponsorTiers() ?? $this->codedPartners();
    }

    public function codedPartners(): array
    {
        // shortcut: invented sponsors with generated SVG logos (public/media/sponsors), so the wall looks filled; replace with confirmed sponsors.
        $tier = fn (string $name, array $sponsors) => ['name' => $name, 'slots' => count($sponsors), 'sponsors' => array_map(fn ($s) => ['name' => $s, 'logo' => self::sponsorLogo($s)], $sponsors)];

        return [
            'provisional' => true,
            'tiers' => [
                $tier('Title Sponsor', ['TechCorp IT']),
                $tier('Platinum Sponsor', ['Global BPO Partners', 'NexaCloud Systems']),
                $tier('Gold Sponsor', ['Delta Digital Bank', 'BrightPath Outsourcing', 'Meghna Software']),
                $tier('Silver Sponsor', ['PayLink BD', 'CodeNest Labs', 'Padma Data Services', 'Orbit Telecom']),
                $tier('Strategic Partner', ['Bangla Talent Hub', 'SkillBridge Foundation', 'Remote Works Asia']),
                $tier('Technology Partner', ['ByteForge Solutions', 'CloudAxis BD', 'Sundarban AI']),
                $tier('Knowledge Partner', ['Dhaka Tech Institute', 'Future Skills Academy', 'Freelance Pro Network']),
                $tier('Media Partner', ['Daily Tech Times', 'Startup Dhaka Media', 'Digital Bangla TV', 'GigBeat Radio']),
            ],
        ];
    }

    public function media(): array
    {
        $programmes = ['Divisional', 'District', 'Campus'];

        return [
            'tiles' => array_map(fn ($i) => [
                'id' => $i,
                'programme' => $programmes[$i % 3],
                'kind' => $i % 5 === 2 ? 'video' : 'photo',
                'src' => self::PHOTOS.sprintf('gallery-%02d.jpg', $i % 14 + 1),
                'caption' => null,
            ], range(0, 15)),
            'videos' => [
                ['title' => 'Divisional summit highlights', 'youtubeId' => null],
                ['title' => 'District roadshow highlights', 'youtubeId' => null],
                ['title' => 'Campus program highlights', 'youtubeId' => null],
            ],
        ];
    }

    public function finalCta(): array
    {
        return ['line' => 'See you in Dhaka.', 'marquee' => 'Register now'];
    }

    /** Section headlines (title + the one emphasised phrase per headline). */
    public function headings(): array
    {
        $h = fn (string $title, string $emphasis) => compact('title', 'emphasis');

        return [
            'why' => $h('Built for every ambition.', 'every ambition.'),
            'highlights' => $h('Nine reasons Dhaka will be loud.', 'loud.'),
            'speakers' => $h('The people on stage.', 'on stage.'),
            'agenda' => $h('A day built for doing.', 'doing.'),
            'exhibition' => $h('Put your booth where the talent is.', 'talent'),
            'sponsorship' => $h('Back the national campaign.', 'national'),
            'forum' => $h('A community that outlasts the summit.', 'outlasts'),
            'stories' => $h('From first gig to first company.', 'first company.'),
            'partners' => $h('Built with partners.', 'partners.'),
            'news' => $h('News & Updates', 'Updates'),
            'media' => $h('From every corner of the delta.', 'delta.'),
        ];
    }

    /** Everything the homepage needs, one key per section (sections 2–16; header/hero/footer share layout data). */
    public function all(): array
    {
        return [
            'headings' => $this->headings(),
            'hero' => $this->hero(),
            'trust' => $this->trust(),
            'about' => $this->about(),
            'journey' => $this->journey(),
            'audiences' => $this->audiences(),
            'highlights' => $this->highlights(),
            'speakers' => $this->speakers(),
            'agenda' => $this->agenda(),
            'awards' => $this->awards(),
            'exhibition' => $this->exhibition(),
            'sponsorship' => $this->sponsorship(),
            'forum' => $this->forum(),
            'stories' => $this->stories(),
            'storyCards' => CmsData::storyCards(),
            'partners' => $this->partners(),
            'media' => $this->media(),
            'finalCta' => $this->finalCta(),
        ];
    }
}
