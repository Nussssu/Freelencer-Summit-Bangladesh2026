<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;

/** Columns every public form submission shares (reference, consent, privacy-safe attribution). */
class SubmissionColumns
{
    public static function add(Blueprint $table): void
    {
        $table->string('reference', 32)->unique();
        $table->string('consent_version', 32);
        $table->timestamp('consented_at');
        $table->char('ip_hash', 64)->nullable();
        $table->string('utm_source', 120)->nullable()->index();
        $table->json('utm')->nullable();
        $table->timestamps();
        $table->softDeletes();
    }
}
