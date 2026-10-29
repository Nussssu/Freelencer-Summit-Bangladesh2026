<?php

namespace App\Content;

/**
 * Hero copy for the built main-navigation pages. Title/description for SEO stay in PageContent.
 * Wording follows the client brief; no counts that the brief itself contradicts (districts, campuses).
 */
class InnerPageContent
{
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
}
