<?php

namespace App\Models;

use App\Enums\AwardStatus;
use App\Models\Concerns\IsSubmission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AwardApplication extends Model
{
    use IsSubmission;

    protected $guarded = ['id'];

    const REF_PREFIX = 'AWD';

    protected function casts(): array
    {
        return ['status' => AwardStatus::class, 'self_nominated' => 'boolean', 'nominator_phone' => 'encrypted', 'nominee_phone' => 'encrypted', 'portfolio_links' => 'array', 'marketplace_profiles' => 'array', 'shortlist_published' => 'boolean', 'winner_published' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AwardCategory::class, 'award_category_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(AwardApplicationFile::class);
    }
}
