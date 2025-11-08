<?php

namespace Database\Seeders;


use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class EventRegistrationSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $firstUser = User::first();
        $firstEvent = Event::first();

        $data = [
            'event_registration' => [
                [
                    'id' => Str::uuid(),
                    'event_id' => $firstEvent->id,
                    'user_id' => $firstUser->id,
                    'registered_at' => '2024-01-01 10:00:00',
                    'status' => 'approved',
                ],
                [
                    'id' => Str::uuid(),
                    'event_id' => $firstEvent->id,
                    'user_id' => $firstUser->id,
                    'registered_at' => '2024-01-02 11:00:00',
                    'status' => 'approved',
                ],
            ],
        ];

        foreach ($data['event_registration'] as $eventRegistrationData) {
            EventRegistration::create([
                'id' => $eventRegistrationData['id'],
                'event_id' => $eventRegistrationData['event_id'],
                'user_id' => $eventRegistrationData['user_id'],
                'registered_at' => Carbon::parse($eventRegistrationData['registered_at']),
                'status' => $eventRegistrationData['status'],
            ]);
        }
    }
}