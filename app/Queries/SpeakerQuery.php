<?php

namespace App\Queries;

use App\Models\Speaker;

class SpeakerQuery
{
    /** @param array{q?: string, country?: string, expertise?: string} $f */
    public function directory(array $f, int $perPage = 24): array
    {
        $page = Speaker::where('visible', true)
            ->when($f['q'] ?? null, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', Paginated::like($s))->orWhere('organisation', 'like', Paginated::like($s))))
            ->when($f['country'] ?? null, fn ($q, $c) => $q->where('country', $c))
            ->when($f['expertise'] ?? null, fn ($q, $e) => $q->whereJsonContains('expertise', $e))
            ->orderByDesc('featured')->orderBy('sort')->orderBy('name')
            ->paginate($perPage)->withQueryString();

        return Paginated::from($page, $this->card(...));
    }

    public function find(string $slug): ?array
    {
        $s = Speaker::where('visible', true)->where('slug', $slug)->with('sessions.hall')->first();

        return $s ? [
            ...$this->card($s),
            'bio' => $s->bio,
            'links' => $s->links ?? [],
            'sessions' => $s->sessions->map(fn ($x) => app(SessionQuery::class)->card($x))->all(),
        ] : null;
    }

    public function card(Speaker $s): array
    {
        return [
            'slug' => $s->slug, 'name' => $s->name, 'designation' => $s->designation, 'organisation' => $s->organisation,
            'country' => $s->country, 'role' => $s->role_label, 'photo' => $s->photo, 'expertise' => $s->expertise ?? [],
            'featured' => $s->featured, 'sample' => $s->is_sample, 'href' => "/speaker/{$s->slug}",
        ];
    }
}
