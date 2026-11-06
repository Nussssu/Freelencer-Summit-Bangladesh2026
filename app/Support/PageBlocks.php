<?php

namespace App\Support;

use App\Content\HomeContent;
use App\Content\InnerPageContent;
use App\Models\Page;

/**
 * Page content blocks ("blocks mirror current props"). A page's blocks are the top-level keys of the content prop its
 * controller already builds (HomeContent::all(), InnerPageContent::<page>()). Each block stores that key's value in the
 * same shape, and the controller lays published blocks over the coded value, so Vue always gets the shape it expects.
 */
class PageBlocks
{
    /** Keys filled by code or the database (downloads, enquiry form fields, calendar links), never by the CMS. */
    public const CODE_ONLY = ['downloads', 'enquiryFields', 'baccoStats', 'ics', 'latestNews'];

    /**
     * Blocks rebuilt by code on that page: /agenda adds per-session calendar links to the landing agenda; the programme
     * directories' filters and the contact topic come from the URL. Lazy (closure) values, the directory and news
     * lists, are live queries and never blocks either.
     */
    private const LOCKED = [
        '/agenda' => ['agenda'],
        '/divisional-summits' => ['filters'],
        '/district-roadshows' => ['filters'],
        '/campus-programs' => ['filters'],
        '/contact' => ['topic'],
        // Record lists owned by an admin module (Event Management, Content Management, Awards Management): the module
        // is the one place to edit them, so a page block can never hold a stale copy. Dot paths lock one key inside a
        // block and leave the rest of it (headlines, text) editable in Pages.
        '/' => ['speakers', 'agenda', 'partners', 'storyCards', 'trust.groups', 'journey.counters', 'journey.directory', 'awards.categories', 'awards.categoriesProvisional', 'awards.deadline'],
        '/speakers' => ['speakers'],
        '/grand-summit' => ['speakers', 'sessions', 'sessionsSample'],
        '/national-journey' => ['divisionSummits', 'journey.counters', 'journey.directory'],
        '/exhibition' => ['booths', 'stats'],
        '/exhibition/exhibitors' => ['exhibitors'],
        '/success-stories' => ['stories', 'filters'],
        '/partners' => ['partners'],
        '/sponsors' => ['tiers', 'provisional'],
        '/sponsorship-opportunity' => ['tiers'],
        '/media' => ['tiles'],
        '/awards' => ['awards.categories', 'awards.categoriesProvisional', 'awards.deadline', 'categoryTexts', 'shortlist', 'winners'],
        '/awards/nominate' => ['categories', 'provisional'],
        '/awards/nominees' => ['nominees'],
        '/awards/winners' => ['winners'],
    ];

    /** @return array{0: list<string>, 1: list<string>} the page's locked top-level keys and locked dot paths */
    private static function locked(string $slug): array
    {
        $all = self::LOCKED[$slug] ?? [];

        return [array_values(array_filter($all, fn ($k) => ! str_contains($k, '.'))), array_values(array_filter($all, fn ($k) => str_contains($k, '.')))];
    }

    /** The coded content prop for a page, or null for pages without block content ("Coming next" pages). */
    public static function coded(string $slug): ?array
    {
        if ($slug === '/') {
            return app(HomeContent::class)->all();
        }
        if (str_contains($slug, '{')) {
            return null;
        }
        $page = InnerPageContent::PAGES[$slug] ?? null;
        if (! $page) {
            return null;
        }

        [$class, $method] = $page['content'] ?? [InnerPageContent::class, lcfirst($page['component'])];

        return rescue(fn () => app()->call([app($class), $method]), report: false);
    }

    /** Deleted in Pages (trashed): the page is offline until it is restored. */
    public static function offline(string $slug): bool
    {
        return rescue(fn () => Page::onlyTrashed()->where('slug', $slug)->exists(), false, report: false);
    }

    /** @return array<string, mixed> block key => coded value, for the editor and the seeder */
    public static function editable(string $slug): array
    {
        $coded = array_filter(static::coded($slug) ?? [], fn ($v) => ! $v instanceof \Closure);
        [$keys, $paths] = static::locked($slug);
        $coded = array_diff_key($coded, array_flip([...self::CODE_ONLY, ...$keys]));
        foreach ($paths as $path) {
            data_forget($coded, $path); // e.g. trust.groups: the strip's text stays editable, its partners do not
        }

        return $coded;
    }

    /** Builder data for a coded value: lists are wrapped as {items}, since a block's data must be an object. */
    public static function wrap(mixed $value): array
    {
        return is_array($value) && ! array_is_list($value) ? $value : ['items' => $value];
    }

    /** @return list<array{type: string, data: array}> the page's current coded content (hero first) as builder blocks */
    public static function seed(string $slug): array
    {
        $hero = static::codedHero($slug);

        return [
            ...($hero ? [['type' => 'hero', 'data' => $hero]] : []),
            ...array_map(fn ($key, $value) => ['type' => $key, 'data' => static::wrap($value)], array_keys($blocks = static::editable($slug)), $blocks),
        ];
    }

    /**
     * A built page's hero as coded in InnerPageContent::PAGES (title, coloured part, intro, image, buttons), or null for
     * the homepage (its hero is a content block) and detail pages (their hero comes from the record).
     *
     * @return array{title: string, emphasis: string, intro: string, image: ?string, actions: list<array{label: string, href: string, tone: string}>}|null
     */
    public static function codedHero(string $slug): ?array
    {
        $config = str_contains($slug, '{') ? null : (InnerPageContent::PAGES[$slug] ?? null);

        return $config ? [
            'title' => $config['title'] ?? '',
            'emphasis' => $config['emphasis'] ?? '',
            'intro' => $config['intro'] ?? '',
            'image' => $config['image'] ?? null,
            'actions' => $config['actions'] ?? [],
        ] : null;
    }

    /**
     * The coded hero with a published page's "hero" block laid over it; a blank title or image keeps the coded one.
     * A copy reads its own blocks over its source page's hero.
     */
    public static function hero(string $slug, array $coded, ?string $source = null): array
    {
        if (! static::codedHero($source ?? $slug)) {
            return $coded;
        }
        $page = rescue(fn () => Page::query()->where('slug', $slug)->where('is_draft', false)->first(), report: false);
        $block = collect($page?->sections ?? [])->firstWhere('type', 'hero')['data'] ?? null;
        if (! is_array($block)) {
            return $coded;
        }
        foreach (['title', 'image'] as $k) {
            if (is_string($block[$k] ?? null) && $block[$k] !== '') {
                $coded[$k] = $block[$k];
            }
        }
        // The coloured part and the intro can be cleared.
        foreach (['emphasis', 'intro'] as $k) {
            if (array_key_exists($k, $block)) {
                $coded[$k] = (string) $block[$k];
            }
        }
        if (is_array($block['actions'] ?? null)) {
            $coded['actions'] = array_values(array_filter(array_map(fn ($a) => [
                'label' => trim((string) ($a['label'] ?? '')),
                'href' => trim((string) ($a['href'] ?? '')),
                'tone' => ($a['tone'] ?? 'gold') === 'ghost' ? 'ghost' : 'gold',
            ], $block['actions']), fn ($a) => $a['label'] !== '' && $a['href'] !== ''));
        }

        return $coded;
    }

    /** The coded content with a published page's blocks laid over it. Drafts and missing pages keep the coded content. */
    public static function apply(string $slug, array $coded): array
    {
        $page = rescue(fn () => Page::query()->where('slug', $slug)->where('is_draft', false)->first(), report: false);
        [$keys, $paths] = static::locked($slug);
        $locked = array_flip([...self::CODE_ONLY, ...$keys]);
        $kept = []; // locked dot paths keep the module's value whatever an older block stored there
        foreach ($paths as $path) {
            if (data_get($coded, $path, $missing = new \stdClass) !== $missing) {
                $kept[$path] = data_get($coded, $path);
            }
        }

        foreach ($page?->sections ?? [] as $block) {
            $key = $block['type'] ?? null;
            if (! is_string($key) || ! array_key_exists($key, $coded) || isset($locked[$key]) || $coded[$key] instanceof \Closure || ! is_array($block['data'] ?? null)) {
                continue;
            }
            $data = is_array($coded[$key]) && ! array_is_list($coded[$key]) ? $block['data'] : ($block['data']['items'] ?? null);
            $coded[$key] = static::merge($coded[$key], $data);
        }
        foreach ($kept as $path => $value) {
            data_set($coded, $path, $value);
        }

        return $coded;
    }

    /**
     * Objects merge key by key (only keys the code defines, minus CODE_ONLY); lists and scalars are replaced whole,
     * so removing an item in the CMS removes it on the site.
     */
    public static function merge(mixed $coded, mixed $cms): mixed
    {
        if (! is_array($coded) || $coded === [] || array_is_list($coded)) {
            return $cms;
        }
        if (! is_array($cms)) {
            return $coded;
        }
        foreach ($cms as $k => $v) {
            if (array_key_exists($k, $coded) && ! in_array($k, self::CODE_ONLY, true)) {
                $coded[$k] = static::merge($coded[$k], $v);
            }
        }

        return $coded;
    }
}
