<?php

namespace App\Models;

use App\Enums\PartnerCategory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['category' => PartnerCategory::class, 'is_sample' => 'boolean'];
    }
}
