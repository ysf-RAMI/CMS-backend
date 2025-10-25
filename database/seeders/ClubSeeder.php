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
                'image' => null,
                'categorie' => 'Academics',
                'max_members' => 50,
                'created_by' => User::where('email', 'admin@example.com')->first()->id,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Debate Society',
                'description' => 'A society for debating various topics.',
                'image' => null,
                'categorie' => 'Academics',
                'max_members' => 30,
                'created_by' => User::where('email', 'admin@example.com')->first()->id,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Football Club',
                'description' => 'A club for football lovers.',
                'image' => null,
                'categorie' => 'Sports',
                'max_members' => 100,
                'created_by' => User::where('email', 'admin@example.com')->first()->id,
            ],
        ];

        foreach ($clubs as $clubData) {
            Club::create($clubData);
        }
    }
}