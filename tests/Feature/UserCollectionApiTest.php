<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCollectionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_user(): void
    {
        $this->postJson('/api/users', ['name' => 'Citra', 'email' => 'citra@flipplay.test', 'password' => 'rahasia123'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Citra')
            ->assertJsonMissingPath('data.password');

        $this->postJson('/api/users', ['name' => 'Citra 2', 'email' => 'citra@flipplay.test', 'password' => 'rahasia123'])
            ->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_can_add_media_to_user_collection(): void
    {
        $user = User::factory()->create();
        $media = Media::factory()->create();

        $this->postJson("/api/users/{$user->id}/collection", ['media_id' => $media->id])
            ->assertCreated()
            ->assertJsonPath('data.id', $media->id)
            ->assertJsonStructure(['data' => ['added_to_collection_at'], 'message']);

        $this->assertDatabaseHas('media_user', ['user_id' => $user->id, 'media_id' => $media->id]);
    }

    public function test_adding_same_media_twice_returns_409_and_keeps_single_row(): void
    {
        $user = User::factory()->create();
        $media = Media::factory()->create();

        $this->postJson("/api/users/{$user->id}/collection", ['media_id' => $media->id])->assertCreated();

        $this->postJson("/api/users/{$user->id}/collection", ['media_id' => $media->id])
            ->assertStatus(409)
            ->assertJson(['message' => 'Media sudah ada di koleksi pengguna ini.']);

        $this->assertDatabaseCount('media_user', 1);
    }

    public function test_same_media_can_be_in_different_users_collections(): void
    {
        [$ani, $budi] = User::factory()->count(2)->create();
        $media = Media::factory()->create();

        $this->postJson("/api/users/{$ani->id}/collection", ['media_id' => $media->id])->assertCreated();
        $this->postJson("/api/users/{$budi->id}/collection", ['media_id' => $media->id])->assertCreated();

        $this->assertDatabaseCount('media_user', 2);
    }

    public function test_add_validates_media_id_and_user(): void
    {
        $user = User::factory()->create();

        $this->postJson("/api/users/{$user->id}/collection", [])
            ->assertStatus(422)->assertJsonValidationErrors(['media_id']);

        $this->postJson("/api/users/{$user->id}/collection", ['media_id' => 999])
            ->assertStatus(422)->assertJsonValidationErrors(['media_id']);

        $this->postJson('/api/users/999/collection', ['media_id' => 1])->assertNotFound();
    }

    public function test_can_list_and_search_user_collection(): void
    {
        $user = User::factory()->create();
        $a = Media::factory()->create(['title' => 'Jujutsu Kaisen']);
        $b = Media::factory()->create(['title' => 'Blue Lock']);
        Media::factory()->create(['title' => 'Tidak Dikoleksi']);
        $user->collection()->attach([$a->id, $b->id]);

        $this->getJson("/api/users/{$user->id}/collection")
            ->assertOk()->assertJsonCount(2, 'data');

        $this->getJson("/api/users/{$user->id}/collection?search=blue")
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Blue Lock');
    }

    public function test_user_detail_shows_collection(): void
    {
        $user = User::factory()->create();
        $user->collection()->attach(Media::factory()->create(['title' => 'Blue Lock']));

        $this->getJson("/api/users/{$user->id}")
            ->assertOk()
            ->assertJsonPath('data.collection_count', 1)
            ->assertJsonPath('data.collection.0.title', 'Blue Lock');
    }

    public function test_can_remove_media_from_collection(): void
    {
        $user = User::factory()->create();
        $media = Media::factory()->create();
        $user->collection()->attach($media);

        $this->deleteJson("/api/users/{$user->id}/collection/{$media->id}")->assertOk();

        $this->assertDatabaseMissing('media_user', ['user_id' => $user->id, 'media_id' => $media->id]);
        $this->assertDatabaseHas('media', ['id' => $media->id]); // media-nya sendiri tidak ikut terhapus
    }

    public function test_removing_media_not_in_collection_returns_404(): void
    {
        $user = User::factory()->create();
        $media = Media::factory()->create();

        $this->deleteJson("/api/users/{$user->id}/collection/{$media->id}")
            ->assertNotFound()
            ->assertJson(['message' => 'Media tidak ada di koleksi pengguna ini.']);
    }
}
