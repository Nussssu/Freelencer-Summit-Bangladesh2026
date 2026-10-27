<?php

namespace App\Content;

/**
 * Content shared by every page (header, menu, footer). Arrays today; an admin
 * panel can swap each method for an Eloquent query with the same shape.
 */
class SiteContent
{
    /** The 16 navigation items from the client brief, in brief order. */
    public function navigation(): array
    {
        return [
            ['label' => 'Home', 'href' => '/', 'group' => 'summit'],
            ['label' => 'About Summit', 'href' => '/about', 'group' => 'summit'],
            ['label' => 'District Summits', 'href' => '/district-roadshows', 'group' => 'journey'],
            ['label' => 'Campus Programs', 'href' => '/campus-programs', 'group' => 'journey'],
            ['label' => 'Grand Summit', 'href' => '/grand-summit', 'group' => 'summit'],
            ['label' => 'Agenda', 'href' => '/agenda', 'group' => 'summit'],
            ['label' => 'Speakers & Experts', 'href' => '/speakers', 'group' => 'summit'],
            ['label' => 'Freelancer Awards', 'href' => '/awards', 'group' => 'take-part'],
            ['label' => 'Exhibition', 'href' => '/exhibition', 'group' => 'take-part'],
            ['label' => 'Sponsorship', 'href' => '/sponsorship-opportunity', 'group' => 'take-part'],
            ['label' => 'BACCO Freelancer Forum', 'href' => '/forum', 'group' => 'take-part'],
            ['label' => 'Registration', 'href' => '/register', 'group' => 'take-part'],
            ['label' => 'News & Updates', 'href' => '/news', 'group' => 'media'],
            ['label' => 'Media Gallery', 'href' => '/media', 'group' => 'media'],
            ['label' => 'Partners', 'href' => '/partners', 'group' => 'media'],
            ['label' => 'Contact', 'href' => '/contact', 'group' => 'media'],
        ];
    }

    public function navGroups(): array
    {
        return [
            ['id' => 'summit', 'label' => 'The Summit'],
            ['id' => 'journey', 'label' => 'National Journey'],
            ['id' => 'take-part', 'label' => 'Take Part'],
            ['id' => 'media', 'label' => 'Media & Contact'],
        ];
    }

    /** Inline header links (subset; National Journey is a page, not a nav item). */
    public function headerLinks(): array
    {
        return [
            ['label' => 'About Summit', 'href' => '/about'],
            ['label' => 'National Journey', 'href' => '/national-journey'],
            ['label' => 'Agenda', 'href' => '/agenda'],
            ['label' => 'Speakers & Experts', 'href' => '/speakers'],
            ['label' => 'Freelancer Awards', 'href' => '/awards'],
        ];
    }

    /** Five separate registration journeys + two secondary CTAs (brief §20). */
    public function ctas(): array
    {
        return [
            'primary' => [
                ['id' => 'register', 'label' => 'Register for Summit', 'href' => '/register', 'tone' => 'gold'],
                ['id' => 'nominate', 'label' => 'Nominate for Awards', 'href' => '/awards/nominate', 'tone' => 'outline'],
                ['id' => 'sponsor', 'label' => 'Become a Sponsor', 'href' => '/sponsorship-opportunity', 'tone' => 'red'],
                ['id' => 'exhibit', 'label' => 'Exhibit at Summit', 'href' => '/exhibition/apply', 'tone' => 'green'],
                ['id' => 'forum', 'label' => 'Join BACCO Freelancer Forum', 'href' => '/forum/register', 'tone' => 'blue'],
            ],
            'secondary' => [
                ['id' => 'agenda', 'label' => 'Explore Agenda', 'href' => '/agenda', 'tone' => 'ghost'],
                ['id' => 'speakers', 'label' => 'Meet the Speakers', 'href' => '/speakers', 'tone' => 'ghost'],
            ],
        ];
    }

    public function organisers(): array
    {
        return [
            ['short' => 'BACCO', 'name' => 'Bangladesh Association of Contact Center & Outsourcing', 'role' => 'Primary organiser', 'logo' => null],
            ['short' => 'DoICT', 'name' => 'Department of Information and Communication Technology', 'role' => 'Primary organiser', 'logo' => null],
        ];
    }

    /** One contact per journey; addresses arrive with the client's content inputs (brief §21). */
    public function contacts(): array
    {
        return [
            ['label' => 'Summit registration', 'value' => null],
            ['label' => 'Awards & nominations', 'value' => null],
            ['label' => 'Sponsorship', 'value' => null],
            ['label' => 'Exhibition', 'value' => null],
            ['label' => 'BACCO Freelancer Forum', 'value' => null],
            ['label' => 'Media & press', 'value' => null],
        ];
    }

    public function legal(): array
    {
        return [
            ['label' => 'Privacy Policy', 'href' => '/privacy-policy'],
            ['label' => 'Terms', 'href' => '/terms'],
            ['label' => 'Sitemap', 'href' => '/sitemap.xml'],
        ];
    }

    /** Footer "important links": the pages people look for first. */
    public function importantLinks(): array
    {
        return [
            ['label' => 'National Journey', 'href' => '/national-journey'],
            ['label' => 'Divisional Summits', 'href' => '/divisional-summits'],
            ['label' => 'Award Categories', 'href' => '/awards/categories'],
            ['label' => 'Sponsors', 'href' => '/sponsors'],
            ['label' => 'Success Stories', 'href' => '/success-stories'],
        ];
    }
}
