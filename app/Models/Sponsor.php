<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sponsor extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['confirmed' => 'boolean', 'is_sample' => 'boolean'];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(SponsorPackage::class, 'sponsor_package_id');
    }
}
