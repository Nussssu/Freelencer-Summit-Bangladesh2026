<?php

namespace App\Http\Controllers;

use App\Content\InnerPageContent;
use App\Content\PageContent;
use App\Models\Page;
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
     * `_page` to override the title, description, hero or breadcrumbs (detail pages). A page deleted in Pages is a 404.
     */
    public function __invoke(Request $request): Response
    {
        $pattern = '/'.ltrim($request->route()->uri(), '/');
        abort_if(PageBlocks::offline($pattern), 404);

        return $this->render($request, $pattern, $request->route()->parameters());
    }

    /**
     * A copy made in Pages (the route fallback): its source page's layout and coded content at the copy's own URL, with
     * the copy's own blocks, title and SEO. Any other unknown URL stays a 404.
     */
    public function copy(Request $request): Response
    {
        $copy = rescue(fn () => Page::query()->where('slug', '/'.trim($request->path(), '/'))->whereNotNull('source')->first(), report: false);
        abort_unless($copy && isset(InnerPageContent::PAGES[$copy->source]) && ! str_contains($copy->source, '{'), 404);

        return $this->render($request, $copy->source, [], $copy);
    }

    private function render(Request $request, string $pattern, array $parameters, ?Page $copy = null): Response
    {
        $config = InnerPageContent::PAGES[$pattern];
        ['title' => $title, 'description' => $description] = PageContent::for($pattern);
        $title = $copy->title ?? $title;

        [$class, $method] = $config['content'] ?? [InnerPageContent::class, lcfirst($config['component'])];
        $content = app()->call([app($class), $method], $parameters);
        abort_if($content === null, 404);

        $path = '/'.ltrim($request->path(), '/');
        $page = [
            'title' => $title,
            'description' => $description,
            'path' => $path,
            'crumbs' => $copy ? [['label' => $title]] : ($config['crumbs'] ?? [['label' => $title]]),
            'hero' => PageBlocks::hero($copy ? $path : $pattern, array_diff_key($config, ['component' => 0, 'content' => 0, 'crumbs' => 0, 'plain' => 0]), $copy?->source),
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
