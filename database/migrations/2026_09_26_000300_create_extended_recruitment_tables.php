<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('address')->nullable();
            $table->string('current_position')->nullable();
            $table->string('preferred_location')->nullable();
            $table->string('availability')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('portfolio_url')->nullable();
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->unsignedSmallInteger('founded_year')->nullable();
            $table->text('culture')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('cover_path')->nullable();
        });

        Schema::create('educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('institution');
            $table->string('degree');
            $table->string('field')->nullable();
            $table->unsignedSmallInteger('start_year');
            $table->unsignedSmallInteger('end_year')->nullable();
            $table->boolean('currently_studying')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('company');
            $table->string('position');
            $table->string('employment_type')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('currently_working')->default(false);
            $table->text('responsibilities')->nullable();
            $table->timestamps();
        });

        Schema::create('user_skill', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->string('proficiency')->default('intermediate');
            $table->primary(['user_id', 'skill_id']);
        });

        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->boolean('is_primary')->default(true);
            $table->timestamps();
        });

        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('timezone');
            $table->string('type');
            $table->string('location')->nullable();
            $table->string('meeting_url')->nullable();
            $table->string('interviewer')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['date', 'start_time']);
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->restrictOnDelete();
            $table->nullableMorphs('reportable');
            $table->string('reason');
            $table->text('details')->nullable();
            $table->string('status')->default('open')->index();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event')->index();
            $table->nullableMorphs('subject');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('interviews');
        Schema::dropIfExists('resumes');
        Schema::dropIfExists('user_skill');
        Schema::dropIfExists('experiences');
        Schema::dropIfExists('educations');
        Schema::table('companies', fn (Blueprint $table) => $table->dropColumn(['phone', 'address', 'founded_year', 'culture', 'linkedin_url', 'cover_path']));
        Schema::table('job_seeker_profiles', fn (Blueprint $table) => $table->dropColumn(['phone', 'date_of_birth', 'gender', 'address', 'current_position', 'preferred_location', 'availability', 'linkedin_url', 'github_url', 'portfolio_url']));
    }
};
