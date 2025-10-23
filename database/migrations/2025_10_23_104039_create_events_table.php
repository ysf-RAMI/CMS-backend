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
        Schema::create('event', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('club')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->dateTime('date');
            $table->string('location');
            $table->string('image')->nullable();
            $table->integer('max_participants')->nullable();
            $table->foreignId('created_by')->constrained('user')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event');
    }
};
