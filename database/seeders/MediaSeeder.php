<?php

namespace Database\Seeders;

use App\Models\Media;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    /**
     * Data awal yang sama dengan index.php di Modul 3.
     */
    public function run(): void
    {
        $rows = [
            ['title' => 'Jujutsu Kaisen',    'type' => 'anime', 'genre' => 'Action', 'release_year' => 2020, 'episodes' => 24],
            ['title' => 'Violet Evergarden', 'type' => 'anime', 'genre' => 'Drama',  'release_year' => 2018, 'episodes' => 13],
            ['title' => 'A Silent Voice',    'type' => 'manga', 'genre' => 'Drama',  'release_year' => 2013, 'chapters' => 62],
            ['title' => 'Blue Lock',         'type' => 'manga', 'genre' => 'Sports', 'release_year' => 2018, 'chapters' => 95],
        ];

        foreach ($rows as $row) {
            Media::updateOrCreate(['title' => $row['title']], $row);
        }
    }
}
