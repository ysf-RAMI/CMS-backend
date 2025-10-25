<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ClubUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('club_user')->truncate();
        // $json = File::get(base_path('data_uuid.json'));
        // $data = json_decode($json, true);

        $clubUsers = [
            [
                'id' => Str::uuid(),
                'user_id' => User::where('email', 'member@example.com')->first()->id,
                'club_id' => Club::where('name', 'Chess Club')->first()->id,
                'status' => 'approved',
                'role' => 'member',
                'joined_at' => Carbon::now(),
            ],
            [
                'id' => Str::uuid(),
                'user_id' => User::where('email', 'adminmember@example.com')->first()->id,
                'club_id' => Club::where('name', 'Chess Club')->first()->id,
                'status' => 'approved',
                'role' => 'admin-member',
                'joined_at' => Carbon::now(),
            ],
        ];

        foreach ($clubUsers as $clubUserData) {
            DB::table('club_user')->insert([
                'id' => $clubUserData['id'],
                'user_id' => $clubUserData['user_id'],
                'club_id' => $clubUserData['club_id'],
                'status' => $clubUserData['status'],
                'role' => $clubUserData['role'],
                'joined_at' => $clubUserData['joined_at'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}