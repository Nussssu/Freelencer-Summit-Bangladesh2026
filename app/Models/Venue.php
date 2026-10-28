<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venue extends Model
{
    protected $guarded = ['id'];

    public function halls(): HasMany
    {
        return $this->hasMany(Hall::class)->orderBy('sort');
    }
}
