<?php

use App\Models\Page;
use App\Support\PageBlocks;
use App\Support\Settings;
use Illuminate\Database\Migrations\Migration;

/**
 * Privacy Policy and Terms became plain text pages with standard content: their CMS rows are re-seeded from the new
 * coded content (the old rows held the draft summary in a different shape). The homepage loses its removed
 * "Ideas. Talent. Opportunities." block, and the footer's legal links drop Sitemap.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['/privacy-policy' => 'Privacy Policy', '/terms' => 'Terms of Use'] as $slug => $title) {
            $page = Page::withTrashed()->firstOrCreate(['slug' => $slug], ['title' => $title, 'is_draft' => false]);
            $page->forceFill(['title' => $title, 'sections' => PageBlocks::seed($slug), 'is_draft' => false])->saveQuietly();
        }
        Page::withTrashed()->where('slug', '/sitemap')->forceDelete();

        $home = Page::withTrashed()->where('slug', '/')->first();
        if ($home && is_array($home->sections)) {
            $sections = array_values(array_filter($home->sections, fn ($b) => ($b['type'] ?? null) !== 'highlights'));
            foreach ($sections as &$block) {
                if (($block['type'] ?? null) === 'headings' && is_array($block['data'] ?? null)) {
                    unset($block['data']['highlights']);
                }
            }
            $home->forceFill(['sections' => $sections])->saveQuietly();
        }

        $legal = Settings::get('legal');
        if (is_array($legal)) {
            Settings::put('legal', array_values(array_filter($legal, fn ($l) => ! in_array($l['href'] ?? null, ['/sitemap.xml', '/sitemap'], true))));
        }
    }

    public function down(): void
    {
        // Content migration: nothing to undo.
    }
};
