<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::truncate();
        // $json = File::get(base_path('data_uuid.json'));
        // $data = json_decode($json, true);

        $users = [
            [
                'id' => Str::uuid(),
                'name' => 'Student User',
                'email' => 'student@example.com',
                'password' => Hash::make('password'),
                'role' => 'student',
                'image' => null,
                'department' => 'Computer Science',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Member User',
                'email' => 'member@example.com',
                'password' => Hash::make('password'),
                'role' => 'member',
                'image' => null,
                'department' => 'Electrical Engineering',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Admin Member User',
                'email' => 'adminmember@example.com',
                'password' => Hash::make('password'),
                'role' => 'member',
                'image' => null,
                'department' => 'Mechanical Engineering',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'image' => null,
                'department' => 'Mathematics',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}