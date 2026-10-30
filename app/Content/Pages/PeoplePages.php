<?php

namespace App\Content\Pages;

use App\Content\HomeContent;

/** Brief §13: speaker profile page (same people as the landing and /speakers). */
class PeoplePages
{
    public function __construct(private HomeContent $home) {}

    public function speaker(string $slug): ?array
    {
        $all = $this->home->speakers();
        $s = collect($all)->firstWhere('slug', $slug);
        if (! $s) {
            return null;
        }
        $line = implode(', ', array_filter([$s['designation'], $s['organisation']]));

        return [
            '_page' => [
                'title' => $s['name'],
                'description' => "{$s['name']}, {$line}. {$s['role']} at Freelancer Summit Bangladesh.",
                'crumbs' => [['label' => 'Speakers & Experts', 'href' => '/speakers'], ['label' => $s['name']]],
                'hero' => ['title' => $s['name'], 'emphasis' => '', 'intro' => $line, 'actions' => []],
            ],
            'speaker' => $s,
            'facts' => [
                ['icon' => 'mic', 'label' => 'Role at the summit', 'value' => $s['role']],
                ['icon' => 'building', 'label' => 'Organisation', 'value' => $s['organisation'] ?? 'To be announced'],
                ['icon' => 'globe', 'label' => 'Country', 'value' => $s['country'] ?? 'To be announced'],
            ],
            'bioEmpty' => 'The full biography is published with the speaker announcement.',
            'sessionsEmpty' => 'Session times are added when the agenda is final.',
            'others' => array_map(fn ($p) => [...$p, 'sample' => false, 'href' => "/speaker/{$p['slug']}"], array_slice(array_values(array_filter($all, fn ($p) => $p['slug'] !== $slug)), 0, 4)),
        ];
    }
}
