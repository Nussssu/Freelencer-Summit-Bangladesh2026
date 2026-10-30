<?php

namespace App\Content;

use App\Enums\DownloadKind;
use App\Queries\ContentQuery;

/**
 * Hero copy and body sections for the built main-navigation pages. Title/description for SEO stay in PageContent.
 * Wording follows the client brief (section numbers in comments). Shared data (speakers, agenda, journey, awards)
 * comes from HomeContent so the landing and inner pages never disagree. No invented people, numbers or dates.
 */
class InnerPageContent
{
    public function __construct(private HomeContent $home) {}

    /** Section heading: eyebrow label, title (emphasis is a substring of it), optional one-line intro. */
    private static function h(string $eyebrow, string $title, ?string $emphasis = null, ?string $intro = null): array
    {
        return compact('eyebrow', 'title', 'emphasis', 'intro');
    }

    /** path => Vue page + hero (emphasis must be a substring of title; one gold action per page) */
    public const PAGES = [
        '/about' => [
            'component' => 'About',
            'eyebrow' => 'About the summit',
            'title' => 'A national platform for freelancers',
            'emphasis' => 'freelancers',
            'intro' => 'Freelancer Summit Bangladesh is a national campaign, not a single-day event. Organised by BACCO with DoICT, it builds momentum across the country and culminates in the Grand Summit in Dhaka.',
            'actions' => [
                ['label' => 'Register for Summit', 'href' => '/register', 'tone' => 'gold'],
                ['label' => 'Explore the national journey', 'href' => '/national-journey', 'tone' => 'outline'],
            ],
        ],
        '/national-journey' => [
            'component' => 'NationalJourney',
            'eyebrow' => 'The national journey',
            'title' => 'Eight divisions, one road to Dhaka',
            'emphasis' => 'Dhaka',
            'intro' => 'Campus programs, district roadshows and divisional summits across all 8 divisions build the campaign that leads to the Grand Summit in Dhaka.',
            'actions' => [
                ['label' => 'Register for Summit', 'href' => '/register', 'tone' => 'gold'],
                ['label' => 'Find a district summit', 'href' => '/district-roadshows', 'tone' => 'outline'],
            ],
        ],
        '/agenda' => [
            'component' => 'Agenda',
            'eyebrow' => 'Grand Summit agenda',
            'title' => 'Sessions for every career stage',
            'emphasis' => 'career stage',
            'intro' => 'Keynotes, panels, workshops, career talks, marketplace sessions and the Freelancer Awards. The day-by-day agenda is published as sessions are confirmed.',
            'actions' => [
                ['label' => 'Register for Summit', 'href' => '/register', 'tone' => 'gold'],
                ['label' => 'Meet the speakers', 'href' => '/speakers', 'tone' => 'outline'],
            ],
        ],
        '/speakers' => [
            'component' => 'Speakers',
            'eyebrow' => 'Speakers & experts',
            'title' => 'Voices of the digital economy',
            'emphasis' => 'digital economy',
            'intro' => 'International speakers, trainers, successful freelancers, entrepreneurs, policymakers and marketplace representatives. Speakers are announced as they confirm.',
            'actions' => [
                ['label' => 'Register for Summit', 'href' => '/register', 'tone' => 'gold'],
                ['label' => 'Explore the agenda', 'href' => '/agenda', 'tone' => 'outline'],
            ],
        ],
        '/awards' => [
            'component' => 'Awards',
            'eyebrow' => 'Freelancer Awards',
            'title' => 'Celebrating freelance excellence',
            'emphasis' => 'excellence',
            'intro' => 'National awards for freelancers and freelancer-led businesses. Nomination is separate from summit registration: choose a category, check eligibility and submit your application.',
            'actions' => [
                ['label' => 'Nominate for Awards', 'href' => '/awards/nominate', 'tone' => 'gold'],
                ['label' => 'View award categories', 'href' => '/awards/categories', 'tone' => 'outline'],
            ],
        ],
    ];

    /** Brief §1–4: positioning, strategic objectives, implementation journey, audiences, organisers. */
    public function about(): array
    {
        $journey = $this->home->journey();

        return [
            'headings' => [
                'why' => self::h('Why it exists', 'More than an event', 'event'),
                'objectives' => self::h('Strategic objectives', 'What the summit sets out to do', 'sets out to do'),
                'phases' => self::h('How it works', 'One campaign, four strands', 'four strands', 'Activity builds across the country all year and comes together at the Grand Summit in Dhaka.'),
                'audiences' => self::h('Who it is for', 'Something for every step of the journey', 'every step'),
                'highlights' => self::h('At the Grand Summit', 'What happens in Dhaka', 'Dhaka'),
                'organisers' => self::h('Organisers', 'Jointly organised by', 'organised by'),
                'cta' => self::h('', 'Be part of the national journey', 'national journey', 'Register to attend, nominate for the awards, exhibit, sponsor or join the BACCO Freelancer Forum.'),
            ],
            // The landing's about block lost its statement copy in the home redesign; the About page keeps it here.
            'about' => $this->home->about() + [
                'statement' => 'Freelancing should be one of the most respected careers in Bangladesh. This is where that starts.',
                'highlight' => 'one of the most respected careers in Bangladesh',
                'paragraphs' => [
                    'Freelancer Summit Bangladesh is a national platform for freelancers, aspiring freelancers, AI practitioners, entrepreneurs, marketplaces and the companies that hire them.',
                    'It connects university and polytechnic students with the global digital economy, through programmes on their own campuses and in their own districts.',
                    'It follows the whole journey: the first gig, a skilled career, and the step from freelancer to entrepreneur.',
                ],
                'stamp' => 'Not a one-day event',
            ],
            'objectives' => [
                'Establish freelancing as one of the most respected and highly desired professions in Bangladesh.',
                'Increase awareness among youth about freelancing, AI and digital service sectors.',
                'Create a continuous national platform for developing internationally competitive skilled human resources.',
                'Connect university and polytechnic students with the global digital economy.',
                'Increase Bangladesh\'s connectivity with international marketplaces, technology institutions and global experts.',
                'Build a national ecosystem for new freelancers, skilled freelancers and freelancer-to-entrepreneur journeys.',
                'Increase youth interest in ITES, BPO and AI-based digital services.',
                'Facilitate Freelancer ID and BACCO Freelancer Forum membership registration.',
                'Bring international speakers, trainers, successful entrepreneurs and global marketplace representatives into the ecosystem.',
            ],
            // Brief §4 implementation table; coverage counts come from config like the landing.
            'phases' => [
                ['title' => 'District Programs', 'coverage' => $journey['steps'][1]['title'], 'href' => '/district-roadshows',
                    'activities' => ['Freelancer roadshows', 'Career awareness', 'Skill development', 'New freelancer campaign', 'Success stories, AI showcase and marketplace sessions']],
                ['title' => 'Campus Programs', 'coverage' => $journey['steps'][0]['title'], 'href' => '/campus-programs',
                    'activities' => ['Seminars', 'Workshops', 'Career talks', 'AI awareness', 'Mentor sessions']],
                ['title' => 'Grand Dhaka Summit', 'coverage' => 'Dhaka', 'href' => '/grand-summit',
                    'activities' => ['Main summit and keynotes', 'Freelancer Awards', 'Seminars and workshops', 'Exhibition', 'Networking']],
                ['title' => 'Digital Campaign', 'coverage' => 'Nationwide', 'href' => '/news',
                    'activities' => ['Media and social campaign', 'Campus ambassadors', 'Success stories', 'News and updates']],
            ],
            'audiences' => $this->home->audiences(),
            'highlights' => $this->home->highlights(),
        ];
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
                'includes' => self::h('Every programme page', 'What you will find for each activity', 'each activity'),
                'cta' => self::h('', 'Bring the summit to your campus', 'your campus', 'Register for the Grand Summit, or follow the programmes coming to your district and institution.'),
            ],
            'journey' => $journey,
            'reel' => [
                ['image' => '/media/photos/gallery-12.jpg', 'place' => 'Rajshahi Divisional Summit', 'date' => '13 Sep 2026'],
                ['image' => '/media/photos/gallery-14.jpg', 'place' => 'Campus program, CUET Chattogram', 'date' => '2 Oct 2026'],
                ['image' => '/media/photos/gallery-13.jpg', 'place' => 'District roadshow, Jashore', 'date' => '18 Oct 2026'],
            ],
            'divisionSummits' => array_map(fn (array $d, int $i) => $d + ['image' => '/media/photos/gallery-'.str_pad((string) ($i + 4), 2, '0', STR_PAD_LEFT).'.jpg'], $summits, array_keys($summits)),
            // What every activity page carries (brief §15).
            'includes' => [
                ['title' => 'Date and location', 'text' => 'Venue, room and a map pin, with travel notes for out-of-town participants.'],
                ['title' => 'Organiser or partner', 'text' => 'The host institution, BACCO chapter and local partners behind each programme.'],
                ['title' => 'Event description', 'text' => 'Who it is for, the session plan and what you will leave with.'],
                ['title' => 'Speakers and mentors', 'text' => 'Freelancers, trainers and marketplace leads on stage and in breakouts.'],
                ['title' => 'Registration link', 'text' => 'Free seats for students and freelancers, confirmed by SMS and email.'],
                ['title' => 'Photo and video gallery', 'text' => 'Highlights and session recordings, published within a week.'],
            ],
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

        $row = fn (string $type, string $name, string $location, string $date, string $partner, string $href) => compact('type', 'name', 'location', 'date', 'partner', 'href');
        $journey['directory'] = [
            'sample' => false,
            'rows' => [
                ...array_map(fn (array $s) => $row('division', "{$s['name']} Divisional Summit", $s['venue'], $s['date'], 'BACCO '.$s['name'].' Chapter', '/divisional-summits'), $summits),
                $row('district', 'Jashore Roadshow', 'Jashore IT Park', '18 Oct 2026', 'Sheikh Hasina Software Technology Park', '/district-roadshows'),
                $row('district', 'Cumilla Roadshow', 'Cumilla Town Hall', '22 Oct 2026', 'Cumilla Freelancers Association', '/district-roadshows'),
                $row('district', 'Bogura Roadshow', 'Bogura Zilla School Auditorium', '29 Oct 2026', 'BACCO Rajshahi Chapter', '/district-roadshows'),
                $row('district', "Cox's Bazar Roadshow", "Cox's Bazar Cultural Centre", '1 Nov 2026', 'Cox\'s Bazar Chamber of Commerce', '/district-roadshows'),
                $row('district', 'Tangail Roadshow', 'Mawlana Bhashani Science and Technology University', '5 Nov 2026', 'Tangail District Administration', '/district-roadshows'),
                $row('campus', 'AI for Freelancers Workshop', 'CUET, Chattogram', '2 Oct 2026', 'CUET Computer Club', '/campus-programs'),
                $row('campus', 'First Client Bootcamp', 'Khulna Polytechnic Institute', '15 Oct 2026', 'Khulna Polytechnic Career Cell', '/campus-programs'),
                $row('campus', 'Career Talk: ITES and BPO', 'Shahjalal University of Science and Technology', '21 Oct 2026', 'SUST Career Club', '/campus-programs'),
                $row('campus', 'Marketplace Profile Clinic', 'Rajshahi University of Engineering and Technology', '28 Oct 2026', 'RUET IT Society', '/campus-programs'),
                $row('campus', 'Mentor Session: Pricing Your Work', 'University of Dhaka', '3 Nov 2026', 'DU IT Society', '/campus-programs'),
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
                'programme' => self::h('Programme', 'Plan your day', 'your day', 'Filter by day, session type or hall, and add any session to your calendar.'),
                'formats' => self::h('Session formats', 'Learn, practise, connect', 'connect'),
                'downloads' => self::h('Downloads', 'Take the programme with you', 'with you'),
                'cta' => self::h('', 'Save your seat in Dhaka', 'in Dhaka', 'Register for the Grand Summit and we will email your confirmation.'),
            ],
            'agenda' => [...$agenda, 'sessions' => array_map(fn ($s, $i) => [...$s, 'ics' => "/agenda/sessions/{$i}.ics"], $agenda['sessions'], array_keys($agenda['sessions']))],
            'formats' => [
                ['title' => 'Keynotes & panels', 'text' => 'Policy makers, industry leaders and global experts on where freelancing in Bangladesh goes next.'],
                ['title' => 'Workshops & seminars', 'text' => 'Hands-on skill sessions in small groups, from client work to AI tools.'],
                ['title' => 'Marketplace sessions', 'text' => 'Platform representatives on profiles, ranking, payments and getting hired.'],
                ['title' => 'Career talks & success stories', 'text' => 'Routes into ITES, BPO and AI services, told by people who took them.'],
            ],
            'downloads' => $content->downloads([DownloadKind::Agenda, DownloadKind::Brochure]),
        ];
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
                'steps' => self::h('How to apply', 'Four steps to a nomination', 'nomination'),
                'statuses' => self::h('After you apply', 'Track your application', 'application'),
                'shortlist' => self::h('Shortlist', 'Shortlisted nominees', 'nominees'),
                'winners' => self::h('Archive', 'Winners archive', 'archive'),
                'faq' => self::h('Questions', 'Before you nominate', 'nominate'),
                'cta' => self::h('', 'Know someone exceptional?', 'exceptional?', 'Nominate yourself or another freelancer. It takes a few minutes and you get a reference number straight away.'),
            ],
            'awards' => $awards = $this->home->awards(),
            'facts' => [
                ['label' => 'Nominations', 'value' => 'Opening soon'],
                ['label' => 'Deadline', 'value' => $awards['deadline'] ?? 'Published with the guidelines'],
                ['label' => 'Ceremony', 'value' => 'Grand Summit, Dhaka'],
                ['label' => 'Results', 'value' => 'Shortlist and winners on this page'],
            ],
            'categoryNote' => 'Description, eligibility, criteria and deadline are published in the award guidelines.',
            'shortlistEmpty' => ['title' => 'Revealing soon', 'body' => 'Shortlisted nominees are published here once the jury has reviewed every application.'],
            'winnersEmpty' => ['title' => 'First edition', 'body' => 'Winners are announced at the Grand Summit in Dhaka and kept here as a permanent archive.'],
            'steps' => [
                ['title' => 'Choose a category', 'text' => 'Read the category description, eligibility and criteria in the award guidelines.'],
                ['title' => 'Prepare your evidence', 'text' => 'Portfolio links, marketplace profiles, achievements and any supporting documents.'],
                ['title' => 'Submit the nomination', 'text' => 'Apply for yourself or nominate someone. You get a reference number and a confirmation email.'],
                ['title' => 'Review and results', 'text' => 'Track your application status. Shortlisted nominees and winners are announced on this page.'],
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
