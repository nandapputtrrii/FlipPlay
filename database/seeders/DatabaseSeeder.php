<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(MediaSeeder::class);

        $ani = User::firstOrCreate(
            ['email' => 'ani@flipplay.test'],
            ['name' => 'Ani', 'password' => 'password123']
        );
        User::firstOrCreate(
            ['email' => 'budi@flipplay.test'],
            ['name' => 'Budi', 'password' => 'password123']
        );

        // Ani sudah punya satu media di koleksinya.
        $ani->collection()->syncWithoutDetaching(
            Media::where('title', 'Jujutsu Kaisen')->pluck('id')
        );
    }
}
