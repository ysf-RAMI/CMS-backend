<?php

namespace Database\Seeders;

use App\Models\Club;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ClubSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Club::truncate();
        $json = File::get(base_path('data.json'));
        $data = json_decode($json, true);

        // Seed Clubs
        foreach ($data['club'] as $clubData) {
            Club::create([
                'name' => $clubData['name'],
                'description' => $clubData['description'] ?? null,
                'image' => $clubData['image'] ?? null,
                'categorie' => $clubData['categorie'] ?? null,
                'max_members' => $clubData['maxMembrs'] ?? null,
                'created_by' => $clubData['created_by'] ?? null,
            ]);
        }
    }
}