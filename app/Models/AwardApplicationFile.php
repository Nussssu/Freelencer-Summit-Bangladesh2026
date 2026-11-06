<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AwardApplicationFile extends Model
{
    protected $guarded = ['id'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(AwardApplication::class, 'award_application_id');
    }
}
