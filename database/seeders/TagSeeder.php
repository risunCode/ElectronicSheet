<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ['name' => 'Important', 'slug' => 'important', 'color' => '#ef4444'],
            ['name' => 'Work', 'slug' => 'work', 'color' => '#3b82f6'],
            ['name' => 'Personal', 'slug' => 'personal', 'color' => '#22c55e'],
            ['name' => 'Urgent', 'slug' => 'urgent', 'color' => '#f97316'],
            ['name' => 'Archive', 'slug' => 'archive', 'color' => '#6b7280'],
        ];

        foreach ($tags as $tag) {
            Tag::updateOrCreate(
                ['slug' => $tag['slug']],
                $tag
            );
        }
    }
}
