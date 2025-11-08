<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update user table - add leading slash to image paths that don't have it
        DB::table('user')
            ->where('image', 'NOT LIKE', '/%')
            ->whereNotNull('image')
            ->update([
                    'image' => DB::raw("CONCAT('/', image)")
                ]);

        // Update club table - add leading slash to image paths that don't have it
        DB::table('club')
            ->where('image', 'NOT LIKE', '/%')
            ->whereNotNull('image')
            ->update([
                    'image' => DB::raw("CONCAT('/', image)")
                ]);

        // Update event table - add leading slash to image paths that don't have it
        DB::table('event')
            ->where('image', 'NOT LIKE', '/%')
            ->whereNotNull('image')
            ->update([
                    'image' => DB::raw("CONCAT('/', image)")
                ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove leading slash from image paths
        DB::table('user')
            ->where('image', 'LIKE', '/%')
            ->update([
                    'image' => DB::raw("SUBSTRING(image, 2)")
                ]);

        DB::table('club')
            ->where('image', 'LIKE', '/%')
            ->update([
                    'image' => DB::raw("SUBSTRING(image, 2)")
                ]);

        DB::table('event')
            ->where('image', 'LIKE', '/%')
            ->update([
                    'image' => DB::raw("SUBSTRING(image, 2)")
                ]);
    }
};
