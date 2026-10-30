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
    public const CODE_ONLY = ['downloads', 'enquiryFields', 'baccoStats', 'ics'];

    /** Blocks rebuilt by code on that page: /agenda adds per-session calendar links to the landing agenda. */
    private const LOCKED = ['/agenda' => ['agenda']];

    /** The coded content prop for a page, or null for pages without block content ("Coming next" pages). */
    public static function coded(string $slug): ?array
    {
        if ($slug === '/') {
            return app(HomeContent::class)->all();
        }
        $page = InnerPageContent::PAGES[$slug] ?? null;

        return $page ? app()->call([app(InnerPageContent::class), lcfirst($page['component'])]) : null;
    }

    /** @return array<string, mixed> block key => coded value, for the editor and the seeder */
    public static function editable(string $slug): array
    {
        return array_diff_key(static::coded($slug) ?? [], array_flip([...self::CODE_ONLY, ...(self::LOCKED[$slug] ?? [])]));
    }

    /** Builder data for a coded value: lists are wrapped as {items}, since a block's data must be an object. */
    public static function wrap(mixed $value): array
    {
        return is_array($value) && ! array_is_list($value) ? $value : ['items' => $value];
    }

    /** @return list<array{type: string, data: array}> the page's current coded content as builder blocks */
    public static function seed(string $slug): array
    {
        return array_map(fn ($key, $value) => ['type' => $key, 'data' => static::wrap($value)], array_keys($blocks = static::editable($slug)), $blocks);
    }

    /** The coded content with a published page's blocks laid over it. Drafts and missing pages keep the coded content. */
    public static function apply(string $slug, array $coded): array
    {
        $page = rescue(fn () => Page::query()->where('slug', $slug)->where('is_draft', false)->first(), report: false);
        $locked = array_flip([...self::CODE_ONLY, ...(self::LOCKED[$slug] ?? [])]);

        foreach ($page?->sections ?? [] as $block) {
            $key = $block['type'] ?? null;
            if (! is_string($key) || ! array_key_exists($key, $coded) || isset($locked[$key]) || ! is_array($block['data'] ?? null)) {
                continue;
            }
            $data = is_array($coded[$key]) && ! array_is_list($coded[$key]) ? $block['data'] : ($block['data']['items'] ?? null);
            $coded[$key] = static::merge($coded[$key], $data);
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
