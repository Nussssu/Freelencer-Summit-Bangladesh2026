<?php

namespace App\Content\Pages;

use App\Content\HomeContent;
use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Models\Division;
use App\Queries\ProgramQuery;
use App\Queries\ReferenceQuery;
use App\Support\Settings;
use Carbon\Carbon;
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

    /** Divisional Summits page, laid out like District Roadshows: the same directory with status and division filters. */
    public function divisional(Request $request): array
    {
        $type = ProgramType::DivisionalSummit;
        $filters = [
            'q' => (string) $request->query('q', ''),
            'status' => (string) $request->query('status', ''),
            'division' => (string) $request->query('division', ''),
        ];

        return [
            'filters' => $filters,
            'programs' => fn () => $this->programs->list($type, $filters, 500),
            'divisionOptions' => $this->ref->divisions(),
            'statusOptions' => ProgramStatus::options(),
            'search' => 'Search divisional summits',
            'headings' => [
                'directory' => self::h('Find a divisional summit', 'divisional summit'),
            ],
            'empty' => ['title' => 'Summits announced soon', 'body' => 'Each divisional summit is listed here with its date, venue and registration once it is confirmed.'],
        ];
    }

    /** District Roadshows page: the searchable directory (status, division and district filters), every roadshow on one page. */
    public function district(Request $request): array
    {
        $type = ProgramType::DistrictRoadshow;
        $filters = [
            'q' => (string) $request->query('q', ''),
            'status' => (string) $request->query('status', ''),
            'division' => (string) $request->query('division', ''),
            'district' => (string) $request->query('district', ''),
        ];

        return [
            'filters' => $filters,
            // Every roadshow on one page: the directory has no pager.
            'programs' => fn () => $this->programs->list($type, $filters, 500),
            'divisionOptions' => $this->ref->divisions(),
            'districtGroups' => $this->ref->districts(),
            'statusOptions' => ProgramStatus::options(),
            'headings' => [
                'directory' => self::h('Find a roadshow', 'roadshow'),
            ],
            'empty' => ['title' => 'Roadshows announced soon', 'body' => 'Each roadshow is listed here with its date, venue and registration once it is confirmed.'],
        ];
    }

    /**
     * Campus Programs page, laid out like District Roadshows: the directory (its heading's button opens the campus
     * registration popup, the ambassador lead form) with status pills and division, district and institution dropdowns.
     * Every campus programme on one page.
     */
    public function campus(Request $request): array
    {
        $type = ProgramType::CampusProgram;
        $filters = [
            'q' => (string) $request->query('q', ''),
            'status' => (string) $request->query('status', ''),
            'division' => (string) $request->query('division', ''),
            'district' => (string) $request->query('district', ''),
            'campus' => (string) $request->query('campus', ''),
        ];

        return [
            'filters' => $filters,
            'programs' => fn () => $this->programs->list($type, $filters, 500),
            'divisionOptions' => $this->ref->divisions(),
            'districtGroups' => $this->ref->districts(),
            'institutionOptions' => $this->ref->campuses(),
            'statusOptions' => ProgramStatus::options(),
            'headings' => [
                'directory' => self::h('Find a campus program', 'campus program'),
            ],
            'consent' => $this->consent(),
            'empty' => ['title' => 'Programmes announced soon', 'body' => 'Campus programmes are listed here with institution, date and registration once they are confirmed.'],
        ];
    }

    /** A divisional summit's page, laid out like a roadshow's; without its own photo the hero shows the division's landmark. */
    public function divisionalDetail(string $slug): ?array
    {
        $page = $this->programPage($this->detail('divisional', $slug));
        $division = $page ? Str::slug((string) $page['program']['division']) : '';
        if ($page && ! $page['program']['image'] && is_file(public_path("media/photos/division-{$division}.jpg"))) {
            $page['_page']['hero']['image'] = "/media/photos/division-{$division}.jpg";
        }

        return $page;
    }

    /** A roadshow's page: the hero shows a landmark of its district (public/media/photos/districts, credits in CREDITS.json). */
    public function districtDetail(string $slug): ?array
    {
        $page = $this->programPage($this->detail('district', $slug));
        $district = $page ? Str::slug((string) $page['program']['district']) : '';
        if (! $district || ! is_file(public_path("media/photos/districts/{$district}.jpg"))) {
            return $page;
        }
        $credits = collect(json_decode((string) file_get_contents(public_path('media/photos/districts/CREDITS.json')), true));
        $page['_page']['hero']['image'] = "/media/photos/districts/{$district}.jpg";
        $page['photoCredit'] = $credits->firstWhere('district', $district);

        return $page;
    }

    /** A campus programme's page, laid out like a roadshow's, with its institution among the facts. */
    public function campusDetail(string $slug): ?array
    {
        $page = $this->programPage($this->detail('campus', $slug));
        if (! $page) {
            return null;
        }
        $p = $page['program'];
        if ($p['institution']) {
            array_unshift($page['facts'], ['icon' => 'cap', 'label' => 'Institution', 'value' => $p['institution']]);
        }
        // A programme without its own photo opens on students at a summit workshop
        if (! $p['image']) {
            $page['_page']['hero']['image'] = '/media/photos/highlight-workshops.jpg';
        }

        return $page;
    }

    /** What the roadshow and campus programme pages add to a detail: organiser block, closed state, photo switch. */
    private function programPage(?array $page): ?array
    {
        if (! $page) {
            return null;
        }
        // Settings › Programmes switches the Photos and videos section on for these pages.
        $page['showMedia'] = (bool) (Settings::get('programmes', [])['roadshow_media'] ?? false);
        // The organiser sits under "About this programme" with its logo (public/media/partners/{slug}.svg), not in the facts card.
        $page['facts'] = array_values(array_filter($page['facts'], fn ($f) => $f['label'] !== 'Organiser or partner'));
        $partner = $page['program']['partner'];
        $logo = $partner ? 'media/partners/'.Str::slug($partner).'.svg' : null;
        $page['partner'] = $partner ? ['name' => $partner, 'logo' => is_file(public_path($logo)) ? "/{$logo}" : null] : null;
        // "Registration closed" pill (and no Register button) once the programme is over or its registration has been switched off.
        $p = $page['program'];
        $over = $p['status'] === ProgramStatus::Completed->value || ($p['startsAt'] && Carbon::parse($p['startsAt'])->isPast());
        $page['registrationClosed'] = $over || ($p['status'] === ProgramStatus::RegistrationOpen->value && ! $p['registrationOpen']);

        return $page;
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

        $date = $p['startsAt'] ? Carbon::parse($p['startsAt'])->timezone('Asia/Dhaka')->format('j F Y, g:i a') : null;

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
