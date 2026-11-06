<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Pages › Copy (a page served at its own URL with its source page's layout) and Delete (offline until restored). */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('source')->nullable()->after('slug'); // the built page a copy takes its layout from
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('source');
        });
    }
};
