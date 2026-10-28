<?php

namespace App\Models;

use App\Enums\AttendeeType;
use App\Models\Concerns\IsSubmission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SummitRegistration extends Model
{
    use HasFactory, IsSubmission;

    protected $guarded = ['id'];

    const REF_PREFIX = 'REG';

    protected function casts(): array
    {
        return ['attendee_type' => AttendeeType::class, 'interests' => 'array', 'phone' => 'encrypted'];
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
