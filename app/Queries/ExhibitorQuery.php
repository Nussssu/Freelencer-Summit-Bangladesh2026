<?php

namespace App\Queries;

use App\Models\Booth;
use App\Models\Exhibitor;

class ExhibitorQuery
{
    /** @param array{q?: string, sector?: string} $f */
    public function directory(array $f, int $perPage = 20): array
    {
        $page = Exhibitor::with('booth')->where('visible', true)
            ->when($f['q'] ?? null, fn ($q, $s) => $q->where('name', 'like', Paginated::like($s)))
            ->when($f['sector'] ?? null, fn ($q, $s) => $q->where('sector', $s))
            ->orderBy('name')->paginate($perPage)->withQueryString();

        return Paginated::from($page, $this->card(...));
    }

    /** Every visible exhibitor, A–Z, with the profile text, a page at a time (the full exhibitor list page). */
    public function all(int $perPage = 8): array
    {
        $page = Exhibitor::with('booth')->where('visible', true)->orderBy('name')->paginate($perPage)->withQueryString();

        return Paginated::from($page, fn ($e) => [...$this->card($e), 'description' => $e->description, 'website' => $e->website]);
    }

    public function sectors(): array
    {
        return Exhibitor::where('visible', true)->distinct()->orderBy('sector')->pluck('sector')->all();
    }

    public function find(string $slug): ?array
    {
        $e = Exhibitor::with('booth')->where('visible', true)->where('slug', $slug)->first();

        return $e ? [...$this->card($e), 'description' => $e->description, 'website' => $e->website] : null;
    }

    public function booths(): array
    {
        return Booth::orderBy('code')->get()->map(fn ($b) => [
            'code' => $b->code, 'status' => $b->status->value, 'size' => $b->size_label, 'x' => $b->x, 'y' => $b->y, 'w' => $b->w, 'h' => $b->h, 'sample' => $b->is_sample,
        ])->all();
    }

    public function card(Exhibitor $e): array
    {
        return ['slug' => $e->slug, 'name' => $e->name, 'sector' => $e->sector, 'logo' => $e->logo, 'booth' => $e->booth?->code, 'sample' => $e->is_sample, 'href' => "/exhibition/exhibitors/{$e->slug}"];
    }
}
