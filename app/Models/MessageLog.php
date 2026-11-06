<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One email or SMS the site sent (or failed to send), for Messages › Email & SMS log. */
class MessageLog extends Model
{
    protected $guarded = ['id'];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
