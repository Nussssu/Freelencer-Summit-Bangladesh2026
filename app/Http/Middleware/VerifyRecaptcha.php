<?php

namespace App\Http\Middleware;

use App\Support\Settings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Public form protection when Settings › Security turns reCAPTCHA on (partials/recaptcha adds the token).
 * A missing, invalid or low-score token is refused; if Google itself is unreachable the submission goes through,
 * because losing a real lead is worse than letting one bot past for a few minutes.
 */
class VerifyRecaptcha
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! ($config = Settings::recaptcha())) {
            return $next($request);
        }
        $token = $request->header('X-Recaptcha-Token') ?: $request->input('g-recaptcha-response');

        try {
            $result = $token ? Http::asForm()->timeout(5)->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => decrypt($config['secret_key']),
                'response' => $token,
                'remoteip' => $request->ip(),
            ])->throw()->json() : ['success' => false];
        } catch (Throwable $e) {
            report($e);

            return $next($request);
        }

        $passed = ($result['success'] ?? false)
            && (($config['version'] ?? 'v3') !== 'v3' || ($result['score'] ?? 0) >= (float) ($config['threshold'] ?? 0.5));

        if (! $passed) {
            throw ValidationException::withMessages(['recaptcha' => 'We could not confirm you are not a robot. Please reload the page and try again.']);
        }

        return $next($request);
    }
}
