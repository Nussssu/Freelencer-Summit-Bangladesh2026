<?php

namespace App\Models;

use App\Models\Concerns\IsSubmission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumApplication extends Model
{
    use IsSubmission;

    protected $guarded = ['id'];

    const REF_PREFIX = 'FRM';

    protected function casts(): array
    {
        return ['phone' => 'encrypted', 'nid' => 'encrypted', 'skills' => 'array', 'marketplace_profiles' => 'array'];
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
