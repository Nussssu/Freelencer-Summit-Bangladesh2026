<?php

namespace App\Models;

use App\Enums\BoothStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booth extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['status' => BoothStatus::class, 'x' => 'float', 'y' => 'float', 'w' => 'float', 'h' => 'float', 'is_sample' => 'boolean'];
    }

    public function exhibitor(): HasOne
    {
        return $this->hasOne(Exhibitor::class);
    }
}
