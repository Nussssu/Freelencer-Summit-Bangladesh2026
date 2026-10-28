<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/** Public IDs like FSB-REG-2026-00001: per prefix and edition, gap-free under concurrent submits (row lock). */
class ReferenceNumber
{
    public static function next(string $prefix, ?string $edition = null): string
    {
        $edition ??= (string) config('summit.edition');
        $key = "{$prefix}-{$edition}";

        $n = DB::transaction(function () use ($key) {
            DB::table('reference_sequences')->insertOrIgnore(['key' => $key, 'last' => 0]);
            $last = DB::table('reference_sequences')->where('key', $key)->lockForUpdate()->value('last') + 1;
            DB::table('reference_sequences')->where('key', $key)->update(['last' => $last]);

            return $last;
        });

        return sprintf('FSB-%s-%s-%05d', $prefix, $edition, $n);
    }
}
