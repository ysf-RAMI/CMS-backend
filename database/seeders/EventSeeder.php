<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Club;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
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

        $chessClubId = Club::where('name', 'Chess Club')->first()->id;
        $adminUserId = User::where('email', 'admin@example.com')->first()->id;

        $events = [
            [
                'id' => Str::uuid(),
                'club_id' => $chessClubId,
                'title' => 'Chess Tournament',
                'description' => 'An exciting chess tournament for all skill levels.',
                'date' => Carbon::now()->addDays(7),
                'location' => 'University Hall',
                'image' => null,
                'max_participants' => 32,
                'created_by' => $adminUserId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'status' => 'pending',
            ],
            [
                'id' => Str::uuid(),
                'club_id' => $chessClubId,
                'title' => 'Chess Workshop: Opening Strategies',
                'description' => 'Learn advanced opening strategies from experienced players.',
                'date' => Carbon::now()->addDays(14),
                'location' => 'Club Room 101',
                'image' => null,
                'max_participants' => 20,
                'created_by' => $adminUserId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'status' => 'pending',
            ],
        ];

        foreach ($events as $eventData) {
            Event::create($eventData);
        }
    }
}