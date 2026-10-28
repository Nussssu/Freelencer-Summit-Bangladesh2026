<?php

namespace App\Models;

use App\Enums\MediaKind;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaItem extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['kind' => MediaKind::class, 'is_sample' => 'boolean'];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(JourneyProgram::class, 'journey_program_id');
    }
}
