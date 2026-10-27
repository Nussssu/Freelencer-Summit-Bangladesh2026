<?php

namespace App\Http\Controllers;

use App\Content\PageContent;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        // Slug templates are skipped until real speakers and articles exist.
        $paths = ['/', ...array_filter(array_keys(PageContent::PAGES), fn ($p) => ! str_contains($p, '{'))];
        $urls = collect($paths)->map(fn ($p) => '<url><loc>'.e(url($p)).'</loc></url>')->implode('');

        return response('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$urls.'</urlset>')
            ->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        return response("User-agent: *\nDisallow:\n\nSitemap: ".url('/sitemap.xml')."\n")
            ->header('Content-Type', 'text/plain');
    }
}
