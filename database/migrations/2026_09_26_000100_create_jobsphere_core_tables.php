<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->after('id');
            $table->string('last_name')->after('first_name');
            $table->string('role')->default('job_seeker')->index();
            $table->string('status')->default('active')->index();
            $table->softDeletes();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('industry')->nullable()->index();
            $table->string('size')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable()->index();
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('verification_status')->default('pending')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('company_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('recruiter');
            $table->timestamps();
            $table->unique(['company_id', 'user_id']);
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description');
            $table->longText('responsibilities')->nullable();
            $table->longText('requirements')->nullable();
            $table->string('employment_type')->index();
            $table->string('work_arrangement')->index();
            $table->string('experience_level')->index();
            $table->string('country')->nullable();
            $table->string('city')->nullable()->index();
            $table->unsignedBigInteger('salary_min')->nullable();
            $table->unsignedBigInteger('salary_max')->nullable();
            $table->char('salary_currency', 3)->default('USD');
            $table->boolean('salary_visible')->default(true);
            $table->unsignedInteger('vacancies')->default(1);
            $table->string('status')->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->date('application_deadline')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'published_at']);
            $table->index(['company_id', 'status']);
        });

        Schema::create('job_skill', function (Blueprint $table) {
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->primary(['job_id', 'skill_id']);
        });

        Schema::create('job_seeker_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('headline')->nullable();
            $table->text('summary')->nullable();
            $table->string('city')->nullable()->index();
            $table->string('country')->nullable();
            $table->string('experience_level')->nullable();
            $table->string('preferred_employment_type')->nullable();
            $table->unsignedBigInteger('expected_salary')->nullable();
            $table->string('resume_path')->nullable();
            $table->timestamps();
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->text('cover_letter')->nullable();
            $table->unsignedBigInteger('expected_salary')->nullable();
            $table->date('availability_date')->nullable();
            $table->string('status')->default('submitted')->index();
            $table->text('employer_notes')->nullable();
            $table->timestamp('applied_at');
            $table->timestamps();
            $table->unique(['job_id', 'user_id']);
            $table->index(['job_id', 'status']);
        });

        Schema::create('application_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('saved_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['job_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_jobs');
        Schema::dropIfExists('application_status_histories');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('job_seeker_profiles');
        Schema::dropIfExists('job_skill');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('company_user');
        Schema::dropIfExists('companies');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'role', 'status', 'deleted_at']);
        });
    }
};
