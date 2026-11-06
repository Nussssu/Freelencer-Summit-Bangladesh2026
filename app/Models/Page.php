<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A routed page's CMS row; `source` marks a copy (served at its own URL with that page's layout), trashed = offline. */
class Page extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['sections' => 'array', 'is_draft' => 'boolean', 'last_updated_at' => 'datetime'];
    }
}
