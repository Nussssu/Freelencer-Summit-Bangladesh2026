<?php

namespace App\Models\Concerns;

use App\Models\Lead;
use App\Support\ReferenceNumber;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

/**
 * Public form submission: reference number on create, consent version + time,
 * HMAC-hashed IP (never the raw address) and UTM attribution. Models set REF_PREFIX.
 */
trait IsSubmission
{
    use SoftDeletes;

    public static function bootIsSubmission(): void
    {
        static::creating(function (self $model) {
            $model->reference ??= ReferenceNumber::next(static::REF_PREFIX);
        });
        static::created(fn (self $model) => Lead::capture($model));
    }

    public function initializeIsSubmission(): void
    {
        $this->mergeCasts(['utm' => 'array', 'consented_at' => 'datetime']);
    }

    /** Create from validated data plus request context. */
    public static function submit(array $data, Request $request): static
    {
        $utm = array_filter((array) $request->input('utm', []), 'is_string');

        return static::create([
            ...$data,
            'consent_version' => config('consent.version'),
            'consented_at' => now(),
            'ip_hash' => $request->ip() ? hash_hmac('sha256', $request->ip(), config('app.key')) : null,
            'utm' => $utm ?: null,
            'utm_source' => isset($utm['utm_source']) ? mb_substr($utm['utm_source'], 0, 120) : null,
        ]);
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }
}
