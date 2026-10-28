<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/** Rejects posts that fill the hidden `hp_company` field (bots); the form kit renders it off-screen. */
class Honeypot
{
    public const FIELD = 'hp_company';

    public function handle(Request $request, Closure $next): Response
    {
        if (filled($request->input(self::FIELD))) {
            throw ValidationException::withMessages(['form' => 'We could not accept this submission. Please try again.']);
        }

        return $next($request);
    }
}
