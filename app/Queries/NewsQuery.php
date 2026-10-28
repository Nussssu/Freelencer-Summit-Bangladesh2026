<?php

namespace App\Queries;

use App\Models\NewsPost;

class NewsQuery
{
    public function list(?string $category, int $perPage = 12): array
    {
        $page = NewsPost::whereNotNull('published_at')->where('published_at', '<=', now())
            ->when($category, fn ($q, $c) => $q->where('category', $c))
            ->latest('published_at')->paginate($perPage)->withQueryString();

        return Paginated::from($page, $this->card(...));
    }

    public function find(string $slug): ?array
    {
        $p = NewsPost::whereNotNull('published_at')->where('published_at', '<=', now())->where('slug', $slug)->first();
        if (! $p) {
            return null;
        }
        $related = NewsPost::whereNotNull('published_at')->where('published_at', '<=', now())->where('category', $p->category)->whereKeyNot($p->id)->latest('published_at')->limit(3)->get();

        return [...$this->card($p), 'body' => $p->body, 'seoTitle' => $p->seo_title, 'seoDescription' => $p->seo_description, 'related' => $related->map($this->card(...))->all()];
    }

    public function card(NewsPost $p): array
    {
        return [
            'slug' => $p->slug, 'title' => $p->title, 'excerpt' => $p->excerpt, 'category' => $p->category->value, 'categoryLabel' => $p->category->label(),
            'publishedAt' => $p->published_at?->toIso8601String(), 'image' => $p->cover_image, 'sample' => $p->is_sample, 'href' => "/news/{$p->slug}",
        ];
    }
}
