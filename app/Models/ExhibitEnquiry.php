<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExhibitEnquiry extends Model
{
    use HasFactory, SoftDeletes; // deleting from the admin keeps the row recoverable, like the other submissions

    protected $fillable = [
        'company_name', 'website', 'industry', 'head_office',
        'full_name', 'designation', 'email', 'phone', 'utm',
    ];

    protected function casts(): array
    {
        return ['utm' => 'array', 'phone' => 'encrypted'];
    }

    protected static function booted(): void
    {
        static::created(fn (self $m) => Lead::capture($m));
    }
}
