<?php

use App\Support\SubmissionColumns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('summit_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('edition', 4);
            $table->string('name');
            $table->string('email');
            $table->text('phone'); // encrypted
            $table->string('organisation')->nullable();
            $table->string('designation')->nullable();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->string('attendee_type', 32); // AttendeeType
            $table->json('interests')->nullable(); // Interest[]
            SubmissionColumns::add($table);
            $table->unique(['edition', 'email']);
        });

        Schema::create('sponsorship_enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('company');
            $table->string('contact_person');
            $table->string('designation')->nullable();
            $table->text('phone'); // encrypted
            $table->string('email');
            $table->string('interest', 64); // package slug or "not-sure"
            $table->string('budget', 64)->nullable();
            $table->text('requirements')->nullable();
            $table->text('message')->nullable();
            $table->boolean('callback_requested')->default(false);
            $table->string('status', 24)->default('new'); // SponsorshipStatus
            SubmissionColumns::add($table);
        });

        // /exhibition/apply. The landing popup keeps its own exhibit_enquiries table.
        Schema::create('exhibition_applications', function (Blueprint $table) {
            $table->id();
            $table->string('organisation');
            $table->string('sector', 64);
            $table->string('booth_requirement', 64)->nullable();
            $table->string('contact_person');
            $table->string('designation')->nullable();
            $table->string('email');
            $table->text('phone'); // encrypted
            $table->text('products')->nullable();
            $table->text('branding_needs')->nullable();
            $table->text('requirements')->nullable();
            SubmissionColumns::add($table);
        });

        Schema::create('forum_applications', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->text('phone'); // encrypted
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->string('experience_level', 24);
            $table->json('skills')->nullable();
            $table->text('services')->nullable();
            $table->string('availability', 24)->nullable();
            $table->json('marketplace_profiles')->nullable();
            SubmissionColumns::add($table);
        });

        Schema::create('program_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journey_program_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->text('phone'); // encrypted
            $table->string('institution')->nullable();
            SubmissionColumns::add($table);
            $table->unique(['journey_program_id', 'email']);
        });

        Schema::create('ambassador_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            $table->string('institution');
            $table->string('name');
            $table->string('email');
            $table->text('phone'); // encrypted
            $table->string('department')->nullable();
            $table->text('motivation')->nullable();
            SubmissionColumns::add($table);
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('topic', 32);
            $table->string('name');
            $table->string('email');
            $table->text('phone')->nullable(); // encrypted
            $table->text('message');
            SubmissionColumns::add($table);
        });

        // The landing enquiry popup joins the submission conventions (nullable: older rows have none).
        Schema::table('exhibit_enquiries', function (Blueprint $table) {
            $table->string('reference', 32)->nullable()->unique()->after('id');
            $table->string('consent_version', 32)->nullable();
            $table->timestamp('consented_at')->nullable();
            $table->char('ip_hash', 64)->nullable();
            $table->string('utm_source', 120)->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('exhibit_enquiries', function (Blueprint $table) {
            $table->dropUnique(['reference']);
            $table->dropColumn(['reference', 'consent_version', 'consented_at', 'ip_hash', 'utm_source', 'deleted_at']);
        });
        foreach (['contact_messages', 'ambassador_leads', 'program_registrations', 'forum_applications', 'exhibition_applications', 'sponsorship_enquiries', 'summit_registrations'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
