<?php

namespace App\Queries;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** Paginator → {data, meta} (types/inner.ts `Paginated<T>`). */
class Paginated
{
    public static function from(LengthAwarePaginator $page, callable $map): array
    {
        return [
            'data' => array_map($map, $page->items()),
            'meta' => ['page' => $page->currentPage(), 'lastPage' => $page->lastPage(), 'total' => $page->total(), 'perPage' => $page->perPage()],
        ];
    }

    /** Escape LIKE wildcards in user search text. */
    public static function like(string $q): string
    {
        return '%'.addcslashes($q, '%_\\').'%';
    }
}
