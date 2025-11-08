<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClubSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Club::truncate();

        $clubs = [
            [
                'id' => Str::uuid(),
                'name' => 'Chess Club',
                'description' => 'A club for chess enthusiasts.',
                'categorie' => 'Academics',
                'max_members' => 50,
                'image' => '/images/default_club_image.jpg',
                'created_by' => User::where('email', 'admin@example.com')->first()->id,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Debate Society',
                'description' => 'A society for debating various topics.',
                'categorie' => 'Academics',
                'max_members' => 30,
                'image' => '/images/default_club_image.jpg',
                'created_by' => User::where('email', 'admin@example.com')->first()->id,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Football Club',
                'description' => 'A club for football lovers.',
                'categorie' => 'Sports',
                'max_members' => 100,
                'image' => '/images/default_club_image.jpg',
                'created_by' => User::where('email', 'admin@example.com')->first()->id,
            ],
        ];

        foreach ($clubs as $clubData) {
            Club::create($clubData);
        }
    }
}