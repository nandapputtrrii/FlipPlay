<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->words(3, true),
            'type' => Media::TYPE_ANIME,
            'genre' => fake()->randomElement(['Action', 'Drama', 'Romance', 'Sports', 'Comedy']),
            'release_year' => fake()->numberBetween(1990, 2025),
            'episodes' => fake()->numberBetween(1, 100),
            'chapters' => null,
        ];
    }

    public function manga(): static
    {
        return $this->state(fn () => [
            'type' => Media::TYPE_MANGA,
            'episodes' => null,
            'chapters' => fake()->numberBetween(1, 200),
        ]);
    }
}
