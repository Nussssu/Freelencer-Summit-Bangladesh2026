<?php

namespace App\Queries;

use App\Enums\ProgramType;
use App\Models\JourneyProgram;

class ProgramQuery
{
    private const PATHS = ['divisional-summit' => '/divisional-summits', 'district-roadshow' => '/district-roadshows', 'campus-program' => '/campus-programs'];

    /** @param array{q?: string, division?: string, district?: string, status?: string} $f */
    public function list(ProgramType $type, array $f, int $perPage = 12): array
    {
        $page = JourneyProgram::with(['division', 'district'])->where('type', $type)
            ->when($f['q'] ?? null, fn ($q, $s) => $q->where('title', 'like', Paginated::like($s)))
            ->when($f['division'] ?? null, fn ($q, $d) => $q->whereRelation('division', 'slug', $d))
            ->when($f['district'] ?? null, fn ($q, $d) => $q->whereRelation('district', 'slug', $d))
            ->when($f['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->orderByRaw('starts_at is null')->orderBy('starts_at')->orderBy('title')
            ->paginate($perPage)->withQueryString();

        return Paginated::from($page, $this->card(...));
    }

    public function find(ProgramType $type, string $slug): ?array
    {
        $p = JourneyProgram::with(['division', 'district', 'campus', 'media'])->where('type', $type)->where('slug', $slug)->first();

        return $p ? [
            ...$this->card($p),
            'description' => $p->description,
            'seoTitle' => $p->seo_title, 'seoDescription' => $p->seo_description,
            'media' => $p->media->map(fn ($m) => ['kind' => $m->kind->value, 'src' => $m->src, 'youtubeId' => $m->youtube_id, 'caption' => $m->caption])->all(),
        ] : null;
    }

    public function card(JourneyProgram $p): array
    {
        return [
            'slug' => $p->slug, 'type' => $p->type->value, 'title' => $p->title,
            'startsAt' => $p->starts_at?->toIso8601String(), 'location' => $p->location, 'partner' => $p->partner,
            'division' => $p->division?->name, 'district' => $p->district?->name,
            'status' => $p->status->value, 'statusLabel' => $p->status->label(), 'registrationOpen' => $p->registration_open,
            'image' => $p->cover_image, 'sample' => $p->is_sample, 'href' => self::PATHS[$p->type->value]."/{$p->slug}",
        ];
    }
}
