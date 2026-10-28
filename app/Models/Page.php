<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['sections' => 'array', 'is_draft' => 'boolean', 'last_updated_at' => 'datetime'];
    }
}
