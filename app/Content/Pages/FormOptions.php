<?php

namespace App\Content\Pages;

use App\Queries\ReferenceQuery;

/** Bits every form page needs: consent wording, districts by division, {value,label} lists. */
trait FormOptions
{
    protected function consent(): array
    {
        return ['version' => config('consent.version'), 'text' => config('consent.texts.'.config('consent.version'))];
    }

    protected function districts(): array
    {
        return app(ReferenceQuery::class)->districts();
    }

    /** ['slug' => 'Label'] → [{value, label}] */
    protected static function options(array $map): array
    {
        return array_map(fn ($v, $l) => ['value' => (string) $v, 'label' => $l], array_keys($map), $map);
    }

    protected static function h(string $title, ?string $emphasis = null): array
    {
        return ['eyebrow' => '', 'title' => $title, 'emphasis' => $emphasis, 'intro' => null];
    }
}
