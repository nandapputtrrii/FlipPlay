<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMediaRequest;
use App\Http\Requests\UpdateMediaRequest;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class MediaController extends Controller
{
    /**
     * GET /api/media?search=&type=&genre=&per_page=
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::in(Media::TYPES)],
            'genre' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);

        $media = Media::query()
            ->searchTitle($filters['search'] ?? null)
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($filters['genre'] ?? null, fn ($q, $genre) => $q->where('genre', $genre))
            ->withCount('users')
            ->orderBy('title')
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();

        return MediaResource::collection($media);
    }

    /**
     * GET /api/media/{media} - detail media + pengguna yang memasukkannya ke koleksi.
     */
    public function show(Media $media): MediaResource
    {
        $media->load('users')->loadCount('users');

        return MediaResource::make($media);
    }

    /**
     * POST /api/media
     */
    public function store(StoreMediaRequest $request): JsonResponse
    {
        $media = Media::create($request->validated());

        return MediaResource::make($media)
            ->response()
            ->setStatusCode(201);
    }

    /**
     * PUT|PATCH /api/media/{media}
     */
    public function update(UpdateMediaRequest $request, Media $media): MediaResource
    {
        $media->update($request->validated());

        return MediaResource::make($media);
    }

    /**
     * DELETE /api/media/{media} - baris pivot ikut terhapus (cascadeOnDelete).
     */
    public function destroy(Media $media): JsonResponse
    {
        $media->delete();

        return response()->json(['message' => 'Media berhasil dihapus.']);
    }
}
