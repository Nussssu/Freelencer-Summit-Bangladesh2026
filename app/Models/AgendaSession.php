<?php

namespace App\Models;

use App\Enums\SessionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AgendaSession extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['type' => SessionType::class, 'date' => 'date', 'is_sample' => 'boolean', 'visible' => 'boolean', 'learn' => 'array', 'audience' => 'array'];
    }

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(Speaker::class, 'moderator_id');
    }

    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(Speaker::class)->withPivot('sort')->orderByPivot('sort');
    }
}
