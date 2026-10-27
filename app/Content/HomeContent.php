<?php

namespace App\Content;

/**
 * Homepage section content. Each public method feeds one section, keeps the
 * shape the Vue types expect (resources/js/types/content.ts) and can later be
 * replaced by an Eloquent-backed implementation without touching components.
 *
 * Rules: no invented people, quotes, statistics or sponsors. Missing data is
 * null and the UI renders "TBA" / "Revealing soon" / "Story coming soon".
 * Anything illustrative carries 'sample' => true.
 */
class HomeContent
{
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
            'video' => ['src' => '/media/delta-flow.webm', 'poster' => '/media/delta-flow-poster.jpg'],
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
            'groups' => [
                ['label' => 'Government & development partners', 'slots' => 3, 'partners' => []],
                ['label' => 'Strategic partners', 'slots' => 3, 'partners' => []],
                ['label' => 'Supporting organisations', 'slots' => 4, 'partners' => []],
            ],
        ];
    }

    public function about(): array
    {
        return [
            'statement' => 'Freelancing should be one of the most respected careers in Bangladesh. This is where that starts.',
            'highlight' => 'one of the most respected careers in Bangladesh',
            'paragraphs' => [
                'Freelancer Summit Bangladesh is a national platform for freelancers, aspiring freelancers, AI practitioners, entrepreneurs, marketplaces and the companies that hire them.',
                'It connects university and polytechnic students with the global digital economy, through programmes on their own campuses and in their own districts.',
                'It follows the whole journey: the first gig, a skilled career, and the step from freelancer to entrepreneur.',
            ],
            'stamp' => 'Not a one-day event',
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
        $row = fn (string $title, array $benefits, string $cta, string $href) => compact('title', 'benefits', 'cta', 'href');

        return [
            $row('Freelancers', ['Meet marketplace representatives in person', 'Pricing, contracts and getting paid from abroad', 'Be seen by buyers and the national awards'], 'Register for Summit', '/register'),
            $row('Aspiring freelancers', ['Learn which skills clients pay for now', 'Workshops on landing a first client', 'Mentors who started where you are'], 'Register for Summit', '/register'),
            $row('Students', ['A campus program may come to your institution', 'Career talks on ITES, BPO and AI services', 'Become a campus ambassador'], 'See campus programs', '/campus-programs'),
            $row('Entrepreneurs', ['Freelancer-to-entrepreneur sessions', 'Meet investors and partner companies', 'Build a team from the talent you meet'], 'Register for Summit', '/register'),
            $row('Companies', ['Hire skilled freelancers and graduates', 'Exhibit your services to thousands', 'Shape the next generation of talent'], 'Exhibit at Summit', '/exhibition/apply'),
            $row('Buyers', ['Find vetted Bangladeshi talent', 'See work across design, development and AI', 'Meet service providers face to face'], 'Register for Summit', '/register'),
            $row('Marketplaces', ['Reach freelancers in every division', 'Run onboarding and marketplace sessions', 'Partner with the national campaign'], 'Become a Sponsor', '/sponsorship-opportunity'),
            $row('Investors', ['Meet founders who grew out of freelancing', 'See the AI and services startups forming', 'Connect with BACCO members'], 'Register for Summit', '/register'),
            $row('Partners', ['Put your name on a national campaign', 'Support campus and district programmes', 'Category-based visibility on site and stage'], 'Become a Sponsor', '/sponsorship-opportunity'),
        ];
    }

    public function highlights(): array
    {
        $h = fn (string $title, string $text) => compact('title', 'text');

        return [
            $h('Freelancer Awards', 'National recognition for the best work across categories.'),
            $h('Keynote sessions', 'Policy makers, global experts and marketplace leaders on one stage.'),
            $h('AI showcase', 'Live demonstrations of AI tools freelancers can use today.'),
            $h('Marketplace sessions', 'Platform representatives on profiles, ranking and payments.'),
            $h('Workshops', 'Hands-on skill sessions in small groups.'),
            $h('Career talks', 'Routes into ITES, BPO and digital services careers.'),
            $h('Success stories', 'Bangladeshi freelancers and founders tell how they did it.'),
            $h('Exhibition', 'Companies, marketplaces and training providers in one hall.'),
            $h('Networking', 'Meet buyers, mentors, investors and each other.'),
        ];
    }

    /** Speaker slots: data shape is final, people are announced later. */
    public function speakers(): array
    {
        $roles = ['Chief Guest', 'Special Guest', 'Special Guest', 'Guest', 'Guest', 'Speaker', 'Speaker', 'Speaker'];

        return array_map(fn ($role, $i) => [
            'slug' => 'speaker-'.($i + 1),
            'role' => $role,
            'name' => null,
            'designation' => null,
            'organisation' => null,
            'country' => null,
            'photo' => null,
            'bio' => null,
            'credential' => null,
            'expertise' => [],
            'links' => [],
            'featured' => $i === 0,
        ], $roles, array_keys($roles));
    }

    public function agenda(): array
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
        return [
            'headline' => 'Your work deserves a stage.',
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
                ['kind' => 'video', 'role' => 'Freelance video editor', 'youtubeId' => null],
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

    public function partners(): array
    {
        $tier = fn (string $name, int $slots) => ['name' => $name, 'slots' => $slots, 'sponsors' => []];

        return [
            'provisional' => true,
            'tiers' => [
                $tier('Title Sponsor', 1), $tier('Platinum', 2), $tier('Gold', 3), $tier('Silver', 4),
                $tier('Strategic Partner', 3), $tier('Technology Partner', 3), $tier('Knowledge Partner', 3), $tier('Media Partner', 4),
            ],
        ];
    }

    public function news(): array
    {
        $n = fn (string $category, string $title) => ['category' => $category, 'title' => $title, 'date' => null, 'slug' => null];

        return [
            $n('Announcement', 'Official summit announcements will be published here.'),
            $n('Roadshow update', 'District roadshow schedule: coming soon.'),
            $n('Speaker announcement', 'The first speakers will be announced here.'),
            $n('Media coverage', 'Press coverage of the campaign will be collected here.'),
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
                'src' => null,
                'caption' => null,
            ], range(0, 13)),
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

    /** Everything the homepage needs, one key per section (sections 2–16; header/hero/footer share layout data). */
    public function all(): array
    {
        return [
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
            'partners' => $this->partners(),
            'news' => $this->news(),
            'media' => $this->media(),
            'finalCta' => $this->finalCta(),
        ];
    }
}
