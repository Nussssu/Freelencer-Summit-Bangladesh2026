<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

/** Append-only record of CMS actions (App\Support\Audit writes it; the admin can only read it). */
class AuditLog extends Model
{
    const UPDATED_AT = null;

    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /** Signed-in staff action; field names only, never values (some are encrypted or personal). */
    public static function record(string $action, string $module, string $description, ?Model $subject = null): void
    {
        static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => $module,
            'description' => Str::limit($description, 497),
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'ip_address' => request()->ip(),
        ]);
    }

    /** Eloquent created/updated/deleted by a signed-in user. Quiet saves (saveQuietly) fire no event and stay out. */
    public static function forModel(string $action, Model $model): void
    {
        if (! auth()->check() || $model instanceof self || $model instanceof SiteSetting) {
            return; // visitors' form submissions are leads, not staff actions; settings log one entry per save
        }
        $label = collect(['title', 'name', 'email', 'key', 'reference', 'slug'])->map(fn ($k) => $model->getAttribute($k))->first(fn ($v) => is_string($v) && $v !== '');
        $module = Str::headline(class_basename($model));
        $fields = $action === 'Updated' ? array_diff(array_keys($model->getChanges()), ['updated_at', 'remember_token']) : [];
        if ($action === 'Updated' && ! $fields) {
            return;
        }

        static::record($action, $module, trim("{$action} {$module} ".($label ? "\"{$label}\"" : "#{$model->getKey()}").($fields ? ' ('.implode(', ', $fields).')' : '')), $model);
    }
}
