<?php

namespace App\Models;

use App\Models\Concerns\IsSubmission;
use Illuminate\Database\Eloquent\Model;

class ForumApplication extends Model
{
    use IsSubmission;

    protected $guarded = ['id'];

    const REF_PREFIX = 'FRM';

    protected function casts(): array
    {
        return ['phone' => 'encrypted', 'skills' => 'array', 'marketplace_profiles' => 'array'];
    }
}
