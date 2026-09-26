<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->string('photo_path')->nullable();
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->json('benefits')->nullable();
        });

        Schema::table('interviews', function (Blueprint $table) {
            $table->string('status')->default('scheduled')->index();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
        });

        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('proficiency');
            $table->timestamps();
        });

        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('issuer');
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('credential_url')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('database_enabled')->default(true);
            $table->boolean('email_application_updates')->default(true);
            $table->boolean('email_interviews')->default(true);
            $table->boolean('email_recommendations')->default(false);
            $table->timestamps();
        });

        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('certifications');
        Schema::dropIfExists('languages');
        Schema::table('interviews', fn (Blueprint $table) => $table->dropColumn(['status', 'cancellation_reason', 'cancelled_at']));
        Schema::table('companies', fn (Blueprint $table) => $table->dropColumn('benefits'));
        Schema::table('job_seeker_profiles', fn (Blueprint $table) => $table->dropColumn('photo_path'));
    }
};
