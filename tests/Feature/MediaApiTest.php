<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaApiTest extends TestCase
{
    use RefreshDatabase;

    private function animePayload(array $override = []): array
    {
        return array_merge([
            'title' => 'Frieren',
            'type' => 'anime',
            'genre' => 'Fantasy',
            'release_year' => 2023,
            'episodes' => 28,
        ], $override);
    }

    public function test_index_returns_paginated_media(): void
    {
        Media::factory()->count(3)->create();

        $this->getJson('/api/media')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data' => [['id', 'title', 'type', 'genre', 'release_year', 'details']], 'links', 'meta']);
    }

    public function test_index_can_search_by_title_and_filter_by_type(): void
    {
        Media::factory()->create(['title' => 'Jujutsu Kaisen']);
        Media::factory()->create(['title' => 'Violet Evergarden']);
        Media::factory()->manga()->create(['title' => 'Jujutsu Kaisen Manga']);

        $this->getJson('/api/media?search=jujutsu')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/media?search=jujutsu&type=manga')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/media?search=tidak-ada')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/media?type=novel')->assertStatus(422)->assertJsonValidationErrors(['type']);
    }

    public function test_show_includes_users_who_collected_the_media(): void
    {
        $media = Media::factory()->create();
        $user = User::factory()->create(['name' => 'Ani']);
        $user->collection()->attach($media);

        $this->getJson("/api/media/{$media->id}")
            ->assertOk()
            ->assertJsonPath('data.title', $media->title)
            ->assertJsonPath('data.users_count', 1)
            ->assertJsonPath('data.users.0.name', 'Ani');
    }

    public function test_show_returns_404_json_for_unknown_media(): void
    {
        $this->getJson('/api/media/999')
            ->assertNotFound()
            ->assertJson(['message' => 'Data tidak ditemukan.']);
    }

    public function test_store_creates_anime(): void
    {
        $this->postJson('/api/media', $this->animePayload())
            ->assertCreated()
            ->assertJsonPath('data.title', 'Frieren')
            ->assertJsonPath('data.episodes', 28)
            ->assertJsonMissingPath('data.chapters');

        $this->assertDatabaseHas('media', ['title' => 'Frieren', 'type' => 'anime']);
    }

    public function test_store_creates_manga(): void
    {
        $this->postJson('/api/media', [
            'title' => 'Vagabond', 'type' => 'manga', 'genre' => 'Action',
            'release_year' => 1998, 'chapters' => 327,
        ])->assertCreated()->assertJsonPath('data.chapters', 327);
    }

    public function test_store_rejects_empty_title(): void
    {
        $this->postJson('/api/media', $this->animePayload(['title' => '']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title'])
            ->assertJsonPath('errors.title.0', 'Judul media wajib diisi.');

        $this->assertDatabaseCount('media', 0);
    }

    public function test_store_rejects_duplicate_title(): void
    {
        Media::factory()->create(['title' => 'Frieren']);

        $this->postJson('/api/media', $this->animePayload())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        $this->assertDatabaseCount('media', 1);
    }

    public function test_store_enforces_type_specific_fields(): void
    {
        // anime tanpa episodes
        $this->postJson('/api/media', $this->animePayload(['episodes' => null]))
            ->assertStatus(422)->assertJsonValidationErrors(['episodes']);

        // anime dengan chapters
        $this->postJson('/api/media', $this->animePayload(['chapters' => 10]))
            ->assertStatus(422)->assertJsonValidationErrors(['chapters']);

        // jenis tidak dikenal
        $this->postJson('/api/media', $this->animePayload(['type' => 'novel']))
            ->assertStatus(422)->assertJsonValidationErrors(['type']);
    }

    public function test_update_changes_fields_and_allows_keeping_own_title(): void
    {
        $media = Media::factory()->create(['title' => 'Blue Lock', 'genre' => 'Sports']);

        $this->patchJson("/api/media/{$media->id}", ['title' => 'Blue Lock', 'genre' => 'Drama'])
            ->assertOk()
            ->assertJsonPath('data.genre', 'Drama');

        $this->assertDatabaseHas('media', ['id' => $media->id, 'genre' => 'Drama']);
    }

    public function test_update_rejects_title_of_another_media_and_type_change(): void
    {
        Media::factory()->create(['title' => 'Blue Lock']);
        $other = Media::factory()->create(['title' => 'Haikyuu']);

        $this->patchJson("/api/media/{$other->id}", ['title' => 'Blue Lock'])
            ->assertStatus(422)->assertJsonValidationErrors(['title']);

        $this->patchJson("/api/media/{$other->id}", ['type' => 'manga'])
            ->assertStatus(422)->assertJsonValidationErrors(['type']);

        $this->patchJson("/api/media/{$other->id}", ['chapters' => 5]) // $other adalah anime
            ->assertStatus(422)->assertJsonValidationErrors(['chapters']);
    }

    public function test_destroy_deletes_media_and_its_pivot_rows(): void
    {
        $media = Media::factory()->create();
        $user = User::factory()->create();
        $user->collection()->attach($media);

        $this->deleteJson("/api/media/{$media->id}")->assertOk();

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        $this->assertDatabaseMissing('media_user', ['media_id' => $media->id]);
        $this->deleteJson("/api/media/{$media->id}")->assertNotFound();
    }
}
