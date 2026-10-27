<?php

namespace App\Http\Controllers;

use App\Content\HomeContent;
use App\Content\SiteContent;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(HomeContent $content, SiteContent $site): Response
    {
        return Inertia::render('Home', ['content' => $content->all()])
            ->withViewData(['jsonLd' => $this->jsonLd($site)]);
    }

    /** Event + Organization structured data; an unconfirmed date is published as month precision only. */
    private function jsonLd(SiteContent $site): array
    {
        $s = config('summit');
        $orgs = array_map(fn ($o) => ['@type' => 'Organization', 'name' => $o['name'], 'alternateName' => $o['short']], $site->organisers());

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Event',
                    'name' => "{$s['name']} {$s['edition']}",
                    'description' => 'The national flagship summit for Bangladeshi freelancers: campus programmes, district roadshows and divisional summits leading to the Grand Summit in Dhaka.',
                    'startDate' => $s['date_confirmed'] ? $s['starts_at'] : substr($s['starts_at'], 0, 7),
                    'eventStatus' => 'https://schema.org/EventScheduled',
                    'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
                    'location' => [
                        '@type' => 'Place',
                        'name' => $s['venue'] ?? 'Venue to be confirmed',
                        'address' => ['@type' => 'PostalAddress', 'addressLocality' => $s['city'], 'addressCountry' => 'BD'],
                    ],
                    'image' => [url('/og-image.png')],
                    'url' => url('/'),
                    'organizer' => $orgs,
                ],
                ...$orgs,
            ],
        ];
    }
}
