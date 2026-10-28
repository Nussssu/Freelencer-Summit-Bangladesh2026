<?php

namespace App\Support;

/** schema.org JSON-LD for inner pages; controllers pass the result as view data `jsonLd`. */
class Seo
{
    /** @param array<string, string> $crumbs label => path, home first */
    public static function breadcrumbs(array $crumbs): array
    {
        $i = 0;

        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(fn ($label, $path) => ['@type' => 'ListItem', 'position' => ++$i, 'name' => $label, 'item' => url($path)], array_keys($crumbs), $crumbs),
        ];
    }

    /** An unconfirmed date is published at month precision; a null venue reads "Venue to be confirmed". */
    public static function event(string $name, ?string $startsAt, bool $dateConfirmed, ?string $place, string $city, string $path, ?string $description = null): array
    {
        return array_filter([
            '@type' => 'Event',
            'name' => $name,
            'description' => $description,
            'startDate' => $startsAt ? ($dateConfirmed ? $startsAt : substr($startsAt, 0, 7)) : null,
            'eventStatus' => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'location' => ['@type' => 'Place', 'name' => $place ?? 'Venue to be confirmed', 'address' => ['@type' => 'PostalAddress', 'addressLocality' => $city, 'addressCountry' => 'BD']],
            'organizer' => [['@type' => 'Organization', 'name' => 'BACCO'], ['@type' => 'Organization', 'name' => 'DoICT']],
            'url' => url($path),
        ]);
    }

    public static function article(string $headline, ?string $published, ?string $image, string $path): array
    {
        return array_filter([
            '@type' => 'NewsArticle',
            'headline' => $headline,
            'datePublished' => $published,
            'image' => $image ? [url($image)] : null,
            'publisher' => ['@type' => 'Organization', 'name' => config('summit.name')],
            'mainEntityOfPage' => url($path),
        ]);
    }

    public static function person(string $name, ?string $jobTitle, ?string $org, ?string $image, string $path): array
    {
        return array_filter([
            '@type' => 'Person',
            'name' => $name,
            'jobTitle' => $jobTitle,
            'worksFor' => $org ? ['@type' => 'Organization', 'name' => $org] : null,
            'image' => $image ? url($image) : null,
            'url' => url($path),
        ]);
    }

    public static function graph(array ...$nodes): array
    {
        return ['@context' => 'https://schema.org', '@graph' => array_values(array_filter($nodes))];
    }
}
