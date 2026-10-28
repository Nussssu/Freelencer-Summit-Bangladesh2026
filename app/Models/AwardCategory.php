<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AwardCategory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['criteria' => 'array', 'deadline' => 'date', 'is_open' => 'boolean'];
    }

    /** Open flag set and the deadline (end of day, Dhaka) not passed. */
    public function acceptsNominations(): bool
    {
        return $this->is_open && (! $this->deadline || now(config('summit.timezone'))->lte($this->deadline->copy()->endOfDay()));
    }
}
