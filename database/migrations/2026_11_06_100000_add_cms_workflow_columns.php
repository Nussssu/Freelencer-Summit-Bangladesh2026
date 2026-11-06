<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Admin workflows on the existing tables (additive only, no data is dropped): a status and internal notes on every
 * public submission, a follow-up date on leads, the agenda detail the public session pages show, a role on success
 * stories, a showcase photo on award applications, and one log row per email/SMS sent.
 */
return new class extends Migration
{
    /** table => default status (null = the table already has a status column). */
    private const SUBMISSIONS = [
        'summit_registrations' => 'registered',
        'forum_applications' => 'pending',
        'ambassador_leads' => 'pending',
        'program_registrations' => 'registered',
        'exhibition_applications' => 'new',
        'contact_messages' => 'new',
        'exhibit_enquiries' => null,
        'sponsorship_enquiries' => null,
        'award_applications' => null,
    ];

    public function up(): void
    {
        foreach (self::SUBMISSIONS as $table => $status) {
            Schema::table($table, function (Blueprint $t) use ($table, $status) {
                if ($status !== null && ! Schema::hasColumn($table, 'status')) {
                    $t->string('status', 32)->default($status)->index();
                }
                if (! Schema::hasColumn($table, 'notes')) {
                    $t->text('notes')->nullable();
                }
            });
        }

        Schema::table('leads', function (Blueprint $t) {
            $t->dateTime('follow_up_at')->nullable()->index();
        });

        Schema::table('award_applications', function (Blueprint $t) {
            $t->unsignedTinyInteger('score')->nullable(); // jury evaluation, 0–100
            $t->string('photo')->nullable(); // portrait for the public shortlist / winners showcase
        });

        Schema::table('agenda_sessions', function (Blueprint $t) {
            $t->text('summary')->nullable();
            $t->json('learn')->nullable();
            $t->json('audience')->nullable();
            $t->string('speaker_name')->nullable(); // shown when no speaker record is linked
            $t->boolean('visible')->default(true)->index();
        });

        Schema::table('success_stories', function (Blueprint $t) {
            $t->string('role')->nullable();
        });

        Schema::create('message_logs', function (Blueprint $t) {
            $t->id();
            $t->string('channel', 16)->default('email'); // email | sms
            $t->string('recipient');
            $t->string('subject')->nullable();
            $t->string('status', 16)->default('sent'); // sent | failed
            $t->text('error')->nullable();
            $t->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamps();
            $t->index(['channel', 'created_at']);
        });

        // The six seeded "sample" sessions were never on the site (the agenda read coded rows); keep them, hidden.
        DB::table('agenda_sessions')->where('is_sample', true)->update(['visible' => false]);
    }

    public function down(): void
    {
        Schema::dropIfExists('message_logs');
        Schema::table('success_stories', fn (Blueprint $t) => $t->dropColumn('role'));
        Schema::table('agenda_sessions', fn (Blueprint $t) => $t->dropColumn(['summary', 'learn', 'audience', 'speaker_name', 'visible']));
        Schema::table('award_applications', fn (Blueprint $t) => $t->dropColumn(['score', 'photo']));
        Schema::table('leads', fn (Blueprint $t) => $t->dropColumn('follow_up_at'));
        foreach (self::SUBMISSIONS as $table => $status) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn($status !== null ? ['status', 'notes'] : ['notes']));
        }
    }
};
