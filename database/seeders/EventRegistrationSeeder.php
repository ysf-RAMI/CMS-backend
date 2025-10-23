<?php

namespace Database\Seeders;

use App\Models\EventRegistration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class EventRegistrationSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EventRegistration::truncate();
        $json = File::get(base_path('data.json'));
        $data = json_decode($json, true);

        // Seed Event Registrations
        foreach ($data['event_registration'] as $eventRegistrationData) {
            EventRegistration::create([
                'event_id' => $eventRegistrationData['eventId'],
                'user_id' => $eventRegistrationData['userId'],
                'registered_at' => Carbon::parse($eventRegistrationData['registered_at'] ?? now())->format('Y-m-d H:i:s'),
                'status' => $eventRegistrationData['status'] ?? 'registered',
            ]);
        }
    }
}