<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SponsorPackage extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['benefits' => 'array', 'provisional' => 'boolean'];
    }

    public function sponsors(): HasMany
    {
        return $this->hasMany(Sponsor::class)->where('confirmed', true)->orderBy('sort');
    }
}
