<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to events table
        Schema::table('event', function (Blueprint $table) {
            $table->index('club_id');
            $table->index('created_at');
            $table->index('status');
            $table->index(['created_at', 'status']); // Composite index for common queries
        });

        // Add indexes to clubs table
        Schema::table('clubs', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('created_by');
        });

        // Add indexes to event_registration table
        Schema::table('event_registration', function (Blueprint $table) {
            $table->index('event_id');
            $table->index('user_id');
            $table->index('status');
            $table->index(['event_id', 'status']); // Composite index
        });

        // Add indexes to club_user table
        Schema::table('club_user', function (Blueprint $table) {
            $table->index('club_id');
            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event', function (Blueprint $table) {
            $table->dropIndex(['club_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at', 'status']);
        });

        Schema::table('clubs', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['created_by']);
        });

        Schema::table('event_registration', function (Blueprint $table) {
            $table->dropIndex(['event_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['event_id', 'status']);
        });

        Schema::table('club_user', function (Blueprint $table) {
            $table->dropIndex(['club_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
        });
    }
};
