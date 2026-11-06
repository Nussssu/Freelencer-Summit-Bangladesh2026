<?php

namespace App\Content\Pages;

use App\Content\CmsData;
use App\Content\HomeContent;
use App\Enums\MediaKind;
use App\Models\MediaItem;
use App\Queries\ContentQuery;
use App\Queries\NewsQuery;
use App\Queries\ReferenceQuery;
use Carbon\Carbon;
use Illuminate\Support\Str;

/** Brief §16: success stories, news, articles, media gallery, and the legal pages. */
class MediaPages
{
    use FormOptions;

    public function __construct(private HomeContent $home, private NewsQuery $news, private ReferenceQuery $ref) {}

    public function stories(): array
    {
        // Content Management › Success stories (published, in sort order).
        $published = CmsData::stories();

        // shortcut: the landing's three sample people (StoriesIntro) until real stories are published.
        $samples = [
            ['name' => 'Nusrat Jahan', 'role' => 'UI/UX Designer', 'kind' => 'Freelancer', 'title' => 'From Dhaka to Global Clients', 'quote' => 'Freelancing gave me the freedom to build the life I want.', 'image' => '/media/photos/story-1.jpg', 'youtubeId' => null, 'sample' => true],
            ['name' => 'Rafiul Islam', 'role' => 'Founder, E-commerce Brand', 'kind' => 'Entrepreneur', 'title' => 'Turning an Idea into a Global Brand', 'quote' => 'Started small, now reaching customers worldwide.', 'image' => '/media/photos/story-2.jpg', 'youtubeId' => null, 'sample' => true],
            ['name' => 'Sadia Noor', 'role' => 'Digital Marketing Specialist', 'kind' => 'Freelancer', 'title' => 'Creating Opportunities Through Freelancing', 'quote' => 'Now I work with clients across 5 countries.', 'image' => '/media/photos/story-3.jpg', 'youtubeId' => null, 'sample' => true],
        ];

        return [
            'headings' => ['stories' => self::h('Stories from across Bangladesh', 'across Bangladesh'), 'videos' => self::h('Watch their stories', 'stories'), 'share' => self::h('Share your story', 'your story')],
            'stories' => $published ?? $samples,
            'filters' => self::storyFilters($published ?? $samples),
            'videos' => [
                ['title' => 'Freelance video editor', 'poster' => '/media/photos/story-video-editor.jpg', 'youtubeId' => null],
                ['title' => 'Campus program stories', 'poster' => '/media/photos/highlight-workshops.jpg', 'youtubeId' => null],
                ['title' => 'From freelancer to founder', 'poster' => '/media/photos/highlight-stories.jpg', 'youtubeId' => null],
            ],
            'videoSoon' => 'Video coming soon',
            'share' => 'Started from a campus program or a district roadshow? Built a team from freelancing? Tell the team and your story could be featured here and on stage in Dhaka.',
        ];
    }

    /** The wall's filter tabs: one per story type in use (Freelancers, Entrepreneurs, Students…). */
    private static function storyFilters(array $stories): array
    {
        $labels = ['Freelancer' => 'Freelancers', 'Entrepreneur' => 'Entrepreneurs', 'Student' => 'Students'];
        $kinds = array_values(array_unique(array_filter(array_column($stories, 'kind'))));

        return array_map(fn ($k) => ['value' => $k, 'label' => $labels[$k] ?? $k.'s'], $kinds ?: ['Freelancer', 'Entrepreneur']);
    }

    /** One grid of every published post, newest first; there is no category filter, so old ?category= links show it all. */
    public function news(): array
    {
        return [
            'posts' => fn () => $this->news->list(null, 12),
            'empty' => ['title' => 'News coming soon', 'body' => 'Official announcements, roadshow updates and speaker announcements are published here first.'],
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
        // Photos stored against a programme, so each one knows its programme type, division, district and campus.
        $programme = ['divisional-summit' => 'Divisional', 'district-roadshow' => 'District', 'campus-program' => 'Campus'];
        $tiles = MediaItem::with(['program.division', 'program.district', 'program.campus'])->where('kind', MediaKind::Photo)
            ->whereHas('program')->orderBy('journey_program_id')->orderBy('sort')->get()
            ->map(fn ($x) => [
                'id' => $x->id,
                'programme' => $programme[$x->program->type->value],
                'programmeLabel' => $x->program->title,
                'kind' => 'photo',
                'src' => $x->src,
                'caption' => $x->caption,
                'division' => $x->program->division?->slug,
                'district' => $x->program->district?->slug,
                'campus' => $x->program->campus?->slug,
            ])->all();

        return [
            'headings' => ['photos' => self::h('Photo gallery', 'gallery'), 'videos' => self::h('Video gallery', 'gallery'), 'press' => self::h('Press and media kit', 'media kit')],
            'tiles' => $tiles,
            'divisionOptions' => $this->ref->divisions(),
            'districtGroups' => $this->ref->districts(),
            'campusOptions' => $this->ref->campuses(),
            'programmes' => [['value' => 'Divisional', 'label' => 'Divisional summits'], ['value' => 'District', 'label' => 'District roadshows'], ['value' => 'Campus', 'label' => 'Campus programs']],
            'videos' => $m['videos'],
            'sample' => true,
            'note' => 'Photos from earlier events until the campaign gallery is published.',
            'downloads' => app(ContentQuery::class)->downloads([\App\Enums\DownloadKind::MediaKit, \App\Enums\DownloadKind::Brochure]),
        ];
    }

    /** Standard privacy policy, plain text. Every heading and paragraph is editable in Pages › Privacy Policy. */
    public function privacy(): array
    {
        return [
            'updated' => '6 November 2026',
            'sections' => [
                ['h' => 'Introduction', 'p' => [
                    'Freelancer Summit Bangladesh ("we", "us" or "our") is organised by the Bangladesh Association of Contact Center & Outsourcing (BACCO) with the Department of ICT, Government of Bangladesh. We respect your privacy and are committed to protecting the personal information you share with us.',
                    'This Privacy Policy explains what information we collect when you visit this website or use its forms, how we use it, who we share it with, and the choices and rights you have. By using this website, you agree to the practices described in this policy.',
                ]],
                ['h' => 'Information we collect', 'p' => [
                    'Information you give us: when you register for the summit, nominate for an award, apply to exhibit, enquire about sponsorship, join the BACCO Freelancer Forum, register for a programme or contact us, we collect the details the form asks for. This may include your name, email address, mobile number, organisation, designation, city, professional details, portfolio and marketplace profile links, and any documents you upload.',
                    'Information collected automatically: when you browse the website we may collect technical information such as your browser type, device, pages visited, referring page and the date and time of your visit. We store a one-way hash of your IP address rather than the address itself, to help prevent spam and abuse.',
                    'Cookies: we use essential cookies to keep the website secure and working, for example to remember your language choice and protect forms. You can control cookies through your browser settings, but some parts of the website may not work properly without them.',
                ]],
                ['h' => 'How we use your information', 'p' => [
                    'We use your information to process your registrations, applications, nominations and enquiries; to send confirmations, reference numbers, e-tickets and event updates; to evaluate award nominations with the jury; to respond to your messages; to plan and improve the summit and this website; and to protect the website and its users against fraud and misuse.',
                    'We will only send you promotional messages where you have agreed to receive them. You can opt out at any time using the link in the message or by contacting us.',
                ]],
                ['h' => 'Legal basis and consent', 'p' => [
                    'We process your information on the basis of your consent, to fulfil the request you make through a form, and for our legitimate interest in running and securing the event. Every form asks for your consent, and we record the version of the consent wording you agreed to and when you agreed to it.',
                ]],
                ['h' => 'Sharing your information', 'p' => [
                    'We do not sell or rent your personal information. We share it only with the organising team; with award jury members, for award applications; with trusted service providers who help us run the website, send emails and deliver the event, under obligations to keep it confidential; and with authorities where we are required to do so by law.',
                    'Where you choose to be listed publicly, for example as a speaker, exhibitor, nominee or award winner, the details you approve may be shown on this website.',
                ]],
                ['h' => 'Data security', 'p' => [
                    'We use appropriate technical and organisational measures to protect your information against unauthorised access, loss or misuse. Contact details such as mobile numbers are stored encrypted, and uploaded documents are kept in private storage that is never published on the website.',
                    'No method of transmission over the internet is completely secure, so we cannot guarantee absolute security, but we work to protect your information and review our safeguards regularly.',
                ]],
                ['h' => 'Data retention', 'p' => [
                    'We keep your information only for as long as it is needed for the purpose it was collected for, including running the current and related future editions of the summit, and to meet legal, accounting or reporting requirements. When it is no longer needed, we delete or anonymise it.',
                ]],
                ['h' => 'Your rights', 'p' => [
                    'You may ask to access the personal information we hold about you, to correct information that is inaccurate, to delete your information, or to withdraw your consent at any time. Withdrawing consent does not affect processing that took place before you withdrew it. To make a request, contact us using the details below.',
                ]],
                ['h' => 'Third-party links', 'p' => [
                    'This website may link to other websites, such as partner, sponsor, marketplace or social media pages. We are not responsible for the privacy practices or content of those websites, and we encourage you to read their privacy policies.',
                ]],
                ['h' => 'Children’s privacy', 'p' => [
                    'This website is intended for users aged 16 and over. We do not knowingly collect personal information from children under 16 without the consent of a parent or guardian. If you believe a child has given us personal information, please contact us and we will delete it.',
                ]],
                ['h' => 'Changes to this policy', 'p' => [
                    'We may update this Privacy Policy from time to time. The updated version will be posted on this page with a new "last updated" date. We encourage you to review this page periodically.',
                ]],
                ['h' => 'Contact us', 'p' => [
                    'If you have any questions about this Privacy Policy or how we handle your information, please contact the Freelancer Summit Bangladesh team through the contact page of this website.',
                ]],
            ],
        ];
    }

    /** Standard terms of use, plain text. Every heading and paragraph is editable in Pages › Terms. */
    public function terms(): array
    {
        return [
            'updated' => '6 November 2026',
            'sections' => [
                ['h' => 'Acceptance of terms', 'p' => [
                    'These Terms of Use govern your access to and use of the Freelancer Summit Bangladesh website, organised by the Bangladesh Association of Contact Center & Outsourcing (BACCO) with the Department of ICT, Government of Bangladesh. By accessing or using this website, you agree to be bound by these terms. If you do not agree, please do not use the website.',
                ]],
                ['h' => 'Use of the website', 'p' => [
                    'You may use this website for lawful purposes only. You agree not to use it in a way that breaks any applicable law, infringes the rights of others, or restricts anyone else’s use of the website.',
                    'You must not attempt to gain unauthorised access to the website, its servers or databases; introduce viruses or other harmful code; submit spam or automated submissions; or copy, scrape or reproduce the website’s content for commercial purposes without our written permission.',
                ]],
                ['h' => 'Registrations, applications and nominations', 'p' => [
                    'When you submit a form, you confirm that the information you provide is true, accurate and complete, and that you have the right to share it. Submitting a form does not guarantee a place at the event, an award, a booth, a forum membership or a sponsorship. Each one is confirmed by the organising team by email.',
                    'We may refuse, cancel or withdraw any registration, application or nomination that contains false information, breaks these terms or the event rules, or for operational reasons.',
                ]],
                ['h' => 'Event information and changes', 'p' => [
                    'We aim to keep event information accurate and up to date, but dates, venues, speakers, sessions and programmes may change. The organisers may change, postpone or cancel any part of the event. The latest information is always published on this website.',
                ]],
                ['h' => 'Event attendance and conduct', 'p' => [
                    'Attendees, speakers, exhibitors and sponsors are expected to behave respectfully and follow the instructions of the organisers and venue staff. The organisers may refuse entry to, or remove, anyone whose behaviour is unsafe, disruptive or unlawful.',
                    'Photographs and video may be taken at summit events and used by the organisers in event reporting, on this website and on social media. By attending, you agree to appear in such material.',
                ]],
                ['h' => 'Intellectual property', 'p' => [
                    'All content on this website, including text, graphics, logos, photographs, videos and design, belongs to the organisers or their licensors and is protected by intellectual property laws. Partner and sponsor names and logos belong to their respective owners. You may view and share pages for personal, non-commercial use, but you may not reproduce, modify or distribute content without prior written permission.',
                ]],
                ['h' => 'Content you submit', 'p' => [
                    'By submitting content such as profiles, portfolio links, biographies, photographs or documents, you grant the organisers a non-exclusive licence to use that content to process your submission and, where you have agreed to it, to display it on this website and in event materials. You remain the owner of your content.',
                ]],
                ['h' => 'Third-party links', 'p' => [
                    'This website may contain links to websites operated by third parties. These links are provided for convenience only. We do not control and are not responsible for the content, policies or practices of any third-party website.',
                ]],
                ['h' => 'Disclaimer', 'p' => [
                    'This website and its content are provided on an "as is" and "as available" basis. While we work to keep the website accurate and available, we make no warranties that it will be error-free, uninterrupted or free of harmful components.',
                ]],
                ['h' => 'Limitation of liability', 'p' => [
                    'To the fullest extent permitted by law, the organisers and their partners are not liable for any direct, indirect or consequential loss or damage arising from your use of, or inability to use, this website or from your reliance on any information on it.',
                ]],
                ['h' => 'Privacy', 'p' => [
                    'Your use of this website is also governed by our Privacy Policy, which explains how we collect, use and protect your personal information.',
                ]],
                ['h' => 'Changes to these terms', 'p' => [
                    'We may update these Terms of Use at any time. The updated version will be posted on this page with a new "last updated" date. Your continued use of the website after changes are published means you accept the updated terms.',
                ]],
                ['h' => 'Governing law', 'p' => [
                    'These terms are governed by the laws of the People’s Republic of Bangladesh. Any dispute relating to these terms or this website is subject to the jurisdiction of the courts of Bangladesh.',
                ]],
                ['h' => 'Contact us', 'p' => [
                    'If you have any questions about these Terms of Use, please contact the Freelancer Summit Bangladesh team through the contact page of this website.',
                ]],
            ],
        ];
    }
}
