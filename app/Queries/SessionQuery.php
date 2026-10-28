<?php

namespace App\Queries;

use App\Models\AgendaSession;

class SessionQuery
{
    /** @param array{q?: string, date?: string, type?: string, track?: string, hall?: string} $f */
    public function list(array $f): array
    {
        return AgendaSession::with(['hall', 'speakers'])
            ->when($f['q'] ?? null, fn ($q, $s) => $q->where('title', 'like', Paginated::like($s)))
            ->when($f['date'] ?? null, fn ($q, $d) => $q->whereDate('date', $d))
            ->when($f['type'] ?? null, fn ($q, $t) => $q->where('type', $t))
            ->when($f['track'] ?? null, fn ($q, $t) => $q->where('track', $t))
            ->when($f['hall'] ?? null, fn ($q, $h) => $q->whereRelation('hall', 'slug', $h))
            ->orderBy('date')->orderBy('starts_at')->orderBy('sort')
            ->get()->map($this->card(...))->all();
    }

    public function find(string $slug): ?array
    {
        $s = AgendaSession::with(['hall', 'speakers', 'moderator'])->where('slug', $slug)->first();

        return $s ? [...$this->card($s), 'description' => $s->description, 'moderator' => $s->moderator?->name] : null;
    }

    public function card(AgendaSession $s): array
    {
        return [
            'slug' => $s->slug, 'title' => $s->title, 'type' => $s->type->value, 'typeLabel' => $s->type->label(),
            'date' => $s->date?->toDateString(), 'start' => $s->starts_at ? substr($s->starts_at, 0, 5) : null, 'end' => $s->ends_at ? substr($s->ends_at, 0, 5) : null,
            'hall' => $s->hall?->name, 'track' => $s->track, 'sample' => $s->is_sample, 'href' => "/agenda/{$s->slug}",
            'speakers' => $s->relationLoaded('speakers') ? $s->speakers->map(fn ($p) => ['name' => $p->name, 'href' => "/speaker/{$p->slug}"])->all() : [],
        ];
    }
}
