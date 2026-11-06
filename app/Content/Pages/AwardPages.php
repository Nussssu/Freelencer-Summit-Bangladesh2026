<?php

namespace App\Content\Pages;

use App\Content\CmsData;
use App\Content\HomeContent;
use App\Models\AwardCategory;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Str;

/** Brief §9: the award nomination form (categories are listed on the awards page). */
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

    // shortcut: the client's shortlist mock-up, portraits cut from it as is; replace with the jury's shortlist.
    public const SHORTLIST = [
        ['name' => 'Arif Hasan', 'role' => 'Web Developer', 'category' => 'Freelancer of the Year', 'photo' => '/media/photos/nominee-1.jpg'],
        ['name' => 'Nusrat Jahan', 'role' => 'Graphic Designer', 'category' => 'Women in Freelancing', 'photo' => '/media/photos/nominee-2.jpg'],
        ['name' => 'Rakib Ahmed', 'role' => 'Digital Marketer', 'category' => 'Rising Talent', 'photo' => '/media/photos/nominee-3.jpg'],
        ['name' => 'Tania Rahman', 'role' => 'Business Consultant', 'category' => 'Entrepreneur of the Year', 'photo' => '/media/photos/nominee-4.jpg'],
    ];

    // shortcut: dummy nominees to fill the showcase page (and its pager) until the jury's list arrives; they reuse the
    // mock-up's four portraits (1 and 3 men, 2 and 4 women). The awards page still shows SHORTLIST only.
    private const DUMMY = [
        'Freelancer of the Year' => [
            ['Mahmudul Karim', 'Mobile App Developer', 3], ['Sadia Islam', 'UI/UX Designer', 2], ['Tanvir Hossain', 'Full-Stack Developer', 1],
            ['Farhana Akter', 'Content Strategist', 4], ['Imran Chowdhury', 'Data Analyst', 3],
        ],
        'Women in Freelancing' => [
            ['Sharmin Sultana', 'Video Editor', 4], ['Ayesha Siddiqua', 'SEO Specialist', 2], ['Lamia Haque', 'Illustrator', 4],
            ['Rumana Begum', 'Virtual Assistant', 2], ['Jannatul Ferdous', 'Copywriter', 4],
        ],
        'Rising Talent' => [
            ['Sakib Hasan', 'WordPress Developer', 1], ['Nafisa Tabassum', 'Motion Designer', 2], ['Rifat Mahmud', 'Python Developer', 3],
            ['Mehjabin Rahman', 'Social Media Manager', 4], ['Shahriar Kabir', '3D Artist', 1],
        ],
        'Entrepreneur of the Year' => [
            ['Kamrul Islam', 'Agency Founder', 3], ['Nasrin Akter', 'E-commerce Consultant', 4], ['Ashraful Alam', 'Software Studio Owner', 1],
            ['Tahmina Khatun', 'Design Agency Founder', 2], ['Mizanur Rahman', 'Outsourcing Firm CEO', 3],
        ],
    ];

    // shortcut: the client's winner spotlight mock-up, photos cut from it as is; replace with the announced winners.
    public const WINNERS = [
        ['name' => 'Fahim Rahman', 'category' => 'Freelancer of the Year', 'year' => 2025, 'photo' => '/media/photos/winner-1.jpg', 'text' => 'Honoring a freelancer with exceptional impact, expertise, and consistent excellence.'],
        ['name' => 'Sumaiya Chowdhury', 'category' => 'Women in Freelancing', 'year' => 2025, 'photo' => '/media/photos/winner-2.jpg', 'text' => 'Celebrating a woman freelancer who leads, innovates, and inspires the community.'],
        ['name' => 'Arnob Hossain', 'category' => 'Rising Talent', 'year' => 2025, 'photo' => '/media/photos/winner-3.jpg', 'text' => 'Recognising an up-and-coming freelancer with outstanding potential and early impact.'],
        ['name' => 'Tasnim Haque', 'category' => 'Entrepreneur of the Year', 'year' => 2025, 'photo' => '/media/photos/winner-2.jpg', 'text' => 'Honouring a freelancer who has grown their work into a team, agency or company.'],
    ];

    // shortcut: dummy past winners to fill the archive page (and its pager); they reuse the three spotlight photos
    // (1 and 3 men, 2 a woman). The awards page still shows WINNERS only.
    private const PAST_WINNERS = [
        2024 => [['Freelancer of the Year', 'Rashed Karim', 1], ['Women in Freelancing', 'Nabila Rahman', 2], ['Rising Talent', 'Sabbir Ahmed', 3], ['Entrepreneur of the Year', 'Mahfuz Alam', 1]],
        2023 => [['Freelancer of the Year', 'Tahsin Islam', 3], ['Women in Freelancing', 'Farzana Yasmin', 2], ['Rising Talent', 'Mehedi Hasan', 1], ['Entrepreneur of the Year', 'Shirin Akter', 2]],
        2022 => [['Freelancer of the Year', 'Rakibul Hasan', 1], ['Women in Freelancing', 'Anika Tabassum', 2], ['Rising Talent', 'Nayeem Hossain', 3], ['Entrepreneur of the Year', 'Zahid Hasan', 3]],
    ];

    public function __construct(private HomeContent $home) {}

    /** The one-line "who it is for" description of an award category (null for a category without one). */
    public static function categoryText(string $category): ?string
    {
        $saved = CmsData::awardCategories()?->firstWhere('name', $category)?->description;

        return filled($saved) ? $saved : (self::ABOUT[$category]['text'] ?? null);
    }

    /** The public shortlist: nominations published in Awards Management, else the coded showcase. */
    public static function shortlist(): array
    {
        return CmsData::shortlist() ?? self::SHORTLIST;
    }

    /** The public winners: winners published in Awards Management, else the coded spotlight. */
    public static function spotlight(): array
    {
        return CmsData::winners() ?? self::WINNERS;
    }

    /** Brief §9 winner announcement page and permanent archive: every winner, listed by year with the category. */
    public function winners(): array
    {
        if ($published = CmsData::winners()) {
            return ['winners' => $published];
        }
        $past = collect(self::PAST_WINNERS)->flatMap(fn ($rows, $year) => array_map(
            fn ($w) => ['name' => $w[1], 'category' => $w[0], 'year' => $year, 'photo' => "/media/photos/winner-{$w[2]}.jpg", 'text' => null],
            $rows,
        ))->all();

        return ['winners' => [...self::WINNERS, ...$past]];
    }

    /** Brief §9 shortlisted nominee showcase: every nominee, listed by category on the page. */
    public function nominees(): array
    {
        if ($published = CmsData::shortlist()) {
            return ['nominees' => $published];
        }
        $dummy = collect(self::DUMMY)->flatMap(fn ($people, $category) => array_map(
            fn ($p) => ['name' => $p[0], 'role' => $p[1], 'category' => $category, 'photo' => "/media/photos/nominee-{$p[2]}.jpg"],
            $people,
        ))->all();

        return ['nominees' => [...self::SHORTLIST, ...$dummy]];
    }

    public function nominate(): array
    {
        $awards = $this->home->awards();
        // Awards Management › Award categories: only the ones accepting nominations (open, deadline not passed).
        $saved = CmsData::awardCategories();
        $categories = $saved
            ? $saved->filter(fn (AwardCategory $c) => $c->acceptsNominations())->map(fn (AwardCategory $c) => ['value' => $c->slug, 'label' => $c->name, 'description' => $c->description ?: (self::ABOUT[$c->name]['text'] ?? null)])->values()->all()
            : array_map(fn ($c) => ['value' => Str::slug($c), 'label' => $c, 'description' => self::ABOUT[$c]['text'] ?? null], $awards['categories']);

        return [
            'categories' => $categories,
            'provisional' => $awards['categoriesProvisional'],
            'districts' => $this->districts(),
            'platforms' => self::options(SubmissionController::PLATFORMS),
            'consent' => $this->consent(),
            'declaration' => 'I confirm the information in this nomination is accurate, and that the nominee agrees to be nominated and to be contacted by the organising team.',
            'experience' => self::options(SubmissionController::AWARD_EXPERIENCE),
            'steps' => ['Applicant Profile', 'Award Category', 'Portfolio & Achievements', 'Supporting Documents', 'Review & Submit'],
        ];
    }
}
