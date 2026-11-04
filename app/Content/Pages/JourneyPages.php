<?php

namespace App\Content\Pages;

use App\Content\HomeContent;
use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Models\Division;
use App\Queries\ProgramQuery;
use App\Queries\ReferenceQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Brief §4, §7, §15: the Grand Summit, the three programme directories and each programme's own page. */
class JourneyPages
{
    use FormOptions;

    private const TYPES = [
        'divisional' => [ProgramType::DivisionalSummit, '/divisional-summits', 'Divisional Summits'],
        'district' => [ProgramType::DistrictRoadshow, '/district-roadshows', 'District Roadshows'],
        'campus' => [ProgramType::CampusProgram, '/campus-programs', 'Campus Programs'],
    ];

    public function __construct(private HomeContent $home, private ProgramQuery $programs, private ReferenceQuery $ref) {}

    public function divisional(Request $request): array
    {
        return $this->list('divisional', $request);
    }

    /** District Roadshows page: short intro, searchable directory (district and status filters), completed-event photos. */
    public function district(Request $request): array
    {
        $type = ProgramType::DistrictRoadshow;
        $filters = [
            'q' => (string) $request->query('q', ''),
            'district' => (string) $request->query('district', ''),
            'status' => (string) $request->query('status', ''),
        ];

        return [
            'filters' => $filters,
            'programs' => fn () => $this->programs->list($type, $filters, 10),
            'districtGroups' => $this->ref->districts(),
            'statusOptions' => ProgramStatus::options(),
            'gallery' => $this->programs->completedPhotos($type),
            'about' => [
                'title' => 'Freelancing, brought to your district',
                'emphasis' => 'your district',
                'paragraphs' => [
                    'District roadshows take the Freelancer Summit to towns across Bangladesh: one day of career awareness, practical skill sessions, local success stories and marketplace guidance, close to home and free to attend.',
                    'Each roadshow is run with a local organiser and feeds into its divisional summit and the Grand Summit in Dhaka.',
                ],
            ],
            'headings' => [
                'directory' => self::h('Find a roadshow', 'roadshow'),
                'gallery' => self::h('From the road', 'road'),
                'cta' => self::h('Follow the national journey', 'national journey'),
            ],
            'empty' => ['title' => 'Roadshows announced soon', 'body' => 'Each roadshow is listed here with its date, venue and registration once it is confirmed.'],
        ];
    }

    public function campus(Request $request): array
    {
        return $this->list('campus', $request);
    }

    public function divisionalDetail(string $slug): ?array
    {
        return $this->detail('divisional', $slug);
    }

    public function districtDetail(string $slug): ?array
    {
        return $this->detail('district', $slug);
    }

    public function campusDetail(string $slug): ?array
    {
        return $this->detail('campus', $slug);
    }

    /** One directory page per programme type, filters synced to the URL (useDirectory). */
    private function list(string $key, Request $request): array
    {
        [$type] = self::TYPES[$key];
        $filters = [
            'q' => (string) $request->query('q', ''),
            'division' => (string) $request->query('division', ''),
            'status' => (string) $request->query('status', ''),
        ];
        $journey = $this->home->journey();
        $counter = ['divisional' => 0, 'district' => 1, 'campus' => 2][$key];

        // Every division gets a card even before its summit is scheduled, so the 8-division promise is visible.
        $scheduled = collect($this->programs->list(ProgramType::DivisionalSummit, [], 50)['data'])->keyBy('division');
        $divisions = $key === 'divisional' ? Division::orderBy('sort')->get()->map(fn ($d) => [
            'name' => $d->name,
            'slug' => $d->slug,
            'program' => $scheduled[$d->name] ?? null,
        ])->all() : null;

        return [
            'type' => $type->value,
            'typeLabel' => $type->label(),
            'filters' => $filters,
            'programs' => fn () => $this->programs->list($type, $filters, 12),
            'divisionOptions' => $this->ref->divisions(),
            'statusOptions' => ProgramStatus::options(),
            'divisions' => $divisions,
            'counter' => $journey['counters'][$counter],
            'steps' => $journey['steps'],
            'activities' => match ($key) {
                'divisional' => [
                    ['icon' => 'mic', 'title' => 'Keynotes and panels', 'text' => 'Government, industry and international experts on freelancing in the division.'],
                    ['icon' => 'star', 'title' => 'Success stories', 'text' => 'Freelancers and founders from the division share how they built their careers.'],
                    ['icon' => 'ai', 'title' => 'AI showcase', 'text' => 'Live demonstrations of AI tools freelancers can use for client work.'],
                    ['icon' => 'globe', 'title' => 'Marketplace sessions', 'text' => 'Platform representatives on profiles, ranking, payments and getting hired.'],
                ],
                'district' => [
                    ['icon' => 'megaphone', 'title' => 'Freelancer roadshow', 'text' => 'The campaign comes to the district with talks, demos and local success stories.'],
                    ['icon' => 'briefcase', 'title' => 'Career awareness', 'text' => 'What freelancing, ITES and BPO careers look like and how to start.'],
                    ['icon' => 'cap', 'title' => 'Skill development', 'text' => 'Short practical sessions on the skills clients pay for now.'],
                    ['icon' => 'rocket', 'title' => 'New freelancer campaign', 'text' => 'Help for first-time freelancers to set up, find a first client and get paid.'],
                ],
                default => [
                    ['icon' => 'mic', 'title' => 'Seminars', 'text' => 'Industry speakers on freelancing, AI and the digital economy.'],
                    ['icon' => 'grid', 'title' => 'Workshops', 'text' => 'Hands-on sessions in small groups, from portfolios to AI tools.'],
                    ['icon' => 'briefcase', 'title' => 'Career talks', 'text' => 'Routes into ITES, BPO and AI-based digital services.'],
                    ['icon' => 'people', 'title' => 'Mentor sessions', 'text' => 'Time with experienced freelancers who started where you are.'],
                ],
            },
            'includes' => ['Date and location', 'Organiser or partner', 'Event description', 'Speakers and mentors', 'Registration link', 'Photo and video gallery'],
            'headings' => [
                'activities' => self::h('What happens at each programme', 'each programme'),
                'divisions' => self::h('Eight divisions, eight summits', 'eight summits'),
                'directory' => self::h($key === 'campus' ? 'Find a campus program' : ($key === 'district' ? 'Find a roadshow' : 'Scheduled summits'), $key === 'campus' ? 'campus program' : ($key === 'district' ? 'roadshow' : 'summits')),
                'ambassador' => self::h('Become a campus ambassador', 'campus ambassador'),
            ],
            'empty' => ['title' => 'Dates announced soon', 'body' => 'Programmes are published here with date, venue and registration as they are confirmed.'],
            'ambassador' => $key === 'campus' ? [
                'text' => 'Students and faculty can help bring a campus program to their institution. Leave your details and the campus team will contact you when programmes are scheduled.',
                'points' => ['Help host seminars and workshops at your institution', 'Get early access to summit announcements', 'Connect your campus with mentors and industry'],
                'consent' => $this->consent(),
            ] : null,
        ];
    }

    private function detail(string $key, string $slug): ?array
    {
        [$type, $path, $label] = self::TYPES[$key];
        $p = $this->programs->find($type, $slug);
        if (! $p) {
            return null;
        }

        $date = $p['startsAt'] ? \Carbon\Carbon::parse($p['startsAt'])->timezone('Asia/Dhaka')->format('j F Y, g:i a') : null;

        return [
            '_page' => [
                'title' => $p['seoTitle'] ?? $p['title'],
                'description' => $p['seoDescription'] ?? Str::limit(strip_tags((string) $p['description']), 155) ?: "{$p['title']}: {$type->label()} of Freelancer Summit Bangladesh.",
                'crumbs' => [['label' => $label, 'href' => $path], ['label' => $p['title']]],
                'hero' => ['title' => $p['title'], 'emphasis' => $p['district'] ?? $p['division'] ?? '', 'intro' => $p['location'] ? "{$type->label()} in {$p['location']}." : "{$type->label()} of Freelancer Summit Bangladesh.", 'image' => $p['image'] ?? '/media/photos/gallery-08.jpg', 'actions' => []],
            ],
            'program' => $p,
            'typeLabel' => $type->label(),
            'facts' => array_values(array_filter([
                ['icon' => 'calendar', 'label' => 'Date', 'value' => $date ?? 'To be announced'],
                ['icon' => 'pin', 'label' => 'Venue', 'value' => $p['location'] ?? 'To be announced'],
                $p['district'] ? ['icon' => 'map', 'label' => 'District', 'value' => $p['district']] : null,
                ['icon' => 'handshake', 'label' => 'Organiser or partner', 'value' => $p['partner'] ?? 'To be announced'],
                ['icon' => 'map', 'label' => 'Division', 'value' => $p['division'] ?? 'To be announced'],
            ])),
            'register' => [
                'open' => $p['registrationOpen'],
                'action' => "/programs/{$type->value}/{$slug}/register",
                'closed' => $p['status'] === ProgramStatus::Completed->value ? 'This programme has taken place. Photos and videos are below.' : 'Registration opens closer to the date. Register for the Grand Summit meanwhile.',
                'consent' => $this->consent(),
            ],
            'related' => array_values(array_filter($this->programs->list($type, [], 4)['data'], fn ($r) => $r['slug'] !== $slug)),
            'backHref' => $path,
            'backLabel' => "All {$label}",
        ];
    }

    /** Brief §7: date, venue, map, countdown, highlights, agenda preview, speakers, exhibition, downloads, archive. */
    public function grandSummit(): array
    {
        $agenda = $this->home->agenda();
        // The six highlights the Grand Summit page features, in this order (picked from the landing's highlight list)
        $pick = ['Keynote sessions', 'AI showcase', 'Workshops', 'Freelancer Awards', 'Marketplace sessions', 'Networking'];
        $highlights = collect($this->home->highlights())->keyBy('title');

        return [
            'headings' => [
                'glance' => self::h('At a glance', 'glance'),
                'highlights' => self::h('Summit highlights', 'highlights'),
                'agenda' => self::h('Agenda preview', 'preview'),
                'speakers' => self::h('Featured speakers', 'speakers'),
                'expo' => self::h('Exhibition & networking', '& networking'),
                'venue' => self::h('Venue & location', 'location'),
                'cta' => self::h('Be part of the Grand Summit', 'Grand Summit'),
            ],
            'highlights' => array_values(array_filter(array_map(fn ($t) => $highlights->get($t), $pick))),
            'sessions' => array_slice($agenda['sessions'], 0, 5),
            'sessionsSample' => $agenda['sample'],
            'speakers' => array_slice($this->home->speakers(), 0, 4),
            'expo' => ['photo' => '/media/photos/exhibition-hero.jpg'],
        ];
    }
}
