<?php

namespace App\Models;

use App\Models\Concerns\IsSubmission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramRegistration extends Model
{
    use IsSubmission;

    protected $guarded = ['id'];

    const REF_PREFIX = 'PRG';

    protected function casts(): array
    {
        return ['phone' => 'encrypted'];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(JourneyProgram::class, 'journey_program_id');
    }
}
