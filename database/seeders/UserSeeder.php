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

    public function run(): void
    {
        User::truncate();

        $users = [
            [
                'id' => Str::uuid(),
                'name' => 'Student User',
                'email' => 'student@example.com',
                'password' => Hash::make('password'),
                'role' => 'student',
                'image' => '/images/default_user_image.jpg',
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
                'image' => '/images/default_user_image.jpg',
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
                'image' => '/images/default_user_image.jpg',
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
                'image' => '/images/default_user_image.jpg',
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