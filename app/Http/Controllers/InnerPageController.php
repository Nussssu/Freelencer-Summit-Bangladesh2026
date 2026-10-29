<?php

namespace App\Http\Controllers;

use App\Content\InnerPageContent;
use App\Content\PageContent;
use App\Support\Seo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InnerPageController extends Controller
{
    /** Renders a built main-navigation page (InnerPageContent) with its hero, SEO meta and breadcrumbs. */
    public function __invoke(Request $request): Response
    {
        $path = '/'.$request->path();
        ['component' => $component] = $hero = InnerPageContent::PAGES[$path];
        ['title' => $title, 'description' => $description] = PageContent::for($path);

        return Inertia::render($component, [
            'page' => [
                'title' => $title,
                'description' => $description,
                'path' => $path,
                'crumbs' => [['label' => $title]],
                'hero' => array_diff_key($hero, ['component' => 0]),
            ],
        ])->withViewData(['jsonLd' => Seo::graph(Seo::breadcrumbs(['Home' => '/', $title => $path]))]);
    }
}
