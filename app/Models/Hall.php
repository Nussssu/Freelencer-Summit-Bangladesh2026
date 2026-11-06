<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hall extends Model
{
    protected $guarded = ['id'];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(AgendaSession::class);
    }
}
