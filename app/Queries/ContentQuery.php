<?php

namespace App\Queries;

use App\Enums\DownloadKind;
use App\Models\Download;
use App\Models\Faq;
use App\Models\Page;
use Illuminate\Support\Facades\Storage;

/** Small page-scoped content: FAQs, downloads, long-text pages. */
class ContentQuery
{
    public function faqs(string $page): array
    {
        return Faq::where('page', $page)->orderBy('sort')->get()->map(fn ($f) => ['q' => $f->question, 'a' => $f->answer, 'sample' => $f->is_sample])->all();
    }

    /** Every kind is listed; `href` is null (card disabled) until a visible file exists on the public disk. */
    public function downloads(array $kinds = []): array
    {
        $rows = Download::where('visible', true)->get()->keyBy(fn ($d) => $d->kind->value);
        $kinds = $kinds ?: DownloadKind::cases();

        return array_map(function (DownloadKind $k) use ($rows) {
            $d = $rows[$k->value] ?? null;
            $ok = $d?->file_path && Storage::disk('public')->exists($d->file_path);

            return ['kind' => $k->value, 'title' => $d->title ?? $k->label(), 'href' => $ok ? Storage::disk('public')->url($d->file_path) : null, 'size' => $ok ? Storage::disk('public')->size($d->file_path) : null];
        }, $kinds);
    }

    public function page(string $slug): ?array
    {
        $p = Page::where('slug', $slug)->first();

        return $p ? ['title' => $p->title, 'sections' => $p->sections ?? [], 'draft' => $p->is_draft, 'updatedAt' => $p->last_updated_at?->toDateString()] : null;
    }
}
