<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,

            // Hanya muncul saat user diambil lewat relasi media->users (data pivot).
            'added_to_collection_at' => $this->whenPivotLoaded('media_user', fn () => $this->pivot->created_at),

            // Hanya muncul bila relasi/count-nya sudah di-load oleh controller.
            'collection_count' => $this->whenCounted('collection'),
            'collection' => MediaResource::collection($this->whenLoaded('collection')),

            'created_at' => $this->created_at,
        ];
    }
}
