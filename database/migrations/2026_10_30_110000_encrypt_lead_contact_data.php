<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Leads copied submissions in plaintext, including phone numbers that the source tables encrypt, and the landing
 * exhibit enquiry popup stored phones in plaintext. Both become encrypted columns (model casts); existing rows are
 * encrypted in place.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->text('phone')->nullable()->change();
            $table->longText('data')->change();
        });
        Schema::table('exhibit_enquiries', fn (Blueprint $table) => $table->text('phone')->change());

        $this->rewrite(fn (?string $v) => $v === null ? null : Crypt::encryptString($v));
    }

    public function down(): void
    {
        $this->rewrite(fn (?string $v) => $v === null ? null : Crypt::decryptString($v));

        Schema::table('leads', function (Blueprint $table) {
            $table->string('phone', 40)->nullable()->change();
            $table->json('data')->change();
        });
        Schema::table('exhibit_enquiries', fn (Blueprint $table) => $table->string('phone', 32)->change());
    }

    private function rewrite(Closure $fn): void
    {
        DB::table('leads')->orderBy('id')->each(fn ($row) => DB::table('leads')->where('id', $row->id)->update([
            'phone' => $fn($row->phone),
            'data' => $fn($row->data),
        ]));
        DB::table('exhibit_enquiries')->orderBy('id')->each(fn ($row) => DB::table('exhibit_enquiries')->where('id', $row->id)->update(['phone' => $fn($row->phone)]));
    }
};
