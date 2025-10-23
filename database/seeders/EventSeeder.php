<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Event::truncate();
        $json = File::get(base_path('data.json'));
        $data = json_decode($json, true);

        // Seed Events
        foreach ($data['event'] as $eventData) {
            Event::create([
                'club_id' => $eventData['clubId'],
                'title' => $eventData['title'],
                'description' => $eventData['description'],
                'date' => Carbon::parse($eventData['date'])->format('Y-m-d H:i:s'),
                'location' => $eventData['location'],
                'image' => $eventData['image'],
                'max_participants' => $eventData['max_participants'],
                'created_by' => $eventData['created_by'],
            ]);
        }
    }
}