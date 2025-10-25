<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = file_get_contents(base_path('data_uuid.json'));
        $data = json_decode($json, true);

        foreach ($data['event'] as $eventData) {
            Event::create([
                'id' => $eventData['id'],
                'club_id' => $eventData['clubId'],
                'title' => $eventData['title'],
                'description' => $eventData['description'],
                'date' => Carbon::parse($eventData['date']), // Parse date string to Carbon instance
                'location' => $eventData['location'],
                'image' => $eventData['image'] ?? null,
                'max_participants' => $eventData['max_participants'] ?? null,
                'created_by' => $eventData['created_by'],
                'created_at' => Carbon::parse($eventData['created_at']),
                'updated_at' => Carbon::parse($eventData['updated_at']),
                'status' => $eventData['status'] ?? 'pending',
            ]);
        }
    }
}