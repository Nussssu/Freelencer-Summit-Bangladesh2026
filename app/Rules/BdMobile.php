<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Bangladesh mobile: 01XXXXXXXXX or +8801XXXXXXXXX (spaces and dashes allowed). Stored as +8801XXXXXXXXX. */
class BdMobile implements ValidationRule
{
    private const PATTERN = '/^(?:\+?880|0)(1[3-9]\d{8})$/';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match(self::PATTERN, self::strip($value))) {
            $fail('Enter a Bangladesh mobile number, like 01712345678 or +8801712345678.');
        }
    }

    public static function normalize(string $value): string
    {
        return preg_match(self::PATTERN, self::strip($value), $m) ? '+880'.$m[1] : $value;
    }

    private static function strip(string $value): string
    {
        return preg_replace('/[\s\-()]/', '', $value);
    }
}
