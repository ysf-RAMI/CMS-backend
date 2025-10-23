<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\DB;

class ClubUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('club_user')->truncate();
        $json = File::get(base_path('data.json'));
        $data = json_decode($json, true);

        // Seed Club_User pivot table
        foreach ($data['club_user'] as $clubUserData) {
            $user = User::find($clubUserData['userId']);
            $club = Club::find($clubUserData['clubId']);

            if ($user && $club) {
                $user->clubs()->attach($club->id, [
                    'role' => $clubUserData['role'] ?? 'student',
                    'status' => $clubUserData['status'] ?? 'pending',
                    'joined_at' => now(),
                ]);
            }
        }
    }
}