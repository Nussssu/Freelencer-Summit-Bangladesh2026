<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Speaker extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['expertise' => 'array', 'links' => 'array', 'visible' => 'boolean', 'featured' => 'boolean', 'is_sample' => 'boolean'];
    }

    public function sessions(): BelongsToMany
    {
        return $this->belongsToMany(AgendaSession::class)->orderBy('date')->orderBy('starts_at');
    }
}
