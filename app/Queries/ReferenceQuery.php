<?php

namespace App\Queries;

use App\Models\District;
use App\Models\Division;

/** Select options for divisions and districts. */
class ReferenceQuery
{
    public function divisions(): array
    {
        return Division::orderBy('sort')->get(['slug', 'name', 'lat', 'lng'])
            ->map(fn ($d) => ['value' => $d->slug, 'label' => $d->name, 'lat' => (float) $d->lat, 'lng' => (float) $d->lng])->all();
    }

    /** Grouped by division for an <optgroup> select. */
    public function districts(): array
    {
        return Division::with(['districts' => fn ($q) => $q->orderBy('name')])->orderBy('sort')->get()
            ->map(fn ($d) => ['label' => $d->name, 'options' => $d->districts->map(fn ($x) => ['value' => $x->slug, 'label' => $x->name])->all()])->all();
    }

    public function districtId(?string $slug): ?int
    {
        return $slug ? District::where('slug', $slug)->value('id') : null;
    }
}
