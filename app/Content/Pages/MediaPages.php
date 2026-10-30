<?php

namespace App\Content\Pages;

use App\Content\HomeContent;
use App\Enums\NewsCategory;
use App\Models\SuccessStory;
use App\Queries\ContentQuery;
use App\Queries\NewsQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Brief §16: success stories, news, articles, media gallery, and the legal pages. */
class MediaPages
{
    use FormOptions;

    public function __construct(private HomeContent $home, private NewsQuery $news) {}

    public function stories(): array
    {
        $published = SuccessStory::where('published', true)->orderBy('sort')->get()->map(fn ($s) => [
            'name' => $s->name, 'role' => null, 'kind' => 'Freelancer', 'title' => $s->headline, 'quote' => Str::limit(strip_tags((string) $s->body), 140) ?: null,
            'image' => $s->photo, 'youtubeId' => $s->youtube_id, 'sample' => $s->is_sample,
        ])->all();

        // shortcut: the landing's three sample people (StoriesIntro) until real stories are published.
        $samples = [
            ['name' => 'Nusrat Jahan', 'role' => 'UI/UX Designer', 'kind' => 'Freelancer', 'title' => 'From Dhaka to Global Clients', 'quote' => 'Freelancing gave me the freedom to build the life I want.', 'image' => '/media/photos/story-1.jpg', 'youtubeId' => null, 'sample' => true],
            ['name' => 'Rafiul Islam', 'role' => 'Founder, E-commerce Brand', 'kind' => 'Entrepreneur', 'title' => 'Turning an Idea into a Global Brand', 'quote' => 'Started small, now reaching customers worldwide.', 'image' => '/media/photos/story-2.jpg', 'youtubeId' => null, 'sample' => true],
            ['name' => 'Sadia Noor', 'role' => 'Digital Marketing Specialist', 'kind' => 'Freelancer', 'title' => 'Creating Opportunities Through Freelancing', 'quote' => 'Now I work with clients across 5 countries.', 'image' => '/media/photos/story-3.jpg', 'youtubeId' => null, 'sample' => true],
        ];

        return [
            'headings' => ['stories' => self::h('Stories from across Bangladesh', 'across Bangladesh'), 'videos' => self::h('Watch their stories', 'stories'), 'share' => self::h('Share your story', 'your story')],
            'stories' => $published ?: $samples,
            'filters' => [['value' => 'Freelancer', 'label' => 'Freelancers'], ['value' => 'Entrepreneur', 'label' => 'Entrepreneurs']],
            'videos' => [
                ['title' => 'Freelance video editor', 'poster' => '/media/photos/story-video-editor.jpg', 'youtubeId' => null],
                ['title' => 'Campus program stories', 'poster' => '/media/photos/highlight-workshops.jpg', 'youtubeId' => null],
                ['title' => 'From freelancer to founder', 'poster' => '/media/photos/highlight-stories.jpg', 'youtubeId' => null],
            ],
            'videoSoon' => 'Video coming soon',
            'share' => 'Started from a campus program or a district roadshow? Built a team from freelancing? Tell the team and your story could be featured here and on stage in Dhaka.',
        ];
    }

    public function news(Request $request): array
    {
        $category = (string) $request->query('category', '');
        $category = in_array($category, NewsCategory::values(), true) ? $category : '';

        return [
            'filters' => ['category' => $category],
            'categories' => NewsCategory::options(),
            'posts' => fn () => $this->news->list($category ?: null, 9),
            'empty' => ['title' => 'News coming soon', 'body' => 'Official announcements, roadshow updates and speaker announcements are published here first.'],
            'upcoming' => array_map(fn ($n) => ['category' => $n['category'], 'title' => $n['title']], $this->home->news()),
        ];
    }

    public function article(string $slug): ?array
    {
        $a = $this->news->find($slug);
        if (! $a) {
            return null;
        }
        $date = $a['publishedAt'] ? Carbon::parse($a['publishedAt'])->timezone('Asia/Dhaka')->format('j F Y') : null;

        return [
            '_page' => [
                'title' => $a['seoTitle'] ?? $a['title'],
                'description' => $a['seoDescription'] ?? $a['excerpt'] ?? Str::limit(strip_tags((string) $a['body']), 155),
                'crumbs' => [['label' => 'News & Updates', 'href' => '/news'], ['label' => Str::limit($a['title'], 48)]],
                'hero' => ['title' => $a['title'], 'emphasis' => '', 'intro' => $a['excerpt'], 'image' => $a['image'] ?? '/media/photos/gallery-0'.(strlen($a['slug']) % 9 + 1).'.jpg', 'actions' => []],
            ],
            'article' => [...$a, 'date' => $date],
        ];
    }

    public function media(): array
    {
        $m = $this->home->media();

        return [
            'headings' => ['photos' => self::h('Photo gallery', 'gallery'), 'videos' => self::h('Video gallery', 'gallery'), 'press' => self::h('Press and media kit', 'media kit')],
            'tiles' => array_map(fn ($t) => [...$t, 'programmeLabel' => "{$t['programme']} programme"], $m['tiles']),
            'programmes' => [['value' => 'Divisional', 'label' => 'Divisional summits'], ['value' => 'District', 'label' => 'District roadshows'], ['value' => 'Campus', 'label' => 'Campus programs']],
            'videos' => $m['videos'],
            'sample' => true,
            'note' => 'Sample photos from earlier events until the campaign gallery is published.',
            'downloads' => app(ContentQuery::class)->downloads([\App\Enums\DownloadKind::MediaKit, \App\Enums\DownloadKind::Brochure]),
        ];
    }

    public function privacy(ContentQuery $content): array
    {
        return $this->legal($content, 'privacy-policy', [
            ['h' => 'What this page covers', 'p' => ['This page explains how the Freelancer Summit Bangladesh website handles the information you give in its forms: summit registration, award nominations, sponsorship and exhibition enquiries, BACCO Freelancer Forum applications, programme registrations and contact messages.']],
            ['h' => 'What we collect', 'p' => ['Only the details each form asks for, such as your name, email, mobile number, organisation and the information needed for that journey. Award applications may include portfolio links, marketplace profiles and supporting documents.']],
            ['h' => 'How it is protected', 'p' => ['Mobile numbers are stored encrypted. Uploaded documents are kept in private storage that is never published on the website. We store a one-way hash of your IP address, not the address itself, to prevent abuse.']],
            ['h' => 'Consent', 'p' => ['Every form asks for your consent, and we record the version of the consent wording you agreed to and when.']],
            ['h' => 'Who sees it', 'p' => ['The organising team, and for award applications the jury, see submissions only to handle your request.']],
            ['h' => 'Contact', 'p' => ['Questions about your information can be sent through the contact page.']],
        ]);
    }

    public function terms(ContentQuery $content): array
    {
        return $this->legal($content, 'terms', [
            ['h' => 'Using this website', 'p' => ['This website gives information about Freelancer Summit Bangladesh and lets you register, apply and send enquiries.']],
            ['h' => 'Registrations and applications', 'p' => ['Submitting a form does not guarantee a place, an award, a booth or a sponsorship. The organising team confirms each one by email.']],
            ['h' => 'Event details', 'p' => ['Dates, venues, speakers and sessions may change. The latest information is always on this website.']],
            ['h' => 'Contact', 'p' => ['Questions about these terms can be sent through the contact page.']],
        ]);
    }

    /** The organiser's approved text when it exists (pages table), otherwise a clearly marked draft summary. */
    private function legal(ContentQuery $content, string $slug, array $draft): array
    {
        $page = $content->page($slug);

        return [
            'sections' => $page['sections'] ?? $draft,
            'draft' => $page ? (bool) $page['draft'] : true,
            'draftNote' => 'Draft summary. The final text will be published once the organiser approves it.',
            'updatedAt' => $page['updatedAt'] ?? null,
        ];
    }
}
