<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCollectionRequest;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Custom action di luar CRUD standar: mengubah relasi many-to-many
 * antara User dan Media lewat pivot table media_user.
 */
class UserCollectionController extends Controller
{
    /**
     * GET /api/users/{user}/collection?search=
     */
    public function index(Request $request, User $user): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $media = $user->collection()
            ->searchTitle($filters['search'] ?? null)
            ->orderByDesc('media_user.created_at') // terbaru ditambahkan di atas
            ->paginate(10)
            ->withQueryString();

        return MediaResource::collection($media);
    }

    /**
     * POST /api/users/{user}/collection   body: { "media_id": 1 }
     */
    public function store(AddToCollectionRequest $request, User $user): JsonResponse
    {
        $mediaId = (int) $request->validated('media_id');

        if ($user->collection()->wherePivot('media_id', $mediaId)->exists()) {
            return $this->alreadyInCollection();
        }

        try {
            $user->collection()->attach($mediaId);
        } catch (UniqueConstraintViolationException) {
            // Dua request bersamaan: unique index di pivot table jadi pengaman terakhir.
            return $this->alreadyInCollection();
        }

        // Ambil ulang lewat relasi supaya data pivot (added_to_collection_at) ikut tampil.
        $media = $user->collection()->wherePivot('media_id', $mediaId)->firstOrFail();

        return MediaResource::make($media)
            ->additional(['message' => 'Media berhasil ditambahkan ke koleksi.'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * DELETE /api/users/{user}/collection/{media}
     */
    public function destroy(User $user, Media $media): JsonResponse
    {
        if (! $user->collection()->wherePivot('media_id', $media->getKey())->exists()) {
            return response()->json([
                'message' => 'Media tidak ada di koleksi pengguna ini.',
            ], 404);
        }

        $user->collection()->detach($media->getKey());

        return response()->json(['message' => 'Media berhasil dihapus dari koleksi.']);
    }

    private function alreadyInCollection(): JsonResponse
    {
        return response()->json([
            'message' => 'Media sudah ada di koleksi pengguna ini.',
        ], 409);
    }
}
