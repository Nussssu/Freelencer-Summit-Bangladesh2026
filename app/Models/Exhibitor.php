<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exhibitor extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['visible' => 'boolean', 'is_sample' => 'boolean'];
    }

    public function booth(): BelongsTo
    {
        return $this->belongsTo(Booth::class);
    }
}
