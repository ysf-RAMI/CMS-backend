<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::truncate();
        $json = File::get(base_path('data_uuid.json'));
        $data = json_decode($json, true);

        // Seed Users
        foreach ($data['user'] as $userData) {
            User::create([
                'id' => $userData['id'],
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'role' => $userData['role'] ?? 'student',
                'image' => $userData['image'] ?? null,
                'department' => $userData['departement'] ?? null,
            ]);
        }
    }
}