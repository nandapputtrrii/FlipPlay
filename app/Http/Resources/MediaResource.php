<?php

namespace App\Http\Resources;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Media
 */
class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'genre' => $this->genre,
            'release_year' => $this->release_year,

            // Anime punya episodes, manga punya chapters.
            'episodes' => $this->when($this->type === Media::TYPE_ANIME, $this->episodes),
            'chapters' => $this->when($this->type === Media::TYPE_MANGA, $this->chapters),
            'details' => $this->details,

            // Hanya muncul saat media diambil lewat relasi user->collection (data pivot).
            'added_to_collection_at' => $this->whenPivotLoaded('media_user', fn () => $this->pivot->created_at),

            // Hanya muncul bila relasi/count-nya sudah di-load oleh controller.
            'users_count' => $this->whenCounted('users'),
            'users' => UserResource::collection($this->whenLoaded('users')),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
