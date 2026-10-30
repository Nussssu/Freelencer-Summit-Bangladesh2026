<?php

namespace App\Http\Controllers;

use App\Content\InnerPageContent;
use App\Content\PageContent;
use App\Support\PageBlocks;
use App\Support\Seo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InnerPageController extends Controller
{
    /**
     * Renders a built page (InnerPageContent::PAGES, keyed by route pattern) with its hero, SEO meta and breadcrumbs.
     * The content method receives the route parameters; it returns null for a missing record (404) and may return
     * `_page` to override the title, description, hero or breadcrumbs (detail pages).
     */
    public function __invoke(Request $request): Response
    {
        $pattern = '/'.ltrim($request->route()->uri(), '/');
        $config = InnerPageContent::PAGES[$pattern];
        ['title' => $title, 'description' => $description] = PageContent::for($pattern);

        [$class, $method] = $config['content'] ?? [InnerPageContent::class, lcfirst($config['component'])];
        $content = app()->call([app($class), $method], $request->route()->parameters());
        abort_if($content === null, 404);

        $path = '/'.$request->path();
        $page = [
            'title' => $title,
            'description' => $description,
            'path' => $path,
            'crumbs' => $config['crumbs'] ?? [['label' => $title]],
            'hero' => array_diff_key($config, ['component' => 0, 'content' => 0, 'crumbs' => 0]),
        ];
        if (isset($content['_page'])) {
            $over = $content['_page'];
            $page = [...$page, ...array_diff_key($over, ['hero' => 0]), 'hero' => [...$page['hero'], ...($over['hero'] ?? [])]];
            unset($content['_page']);
        }

        if (is_array($content)) {
            $content = PageBlocks::apply($path, $content);
        }

        $trail = ['Home' => '/'];
        foreach ($page['crumbs'] as $c) {
            $trail[$c['label']] = $c['href'] ?? $page['path'];
        }

        return Inertia::render($config['component'], ['page' => $page, 'content' => $content])
            ->withViewData(['jsonLd' => Seo::graph(Seo::breadcrumbs($trail))]);
    }
}
