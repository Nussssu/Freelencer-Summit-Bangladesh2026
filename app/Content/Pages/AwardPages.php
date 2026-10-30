<?php

namespace App\Content\Pages;

use App\Content\HomeContent;
use App\Enums\DownloadKind;
use App\Http\Controllers\SubmissionController;
use App\Queries\ContentQuery;
use Illuminate\Support\Str;

/** Brief §9: award categories and the separate nomination form. */
class AwardPages
{
    use FormOptions;

    // Who each provisional category is for (wording from the brief's audiences; criteria come with the guidelines).
    private const ABOUT = [
        'Rising Freelancer' => ['icon' => 'rocket', 'tone' => 'tone-sky', 'text' => 'For freelancers early in their career who are already winning clients and building a reputation.'],
        'Woman Freelancer' => ['icon' => 'star', 'tone' => 'tone-rose', 'text' => 'Recognising women freelancers whose work and growth inspire others across Bangladesh.'],
        'AI & Automation' => ['icon' => 'ai', 'tone' => 'tone-violet', 'text' => 'For freelancers delivering AI, automation and data services to clients at home and abroad.'],
        'Creative & Design' => ['icon' => 'pen', 'tone' => 'tone-sun', 'text' => 'For design, video, animation and creative work that stands out on global marketplaces.'],
        'Freelancer to Entrepreneur' => ['icon' => 'building', 'tone' => 'tone-mint', 'text' => 'For freelancers who have grown their work into a team, agency or registered company.'],
        'Campus Freelancer' => ['icon' => 'cap', 'tone' => 'tone-teal', 'text' => 'For students at universities and polytechnics who freelance alongside their studies.'],
    ];

    public function __construct(private HomeContent $home) {}

    public function categories(ContentQuery $content): array
    {
        $awards = $this->home->awards();

        return [
            'headings' => [
                'categories' => self::h('Award categories', 'categories'),
                'criteria' => self::h('What the jury looks at', 'jury'),
                'timeline' => self::h('From nomination to the stage', 'to the stage'),
            ],
            'provisional' => $awards['categoriesProvisional'],
            'categories' => array_map(fn ($c) => ['slug' => Str::slug($c), 'name' => $c, ...(self::ABOUT[$c] ?? ['icon' => 'trophy', 'tone' => 'tone-teal', 'text' => null]), 'href' => '/awards/nominate?category='.Str::slug($c)], $awards['categories']),
            'eligibility' => $awards['eligibility'],
            'deadline' => $awards['deadline'] ?? 'Published with the award guidelines',
            'criteria' => [
                ['icon' => 'chart', 'title' => 'Quality and results', 'text' => 'The standard of work and the results delivered for clients.'],
                ['icon' => 'globe', 'title' => 'Reach', 'text' => 'Clients served, markets reached and repeat business.'],
                ['icon' => 'heart', 'title' => 'Contribution', 'text' => 'Mentoring, community work and helping others start.'],
                ['icon' => 'shield', 'title' => 'Professional conduct', 'text' => 'Reviews, reliability and how the work is run.'],
            ],
            'criteriaNote' => 'Final criteria and weightings for each category are published in the award guidelines.',
            'statuses' => ['Submitted', 'Under Review', 'Shortlisted', 'Winner'],
            'downloads' => $content->downloads([DownloadKind::Guidelines]),
        ];
    }

    public function nominate(): array
    {
        $awards = $this->home->awards();

        return [
            'categories' => array_map(fn ($c) => ['value' => Str::slug($c), 'label' => $c, 'description' => self::ABOUT[$c]['text'] ?? null], $awards['categories']),
            'provisional' => $awards['categoriesProvisional'],
            'districts' => $this->districts(),
            'platforms' => self::options(SubmissionController::PLATFORMS),
            'consent' => $this->consent(),
            'declaration' => 'I confirm the information in this nomination is accurate, and that the nominee agrees to be nominated and to be contacted by the organising team.',
            'steps' => ['Category', 'Nominee', 'Evidence', 'Review'],
            'aside' => [
                'points' => ['Separate from summit registration', 'Reference number on submission', 'Confirmation email straight away', 'Status: Submitted, Under Review, Shortlisted, Winner'],
                'help' => 'Questions about a nomination? Contact the awards team.',
            ],
            'eligibility' => $awards['eligibility'],
        ];
    }
}
