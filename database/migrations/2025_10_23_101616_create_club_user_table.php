<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('club_user', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->constrained('user')->onDelete('cascade');
            $table->uuid('club_id')->constrained('club')->onDelete('cascade');
            $table->enum('role', ['admin-member', 'member', 'student'])->default('student');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'club_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_user');
    }
};
