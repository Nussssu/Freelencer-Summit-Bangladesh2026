<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * CMS settings (site_settings rows, one JSON value per key), read once per request or queued job.
 * Every reader passes the coded default, so an empty table renders the site exactly as before.
 */
class Settings
{
    /** @var array<string, mixed>|null */
    private static ?array $cache = null;

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::all()[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        static::flush();
    }

    /** Forget the per-request copy (after a write, and before each queued job). */
    public static function flush(): void
    {
        static::$cache = null;
    }

    /** @return array<string, mixed> */
    public static function all(): array
    {
        try {
            return static::$cache ??= SiteSetting::query()->pluck('value', 'key')->all();
        } catch (Throwable) {
            return []; // table not migrated yet (fresh install, early console commands)
        }
    }

    /** Site name, tagline, logos, favicon and contact details, with the config values as defaults. */
    public static function identity(): array
    {
        $saved = array_filter(static::get('identity', []), fn ($v) => $v !== null && $v !== '');

        return $saved + [
            'site_name' => config('summit.name'), 'tagline' => config('summit.tagline'),
            'logo' => null, 'logo_dark' => null, 'favicon' => null, 'phone' => null, 'email' => null, 'address' => null,
        ];
    }

    /** reCAPTCHA settings when protection is on and both keys are set, else null (forms unprotected). */
    public static function recaptcha(): ?array
    {
        $r = static::get('recaptcha', []);

        return ! empty($r['enabled']) && ! empty($r['site_key']) && ! empty($r['secret_key']) ? $r : null;
    }

    /** Point the mailer at the SMTP details saved in the CMS, when there are any; .env stays the fallback. */
    public static function applyMail(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }
        $mail = SiteSetting::find('mail')?->value ?? [];
        $smtp = array_filter($mail['smtp'] ?? [], fn ($v) => $v !== null && $v !== '');

        if (isset($smtp['host'])) {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $smtp['host'],
                'mail.mailers.smtp.port' => (int) ($smtp['port'] ?? 587),
                'mail.mailers.smtp.scheme' => $smtp['scheme'] ?? null,
                'mail.mailers.smtp.username' => $smtp['username'] ?? null,
                'mail.mailers.smtp.password' => isset($smtp['password']) ? decrypt($smtp['password']) : null,
            ]);
        }
        if (! empty($mail['from_address'])) {
            config(['mail.from.address' => $mail['from_address'], 'mail.from.name' => $mail['from_name'] ?? config('mail.from.name')]);
        }
    }
}
