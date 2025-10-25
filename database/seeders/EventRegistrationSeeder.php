<?php

namespace Database\Seeders;

use App\Models\EventRegistration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        $data = [
            'event_registration' => [
                [
                    'id' => 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11',
                    'eventId' => 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11',
                    'userId' => 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11',
                    'registered_at' => '2024-01-01 10:00:00',
                ],
                [
                    'id' => 'b1fccb00-0d1c-4ff9-cc7e-7cc0ce491b22',
                    'eventId' => 'b1fccb00-0d1c-4ff9-cc7e-7cc0ce491b22',
                    'userId' => 'b1fccb00-0d1c-4ff9-cc7e-7cc0ce491b22',
                    'registered_at' => '2024-01-02 11:00:00',
                ],
            ],
        ];

        foreach ($data['event_registration'] as $eventRegistrationData) {
            EventRegistration::create([
                'id' => $eventRegistrationData['id'],
                'event_id' => $eventRegistrationData['eventId'],
                'user_id' => $eventRegistrationData['userId'],
                'registered_at' => Carbon::parse($eventRegistrationData['registered_at']),
            ]);
        }
    }
}