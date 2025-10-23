<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class AnnouncementSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Announcement::truncate();
        $json = File::get(base_path('data.json'));
        $data = json_decode($json, true);

        // Seed Announcements
        foreach ($data['announcement'] as $announcementData) {
            Announcement::create([
                'club_id' => $announcementData['clubId'] ?? null,
                'title' => $announcementData['title'],
                'content' => $announcementData['content'],
                'created_by' => $announcementData['created_by'],
            ]);
        }
    }
}