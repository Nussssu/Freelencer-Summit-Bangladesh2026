<?php

namespace App\Content;

use App\Enums\DownloadKind;
use App\Models\NewsPost;
use App\Queries\ContentQuery;
use App\Queries\NewsQuery;
use Illuminate\Support\Str;

/**
 * Hero copy and body sections for the built main-navigation pages. Title/description for SEO stay in PageContent.
 * Wording follows the client brief (section numbers in comments). Shared data (speakers, agenda, journey, awards)
 * comes from HomeContent so the landing and inner pages never disagree. No invented people, numbers or dates.
 */
class InnerPageContent
{
    /** Summit days shown on the agenda and in its PDF, whether or not sessions are published for them yet. */
    public const AGENDA_DAYS = ['Day 1', 'Day 2', 'Day 3'];

    public function __construct(private HomeContent $home, private NewsQuery $news) {}

    /** Section heading: eyebrow label, title (emphasis is a substring of it), optional one-line intro. */
    private static function h(string $eyebrow, string $title, ?string $emphasis = null, ?string $intro = null): array
    {
        return compact('eyebrow', 'title', 'emphasis', 'intro');
    }

    /**
     * Route pattern => Vue page + hero (emphasis must be a substring of title; one gold action per page).
     * `content` points at the class method that builds the page body (default: this class, lcfirst(component)).
     */
    public const PAGES = [
        '/about' => [
            'component' => 'About',
            'title' => 'About Summit',
            'emphasis' => 'Summit',
            // shortcut: Unsplash+ preview (watermarked); replace with the licensed download before launch.
            'image' => '/media/photos/about-hero.jpg',
            // The title-only hero shows no action; the ghost one closes the page beside Register.
            'actions' => [
                ['label' => 'Explore the Summit', 'href' => '/grand-summit', 'tone' => 'gold'],
                ['label' => 'Get Involved', 'href' => '/contact', 'tone' => 'ghost'],
            ],
        ],
        '/national-journey' => [
            'component' => 'NationalJourney',
            // Title-only hero like About Summit (shared NewsHero); intro and actions stay for the closing band below.
            'title' => 'National Journey',
            'emphasis' => 'Journey',
            'intro' => 'Campus programs, district roadshows and divisional summits across all 8 divisions build the campaign that leads to the Grand Summit in Dhaka.',
            'image' => '/media/photos/journey-hero.jpg',
            'actions' => [
                ['label' => 'Register for Summit', 'href' => '/register', 'tone' => 'gold'],
                ['label' => 'Find a district roadshow', 'href' => '/district-roadshows', 'tone' => 'ghost'],
            ],
        ],
        '/divisional-summits' => [
            'component' => 'Programs', 'content' => [Pages\JourneyPages::class, 'divisional'],
            'title' => 'A summit in every division',
            'emphasis' => 'every division',
            'intro' => "Eight divisional summits bring each division's districts and campuses together on the road to the Grand Summit in Dhaka.",
            'image' => '/media/photos/gallery-07.jpg',
            'actions' => [['label' => 'Register for Summit', 'href' => '/register', 'tone' => 'gold'], ['label' => 'See the national journey', 'href' => '/national-journey', 'tone' => 'ghost']],
        ],
        '/divisional-summits/{slug}' => ['component' => 'ProgramDetail', 'content' => [Pages\JourneyPages::class, 'divisionalDetail'], 'title' => 'Divisional summit'],
        '/district-roadshows' => [
            'component' => 'DistrictRoadshows', 'content' => [Pages\JourneyPages::class, 'district'],
            'title' => 'District Roadshows',
            'emphasis' => 'Roadshows',
            'intro' => 'Career awareness, skill development, success stories, an AI showcase and marketplace sessions, brought to districts across Bangladesh.',
            'image' => '/media/photos/gallery-10.jpg',
            'actions' => [['label' => 'Register for Summit', 'href' => '/register', 'tone' => 'gold'], ['label' => 'Divisional summits', 'href' => '/divisional-summits', 'tone' => 'ghost']],
        ],
        '/district-roadshows/{slug}' => ['component' => 'ProgramDetail', 'content' => [Pages\JourneyPages::class, 'districtDetail'], 'title' => 'District roadshow'],
        '/campus-programs' => [
            'component' => 'Programs', 'content' => [Pages\JourneyPages::class, 'campus'],
            'title' => 'Bringing the summit to campus',
            'emphasis' => 'to campus',
            'intro' => 'Seminars, workshops, career talks, AI awareness and mentor sessions at universities, polytechnics and higher education institutions.',
            'image' => '/media/photos/highlight-career.jpg',
            'actions' => [['label' => 'Become a campus ambassador', 'href' => '#ambassador', 'tone' => 'gold'], ['label' => 'Find a programme', 'href' => '#directory', 'tone' => 'ghost']],
        ],
        '/campus-programs/{slug}' => ['component' => 'ProgramDetail', 'content' => [Pages\JourneyPages::class, 'campusDetail'], 'title' => 'Campus program'],
        '/grand-summit' => [
            'component' => 'GrandSummit', 'content' => [Pages\JourneyPages::class, 'grandSummit'],
            'title' => 'The Grand Summit in Dhaka',
            'emphasis' => 'in Dhaka',
            'intro' => 'Where the national journey ends: keynotes, the Freelancer Awards, seminars, workshops, exhibition and networking in Dhaka.',
            'image' => '/media/photos/agenda-hero.jpg',
            'actions' => [['label' => 'Register for Summit', 'href' => '/register', 'tone' => 'gold'], ['label' => 'Explore the agenda', 'href' => '/agenda', 'tone' => 'ghost']],
        ],
        '/agenda' => [
            'component' => 'Agenda',
            'title' => 'Summit Agenda',
            'emphasis' => 'Agenda',
            'intro' => 'Keynotes, panels, workshops, career talks, marketplace sessions and the Freelancer Awards. The agenda is published as sessions are confirmed.',
            'image' => '/media/photos/agenda-hero.jpg',
            'actions' => [
                ['label' => 'Register for Summit', 'href' => '/register', 'tone' => 'gold'],
                ['label' => 'Meet the speakers', 'href' => '/speakers', 'tone' => 'ghost'],
            ],
        ],
        '/agenda/session/{slug}' => ['component' => 'AgendaSessionDetail', 'content' => [self::class, 'agendaSession'], 'title' => 'View Session Details', 'emphasis' => 'Details', 'image' => '/media/photos/agenda-hero.jpg'],
        '/speakers' => [
            'component' => 'Speakers',
            'title' => 'Speakers & Experts',
            'emphasis' => 'Experts',
            'intro' => 'International speakers, trainers, successful freelancers, entrepreneurs, policymakers and marketplace representatives, announced as they confirm.',
            'image' => '/media/photos/speakers-hero.jpg',
            'actions' => [
                ['label' => 'Register for Summit', 'href' => '/register', 'tone' => 'gold'],
                ['label' => 'Explore the agenda', 'href' => '/agenda', 'tone' => 'ghost'],
            ],
        ],
        '/speaker/{slug}' => ['component' => 'SpeakerProfile', 'content' => [Pages\PeoplePages::class, 'speaker'], 'title' => 'Speaker'],
        '/awards' => [
            'component' => 'Awards',
            'title' => 'Freelancer Awards',
            'emphasis' => 'Awards',
            'intro' => 'National awards for freelancers and freelancer-led businesses. Nomination is separate from summit registration.',
            'image' => '/media/photos/awards-hero.jpg',
            'actions' => [
                ['label' => 'Nominate for Awards', 'href' => '/awards/nominate', 'tone' => 'gold'],
                ['label' => 'View award categories', 'href' => '/awards#categories', 'tone' => 'ghost'],
            ],
        ],
        '/awards/nominate' => [
            'component' => 'AwardNominate', 'content' => [Pages\AwardPages::class, 'nominate'],
            'crumbs' => [['label' => 'Freelancer Awards', 'href' => '/awards'], ['label' => 'Nominate']],
            'title' => 'Award Nomination',
            'emphasis' => 'Nomination',
            'intro' => 'Nominate yourself or someone you know for the Freelancer Awards 2026.',
            // shortcut: cut from the client's nomination mock-up (low resolution, soft-edged PNG); replace with the original.
            'image' => '/media/photos/award-trophy.png',
        ],
        '/awards/winners' => [
            'component' => 'AwardWinners', 'content' => [Pages\AwardPages::class, 'winners'],
            'crumbs' => [['label' => 'Freelancer Awards', 'href' => '/awards'], ['label' => 'Winners']],
            'title' => 'Freelancer Award Winners',
            'emphasis' => 'Winners',
            'image' => '/media/photos/awards-hero.jpg',
            'actions' => [
                ['label' => 'Nominate for Awards', 'href' => '/awards/nominate', 'tone' => 'gold'],
                ['label' => 'Back to the awards', 'href' => '/awards', 'tone' => 'ghost'],
            ],
        ],
        '/awards/nominees' => [
            'component' => 'AwardNominees', 'content' => [Pages\AwardPages::class, 'nominees'],
            'crumbs' => [['label' => 'Freelancer Awards', 'href' => '/awards'], ['label' => 'Shortlisted Nominees']],
            'title' => 'Shortlisted Nominees',
            'emphasis' => 'Nominees',
            'image' => '/media/photos/awards-hero.jpg',
            'actions' => [
                ['label' => 'Nominate for Awards', 'href' => '/awards/nominate', 'tone' => 'gold'],
                ['label' => 'Back to the awards', 'href' => '/awards', 'tone' => 'ghost'],
            ],
        ],
        '/sponsors' => [
            'component' => 'Sponsors', 'content' => [Pages\BusinessPages::class, 'sponsors'],
            'title' => 'The organisations behind the summit',
            'emphasis' => 'behind the summit',
            'intro' => 'Sponsors and partners by category. Confirmed sponsor logos appear here as agreements are signed.',
            'image' => '/media/photos/sponsor-hall.jpg',
            'actions' => [['label' => 'Become a Sponsor', 'href' => '/sponsorship-opportunity', 'tone' => 'gold'], ['label' => 'All partners', 'href' => '/partners', 'tone' => 'ghost']],
        ],
        '/sponsorship-opportunity' => [
            'component' => 'Sponsorship', 'content' => [Pages\BusinessPages::class, 'sponsorship'],
            'title' => 'Back the national campaign',
            'emphasis' => 'national campaign',
            'intro' => 'Put your brand in front of freelancers, students and companies in all 8 divisions, on site and on stage in Dhaka.',
            'image' => '/media/photos/involved-sponsor.jpg',
            'actions' => [['label' => 'Send an enquiry', 'href' => '#enquiry', 'tone' => 'gold'], ['label' => 'See sponsorship tiers', 'href' => '#tiers', 'tone' => 'ghost']],
        ],
        '/exhibition' => [
            'component' => 'Exhibition', 'content' => [Pages\BusinessPages::class, 'exhibition'],
            'title' => 'Put your booth where the talent is',
            'emphasis' => 'the talent is',
            'intro' => 'Meet freelancers, students and companies from all 8 divisions, recruit on the spot and launch services to the people who buy them.',
            'image' => '/media/photos/exhibition-hero.jpg',
            'actions' => [['label' => 'Exhibit at Summit', 'href' => '/exhibition/apply', 'tone' => 'gold'], ['label' => 'Exhibitor directory', 'href' => '/exhibition/exhibitors', 'tone' => 'ghost']],
        ],
        '/exhibition/exhibitors' => [
            'component' => 'Exhibitors', 'content' => [Pages\BusinessPages::class, 'exhibitors'],
            'crumbs' => [['label' => 'Exhibition', 'href' => '/exhibition'], ['label' => 'Exhibitors']],
            'title' => 'Meet our exhibitors',
            'emphasis' => 'exhibitors',
            'image' => '/media/photos/exhibition-hero.jpg',
            'actions' => [['label' => 'Exhibit at Summit', 'href' => '/exhibition/apply', 'tone' => 'gold']],
        ],
        '/exhibition/exhibitors/{slug}' => ['component' => 'ExhibitorProfile', 'content' => [Pages\BusinessPages::class, 'exhibitor'], 'title' => 'Exhibitor'],
        '/exhibition/apply' => [
            'component' => 'ExhibitionApply', 'content' => [Pages\BusinessPages::class, 'exhibitionApply'],
            'crumbs' => [['label' => 'Exhibition', 'href' => '/exhibition'], ['label' => 'Apply']],
            'title' => 'Exhibition Registration',
            'emphasis' => 'Registration',
            'intro' => 'Apply for an exhibition space to showcase your organization, products and services at the Freelancer Summit Bangladesh 2026.',
            'image' => '/media/photos/involved-exhibit.jpg',
        ],
        '/forum' => [
            'component' => 'Forum', 'content' => [Pages\BusinessPages::class, 'forum'],
            'title' => 'BACCO',
            'emphasis' => '',
            'intro' => 'A long-term professional home for Bangladeshi freelancers, run by the industry body for the contact centre and outsourcing sector.',
            'image' => '/media/photos/forum-hero.jpg',
            'actions' => [['label' => 'Join the forum', 'href' => '/forum/register', 'tone' => 'gold'], ['label' => 'Member benefits', 'href' => '#benefits', 'tone' => 'ghost']],
        ],
        '/forum/register' => [
            'component' => 'ForumRegister', 'content' => [Pages\BusinessPages::class, 'forumRegister'],
            'crumbs' => [['label' => 'BACCO Freelancer Forum', 'href' => '/forum'], ['label' => 'Join']],
            'title' => 'Freelancer Forum Registration',
            'emphasis' => 'Registration',
            'intro' => 'Join the BACCO Freelancer Forum to be part of a national freelancer community, access opportunities, and grow together.',
            'image' => '/media/photos/involved-forum.jpg',
        ],
        '/register' => [
            'component' => 'Register', 'content' => [Pages\RegistrationPages::class, 'register'],
            'title' => 'Register for the Grand Summit',
            'emphasis' => 'Summit',
            'intro' => "Be part of Bangladesh's largest freelance and digital talent summit.",
            'image' => '/media/photos/involved-register.jpg',
        ],
        '/success-stories' => [
            'component' => 'SuccessStories', 'content' => [Pages\MediaPages::class, 'stories'],
            'title' => 'Real people, real impact',
            'emphasis' => 'real impact',
            'intro' => 'Bangladeshi freelancers and entrepreneurs on how they started, grew and built careers from Bangladesh.',
            'image' => '/media/photos/highlight-stories.jpg',
            'actions' => [['label' => 'Register for Summit', 'href' => '/register', 'tone' => 'gold'], ['label' => 'Share your story', 'href' => '/contact?topic=media', 'tone' => 'ghost']],
        ],
        '/news' => [
            'component' => 'News', 'content' => [Pages\MediaPages::class, 'news'],
            'title' => 'Latest News',
            'emphasis' => 'News',
            'intro' => 'Announcements, roadshow updates, speaker announcements, press releases and media coverage.',
            'image' => '/media/photos/highlight-networking.jpg',
            'actions' => [['label' => 'Register for Summit', 'href' => '/register', 'tone' => 'gold'], ['label' => 'Media gallery', 'href' => '/media', 'tone' => 'ghost']],
        ],
        '/news/{slug}' => ['component' => 'Article', 'content' => [Pages\MediaPages::class, 'article'], 'title' => 'News'],
        '/media' => [
            'component' => 'Media', 'content' => [Pages\MediaPages::class, 'media'],
            'title' => 'From every corner of the delta',
            'emphasis' => 'the delta',
            'intro' => 'Photos and videos from divisional summits, district roadshows and campus programs.',
            'image' => '/media/photos/gallery-01.jpg',
            'actions' => [['label' => 'Latest news', 'href' => '/news', 'tone' => 'ghost']],
        ],
        '/partners' => [
            'component' => 'Partners', 'content' => [Pages\BusinessPages::class, 'partners'],
            'title' => 'Built with partners',
            'emphasis' => 'partners',
            'intro' => 'Organisers, government and development partners, strategic partners, supporting organisations and sponsors.',
            'image' => '/media/photos/highlight-networking.jpg',
            'actions' => [['label' => 'Become a Sponsor', 'href' => '/sponsorship-opportunity', 'tone' => 'gold'], ['label' => 'Contact the team', 'href' => '/contact?topic=sponsorship', 'tone' => 'ghost']],
        ],
        '/contact' => [
            'component' => 'Contact', 'content' => [Pages\RegistrationPages::class, 'contact'],
            'title' => 'Talk to the summit team',
            'emphasis' => 'summit team',
            'intro' => 'Choose a topic so your message reaches the right person. We reply by email.',
            'image' => '/media/photos/gallery-12.jpg',
        ],
        '/privacy-policy' => ['component' => 'Legal', 'content' => [Pages\MediaPages::class, 'privacy'], 'title' => 'Privacy policy', 'emphasis' => 'policy', 'intro' => 'How Freelancer Summit Bangladesh collects, uses and protects personal information.', 'image' => '/media/photos/gallery-13.jpg'],
        '/terms' => ['component' => 'Legal', 'content' => [Pages\MediaPages::class, 'terms'], 'title' => 'Terms of use', 'emphasis' => 'use', 'intro' => 'The terms for using this website and its registration forms.', 'image' => '/media/photos/gallery-14.jpg'],
    ];

    /**
     * Brief §1–4 in the client's About mock-up: overview, purpose and vision, key objectives, the national journey,
     * organisers. Block keys are new (not the old why/objectives/phases), so stale CMS blocks cannot reach this shape.
     */
    public function about(): array
    {
        return [
            'headings' => [
                'overview' => self::h('', 'The summit at a glance'),
                'pillars' => self::h('', 'Purpose & Vision', 'Vision'),
                'goals' => self::h('', 'Key Objectives', 'Objectives'),
                'organisers' => self::h('', 'Organized by', 'by'),
                'cta' => self::h('', 'Be part of Bangladesh’s freelance future.', 'future.'),
            ],
            // Brief §1 positioning, laid out like the reference About page (bposummit.org.bd/about-us): name, then prose.
            'intro' => [
                'title' => 'Freelancer Summit Bangladesh 2026',
                'emphasis' => 'Bangladesh 2026',
                'paragraphs' => [
                    'Freelancer Summit Bangladesh 2026 is envisioned as the largest national platform dedicated to freelancers, aspiring freelancers, digital professionals, AI practitioners, entrepreneurs, students, marketplaces, technology companies and the wider digital economy ecosystem of Bangladesh.',
                    'The initiative will not be a single-day event only. It will operate as a national campaign that builds momentum through activities across Bangladesh and culminates in the Grand Freelancer Summit in Dhaka in late November 2026.',
                ],
            ],
            // Figures as given in the client's About mock-up; a leading number rolls up on the page, the rest stays text.
            'overview' => [
                'stats' => [
                    ['icon' => 'people', 'value' => '2,500+', 'label' => 'Attendees'],
                    ['icon' => 'mic', 'value' => '50+', 'label' => 'Speakers'],
                    ['icon' => 'layers', 'value' => '20+', 'label' => 'Sessions & Workshops'],
                    ['icon' => 'network', 'value' => 'Global', 'label' => 'Industry & Platform Presence'],
                ],
            ],
            // shortcut: photos cut from the client's Purpose & Vision mock-up (low resolution); replace with the originals.
            'pillars' => [
                ['icon' => 'target', 'title' => 'Our Purpose', 'text' => 'Empowering freelancers through skill development, professional connections, and meaningful opportunities.', 'image' => '/media/photos/about-purpose.jpg'],
                ['icon' => 'eye', 'title' => 'Our Vision', 'text' => 'Building a globally competitive and inclusive freelancing ecosystem for Bangladesh.', 'image' => '/media/photos/about-vision.jpg'],
            ],
            'goals' => [
                ['icon' => 'laptop', 'title' => 'Advance Digital Skills'],
                ['icon' => 'people', 'title' => 'Create Opportunities'],
                ['icon' => 'layers', 'title' => 'Strengthen the Ecosystem'],
                ['icon' => 'chart', 'title' => 'Connect to Global Markets'],
            ],
            // Logos and names stay in Settings (site.organisers); this is the one-line description per organiser.
            'organisers' => [
                ['short' => 'BACCO', 'text' => 'Bangladesh Association of Contact Center & Outsourcing'],
                ['short' => 'DoICT', 'text' => 'Department of ICT, Government of Bangladesh'],
            ],
        ];
    }

    /** Real event photo per news category, for posts published without a cover. */
    private const NEWS_PHOTOS = [
        'announcement' => '/media/photos/gallery-10.jpg',
        'roadshow' => '/media/photos/gallery-13.jpg',
        'speakers' => '/media/photos/highlight-career.jpg',
        'press' => '/media/photos/gallery-12.jpg',
        'in-the-media' => '/media/photos/highlight-networking.jpg',
    ];

    /**
     * Landing News & Updates marquee: the newest real posts (title, excerpt, date), each opening its article page. Demo rows
     * (is_sample) never show here; with nothing published the section stays hidden.
     */
    public function latestNews(): array
    {
        return NewsPost::where('is_sample', false)->whereNotNull('published_at')->where('published_at', '<=', now())->latest('published_at')->limit(8)->get()
            ->map(fn (NewsPost $p) => [
                'title' => $p->title,
                'text' => $p->excerpt,
                'href' => "/news/{$p->slug}",
                'tag' => $p->published_at->timezone('Asia/Dhaka')->format('d/m/Y'),
                'image' => $p->cover_image ?? self::NEWS_PHOTOS[$p->category->value],
            ])->all();
    }

    /** Brief §15: map, divisions, progress counters, timeline to Dhaka, activation directory. */
    public function nationalJourney(): array
    {
        [$journey, $summits] = $this->journeyDemo();

        return [
            'headings' => [
                'progress' => self::h('Progress', 'The journey so far', 'so far', 'Counters update as programmes take place across the country.'),
                'route' => self::h('The route', 'From grassroots to the Grand Summit', 'Grand Summit'),
                'divisions' => self::h('8 divisions', 'A summit in every division', 'every division', 'Each divisional summit brings its districts and campuses together. Six divisional summits are done; Mymensingh and Dhaka close the route in November.'),
                'finder' => self::h('', 'Find a programme near you.', 'near you.'),
                'cta' => self::h('', 'Bring the summit to your campus', 'your campus'),
            ],
            'journey' => $journey,
            'reel' => [
                ['image' => '/media/photos/gallery-12.jpg', 'place' => 'Rajshahi Divisional Summit', 'date' => '13 Sep 2026'],
                ['image' => '/media/photos/gallery-14.jpg', 'place' => 'Campus program, CUET Chattogram', 'date' => '2 Oct 2026'],
                ['image' => '/media/photos/gallery-13.jpg', 'place' => 'District roadshow, Jashore', 'date' => '18 Oct 2026'],
            ],
            'divisionSummits' => $summits,
        ];
    }

    /**
     * Page-only figures for /national-journey: counters, division summits and directory rows.
     * shortcut: demo figures so the page reads populated; move to config/summit.php or the CMS once programmes report real numbers.
     */
    private function journeyDemo(): array
    {
        $journey = $this->home->journey();
        foreach ($journey['counters'] as &$c) {
            $c['value'] = ['divisions_covered' => 6, 'districts_covered' => 11, 'institutions_activated' => 87, 'participants_reached' => 42500][$c['id']];
        }
        unset($c);

        $d = fn (string $name, string $date, string $venue, int $districts, int $campuses, int $participants, bool $held) => compact('name', 'date', 'venue', 'districts', 'campuses', 'participants', 'held');
        $summits = [
            $d('Rajshahi', '13 Sep 2026', 'Rajshahi University Auditorium', 8, 14, 6200, true),
            $d('Khulna', '20 Sep 2026', 'KUET Convention Hall', 10, 12, 5400, true),
            $d('Rangpur', '27 Sep 2026', 'Begum Rokeya University', 8, 10, 4100, true),
            $d('Chattogram', '4 Oct 2026', 'Chittagong Club Convention Centre', 11, 15, 7800, true),
            $d('Sylhet', '11 Oct 2026', 'SUST Central Auditorium', 4, 9, 3900, true),
            $d('Barishal', '25 Oct 2026', 'Barishal Shilpakala Academy', 6, 8, 3600, true),
            $d('Mymensingh', '8 Nov 2026', 'Bangladesh Agricultural University', 4, 9, 3800, false),
            $d('Dhaka', '15 Nov 2026', 'Bangabandhu International Conference Center', 13, 23, 9500, false),
        ];
        $summits = array_map(fn (array $s, int $i) => $s + ['image' => '/media/photos/gallery-'.str_pad((string) ($i + 4), 2, '0', STR_PAD_LEFT).'.jpg'], $summits, array_keys($summits));

        // shortcut: Rajshahi to Sylhet are cut from the client's finder mock-up (low resolution); Barishal (Guthia Mosque),
        // Mymensingh (BAU) and Dhaka (BICC) are Wikimedia Commons photos under CC BY / CC BY-SA, which need a credit line.
        $landmark = fn (array $s) => '/media/photos/division-'.strtolower($s['name']).'.jpg';
        $row = fn (string $type, string $name, string $location, string $date, string $partner, string $href, string $image) => compact('type', 'name', 'location', 'date', 'partner', 'href', 'image');
        $journey['directory'] = [
            'sample' => false,
            'rows' => [
                ...array_map(fn (array $s) => $row('division', "{$s['name']} Divisional Summit", $s['venue'], $s['date'], 'BACCO '.$s['name'].' Chapter', '/divisional-summits', $landmark($s)), $summits),
                $row('district', 'Jashore Roadshow', 'Jashore IT Park', '18 Oct 2026', 'Sheikh Hasina Software Technology Park', '/district-roadshows', '/media/photos/gallery-13.jpg'),
                $row('district', 'Cumilla Roadshow', 'Cumilla Town Hall', '22 Oct 2026', 'Cumilla Freelancers Association', '/district-roadshows', '/media/photos/gallery-01.jpg'),
                $row('district', 'Bogura Roadshow', 'Bogura Zilla School Auditorium', '29 Oct 2026', 'BACCO Rajshahi Chapter', '/district-roadshows', '/media/photos/gallery-02.jpg'),
                $row('district', "Cox's Bazar Roadshow", "Cox's Bazar Cultural Centre", '1 Nov 2026', "Cox's Bazar Chamber of Commerce", '/district-roadshows', '/media/photos/gallery-03.jpg'),
                $row('district', 'Tangail Roadshow', 'Mawlana Bhashani Science and Technology University', '5 Nov 2026', 'Tangail District Administration', '/district-roadshows', '/media/photos/gallery-12.jpg'),
                $row('campus', 'AI for Freelancers Workshop', 'CUET, Chattogram', '2 Oct 2026', 'CUET Computer Club', '/campus-programs', '/media/photos/gallery-14.jpg'),
                $row('campus', 'First Client Bootcamp', 'Khulna Polytechnic Institute', '15 Oct 2026', 'Khulna Polytechnic Career Cell', '/campus-programs', '/media/photos/highlight-workshops.jpg'),
                $row('campus', 'Career Talk: ITES and BPO', 'Shahjalal University of Science and Technology', '21 Oct 2026', 'SUST Career Club', '/campus-programs', '/media/photos/highlight-career.jpg'),
                $row('campus', 'Marketplace Profile Clinic', 'Rajshahi University of Engineering and Technology', '28 Oct 2026', 'RUET IT Society', '/campus-programs', '/media/photos/highlight-ai.jpg'),
                $row('campus', 'Mentor Session: Pricing Your Work', 'University of Dhaka', '3 Nov 2026', 'DU IT Society', '/campus-programs', '/media/photos/involved-register.jpg'),
            ],
        ];

        return [$journey, $summits];
    }

    /** Brief §7, §14: day-based agenda, session types, halls, speakers, add to calendar, downloadable agenda. */
    public function agenda(ContentQuery $content): array
    {
        $agenda = $this->home->agenda();

        return [
            'headings' => [
                'programme' => self::h('', 'Summit Programme', 'Programme'),
                'plan' => self::h('', 'Plan your day', 'your day'),
                'tracks' => self::h('', 'At a Glance: Programme Tracks'),
                'cta' => self::h('', 'Register for the Grand Summit', 'Grand Summit', "Secure your spot at Freelancer Summit Bangladesh ".config('summit.edition')." and be part of the country's biggest freelancing and entrepreneurship gathering."),
            ],
            // Day tabs for the whole summit; a day without sessions yet shows "Announcing soon".
            'agenda' => [...$agenda, 'days' => self::AGENDA_DAYS, 'sessions' => $this->agendaSessions()],
            'downloads' => $content->downloads([DownloadKind::Agenda, DownloadKind::Brochure]),
        ];
    }

    /** One agenda session's own page: the full brief, at-a-glance facts, speaker and related sessions. */
    public function agendaSession(string $slug): ?array
    {
        $sessions = $this->agendaSessions();
        $i = array_search($slug, array_column($sessions, 'slug'), true);
        if ($i === false) {
            return null;
        }
        $session = $sessions[$i];
        // The next three sessions in running order, wrapping round to the start of the day.
        $related = array_map(fn ($n) => $sessions[($i + $n) % count($sessions)], range(1, min(3, count($sessions) - 1)));

        return [
            '_page' => [
                'title' => $session['title'],
                'description' => $session['summary'] ?? PageContent::for('/agenda/session/{slug}')['description'],
                'crumbs' => [['label' => 'Agenda', 'href' => '/agenda'], ['label' => 'Session Details']],
                // The hero names the session type (Workshop, Keynote…), its last word in gold.
                'hero' => ['title' => $session['type'], 'emphasis' => Str::afterLast($session['type'], ' ')],
            ],
            'session' => $session,
            'related' => $related,
            'days' => array_values(array_unique(array_column($sessions, 'day'))),
            'headings' => [
                'speaker' => self::h('', 'Meet the Speaker', 'Speaker'),
                'related' => self::h('', 'Related Sessions', 'Sessions'),
                'cta' => self::h('', 'Join the Grand Summit', 'Grand Summit'),
            ],
        ];
    }

    /** Agenda rows with their sample detail, page link and calendar file; replace the detail with CMS session fields. */
    public function agendaSessions(): array
    {
        // Sample detail by session index.
        $details = [
            ['track' => 'Industry & policy', 'summary' => 'Where freelancing in Bangladesh stands today and what the next year asks of it.', 'learn' => ['The state of the freelance economy', 'Where demand is growing', 'What industry and government are planning'], 'audience' => ['Freelancers planning their next year', 'Students considering a freelance career', 'Industry and policy partners']],
            ['track' => 'Marketplaces', 'summary' => 'Platform teams on profiles, ranking, payments and getting hired.', 'learn' => ['Build a profile that ranks', 'Avoid common account issues', 'Get paid faster and safer'], 'audience' => ['Freelancers new to the marketplaces', 'Freelancers whose profiles have stalled', 'Anyone setting up international payments']],
            ['track' => 'AI & skills', 'summary' => 'A hands-on walkthrough of AI tools you can use on client work the same day.', 'learn' => ['Pick the right tool for a task', 'Speed up delivery without losing quality', 'Price AI-assisted work fairly'], 'audience' => ['Freelancers who are new to AI tools', 'Professionals looking to boost productivity', 'Anyone curious about AI in freelancing']],
            ['track' => 'Business growth', 'summary' => 'Taking a solo practice to a registered company: licences, tax, team and clients.', 'learn' => ['Register and stay compliant', 'Hire your first team members', 'Win larger, longer contracts'], 'audience' => ['Established freelancers ready to grow', 'Small agency founders', 'Freelancers thinking about hiring']],
            ['track' => 'Recognition', 'summary' => 'The Freelancer Awards ceremony, honouring freelancers from all 8 divisions.', 'learn' => ['Meet the winners', 'See what the judges looked for', 'Plan your nomination for next year'], 'audience' => ['Nominees and their guests', 'Freelancers planning a nomination', 'Everyone at the summit']],
        ];
        $sessions = $this->home->agenda()['sessions'];

        return array_map(fn ($s, $i) => [
            ...$s,
            ...($details[$i] ?? []),
            'slug' => Str::slug($s['title']),
            'href' => '/agenda/session/'.Str::slug($s['title']),
            'ics' => "/agenda/sessions/{$i}.ics",
        ], $sessions, array_keys($sessions));
    }

    /** Brief §13: directory with filters, profile links, speaker categories. */
    public function speakers(): array
    {
        return [
            'headings' => [
                'directory' => self::h('Directory', 'Meet the speakers', 'speakers', 'More speakers are announced as they confirm. Select a profile for their biography and sessions.'),
                'categories' => self::h('On stage', 'Who speaks at the summit', 'summit'),
                'cta' => self::h('', 'Hear them live in Dhaka', 'live in Dhaka', 'Register for the Grand Summit and see where each speaker appears in the agenda.'),
            ],
            'speakers' => array_map(fn ($p) => [...$p, 'sample' => false, 'href' => "/speaker/{$p['slug']}"], $this->home->speakers()),
            'categories' => ['International speakers', 'Trainers', 'Successful freelancers', 'Entrepreneurs', 'Policymakers', 'Marketplace representatives'],
        ];
    }

    /** Brief §9: categories, eligibility, separate nomination workflow, statuses, shortlist and winners archive. */
    public function awards(ContentQuery $content): array
    {
        return [
            'headings' => [
                'overview' => self::h('About the awards', 'Your work deserves a stage', 'a stage'),
                'categories' => self::h('Categories', 'Award categories', 'categories', 'Provisional until the award guidelines are published.'),
                'steps' => self::h('Nomination process', 'How to Nominate', 'Nominate'),
                'statuses' => self::h('', 'Application Review Status'),
                'shortlist' => self::h('Freelancer Awards', 'Shortlisted Nominees', 'Nominees'),
                'winners' => self::h('Winners & Archive', 'Featured Winner Spotlights', 'Spotlights'),
                'faq' => self::h('Questions', 'Before you nominate', 'nominate'),
                'cta' => self::h('', 'Your work deserves recognition.', 'recognition.', 'Put yourself or a deserving freelancer in the spotlight at the Freelancer Awards.'),
            ],
            'awards' => $awards = $this->home->awards(),
            'facts' => [
                ['label' => 'Nominations', 'value' => 'Opening soon'],
                ['label' => 'Deadline', 'value' => $awards['deadline'] ?? 'Published with the guidelines'],
                ['label' => 'Ceremony', 'value' => 'Grand Summit, Dhaka'],
                ['label' => 'Results', 'value' => 'Shortlist and winners on this page'],
            ],
            'categoryNote' => 'Description, eligibility, criteria and deadline are published in the award guidelines.',
            'categoryTexts' => array_map(fn ($c) => Pages\AwardPages::categoryText($c), $awards['categories']),
            'shortlistEmpty' => ['title' => 'Revealing soon', 'body' => 'Shortlisted nominees are published here once the jury has reviewed every application.'],
            'shortlist' => Pages\AwardPages::SHORTLIST,
            'winners' => Pages\AwardPages::WINNERS,
            'winnersEmpty' => ['title' => 'First edition', 'body' => 'Winners are announced at the Grand Summit in Dhaka and kept here as a permanent archive.'],
            'steps' => [
                ['title' => 'Choose a Category', 'text' => 'Review the categories, eligibility and award criteria.'],
                ['title' => 'Prepare Your Evidence', 'text' => 'Portfolio links, achievements and supporting documents.'],
                ['title' => 'Submit Your Nomination', 'text' => 'Complete the application and get a reference number.'],
                ['title' => 'Review & Results', 'text' => 'Track your application status and follow the award announcement.'],
            ],
            'faq' => [
                ['q' => 'Is award nomination the same as summit registration?', 'a' => 'No. Nominations use their own form and review process. Register separately if you also want to attend the Grand Summit.'],
                ['q' => 'Can I nominate someone else?', 'a' => 'Yes. You can apply for yourself or nominate another freelancer; the form asks for the nominee\'s details and consent.'],
                ['q' => 'When is the deadline?', 'a' => 'The submission deadline is published with the award guidelines when nominations open.'],
                ['q' => 'How is my information used?', 'a' => 'Applications are seen only by the organising team and the jury, and are handled under the privacy policy.'],
            ],
            'downloads' => $content->downloads([DownloadKind::Guidelines]),
        ];
    }
}
