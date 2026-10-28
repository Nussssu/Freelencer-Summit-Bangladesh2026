<?php

namespace App\Models;

use App\Enums\SponsorshipStatus;
use App\Models\Concerns\IsSubmission;
use Illuminate\Database\Eloquent\Model;

class SponsorshipEnquiry extends Model
{
    use IsSubmission;

    protected $guarded = ['id'];

    const REF_PREFIX = 'SPO';

    protected function casts(): array
    {
        return ['status' => SponsorshipStatus::class, 'phone' => 'encrypted', 'callback_requested' => 'boolean'];
    }
}
