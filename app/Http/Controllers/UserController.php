<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    /**
     * GET /api/users
     */
    public function index(): AnonymousResourceCollection
    {
        return UserResource::collection(
            User::query()->withCount('collection')->orderBy('name')->paginate(10)
        );
    }

    /**
     * GET /api/users/{user} - profil + seluruh koleksi media pengguna.
     */
    public function show(User $user): UserResource
    {
        $user->load('collection')->loadCount('collection');

        return UserResource::make($user);
    }

    /**
     * POST /api/users
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        return UserResource::make($user)
            ->response()
            ->setStatusCode(201);
    }
}
