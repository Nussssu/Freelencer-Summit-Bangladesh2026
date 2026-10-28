<?php

namespace App\Models;

use App\Enums\NewsCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsPost extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['category' => NewsCategory::class, 'published_at' => 'datetime', 'is_sample' => 'boolean'];
    }
}
