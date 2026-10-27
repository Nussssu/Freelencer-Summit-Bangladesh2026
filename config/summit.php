<?php

/*
| Edition-level settings. Every value the client brief leaves open (edition year,
| district count, date, venue, tagline) lives here so an admin panel or .env can
| change it without touching code. See docs/decisions.md.
*/

return [
    'name' => 'Freelancer Summit Bangladesh',
    'edition' => env('SUMMIT_EDITION', '2026'),

    // Placeholder: brief says "late November 2026, exact date to be confirmed".
    'starts_at' => env('SUMMIT_STARTS_AT', '2026-11-28T10:00:00+06:00'),
    'date_confirmed' => (bool) env('SUMMIT_DATE_CONFIRMED', false),
    'timezone' => 'Asia/Dhaka',
    'city' => 'Dhaka',
    'venue' => env('SUMMIT_VENUE'), // null = to be confirmed

    'divisions' => (int) env('SUMMIT_DIVISIONS', 8),
    'districts' => (int) env('SUMMIT_DISTRICTS', 12),
    'campuses' => (int) env('SUMMIT_CAMPUSES', 100),

    'tagline' => env('SUMMIT_TAGLINE', 'Work from anywhere. Win from here.'),
    'tagline_final' => (bool) env('SUMMIT_TAGLINE_FINAL', false),
    'hashtag' => '#FreelancerSummitBD',

    // BACCO figures as published on bposummit.org.bd; hidden until the client approves.
    'show_bacco_stats' => (bool) env('SUMMIT_SHOW_BACCO_STATS', false),

    // CMS-managed journey counters; all start at 0 until programmes run.
    'counters' => [
        'divisions_covered' => (int) env('SUMMIT_COUNTER_DIVISIONS', 0),
        'districts_covered' => (int) env('SUMMIT_COUNTER_DISTRICTS', 0),
        'institutions_activated' => (int) env('SUMMIT_COUNTER_INSTITUTIONS', 0),
        'participants_reached' => (int) env('SUMMIT_COUNTER_PARTICIPANTS', 0),
    ],

    // Official channels; null renders a "coming soon" state instead of a dead link.
    'social' => [
        'facebook' => env('SUMMIT_SOCIAL_FACEBOOK'),
        'linkedin' => env('SUMMIT_SOCIAL_LINKEDIN'),
        'youtube' => env('SUMMIT_SOCIAL_YOUTUBE'),
        'x' => env('SUMMIT_SOCIAL_X'),
    ],

    'analytics' => [
        'ga4' => env('ANALYTICS_GA4_ID'),
        'gtm' => env('ANALYTICS_GTM_ID'),
    ],

    'locales' => ['en', 'bn'],
];
