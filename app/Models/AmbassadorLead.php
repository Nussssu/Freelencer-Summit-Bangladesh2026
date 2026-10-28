<?php

namespace App\Models;

use App\Models\Concerns\IsSubmission;
use Illuminate\Database\Eloquent\Model;

class AmbassadorLead extends Model
{
    use IsSubmission;

    protected $guarded = ['id'];

    const REF_PREFIX = 'AMB';

    protected function casts(): array
    {
        return ['phone' => 'encrypted'];
    }
}
