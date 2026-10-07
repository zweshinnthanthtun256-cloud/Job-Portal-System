<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = array_values(array_filter([
            'email_application_updates',
            'email_interviews',
            'email_recommendations',
        ], fn (string $column) => Schema::hasColumn('notification_preferences', $column)));

        if ($columns !== []) {
            Schema::table('notification_preferences', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }

    public function down(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table) {
            $table->boolean('email_application_updates')->default(true);
            $table->boolean('email_interviews')->default(true);
            $table->boolean('email_recommendations')->default(false);
        });
    }
};
