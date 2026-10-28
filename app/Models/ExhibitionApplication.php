<?php

namespace App\Models;

use App\Models\Concerns\IsSubmission;
use Illuminate\Database\Eloquent\Model;

class ExhibitionApplication extends Model
{
    use IsSubmission;

    protected $guarded = ['id'];

    const REF_PREFIX = 'EXH';

    protected function casts(): array
    {
        return ['phone' => 'encrypted'];
    }
}
