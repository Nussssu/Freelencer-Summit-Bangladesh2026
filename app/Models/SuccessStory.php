<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuccessStory extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['published' => 'boolean', 'is_sample' => 'boolean'];
    }
}
