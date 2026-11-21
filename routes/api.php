<?php

use App\Http\Controllers\MediaController;
use App\Http\Controllers\UserCollectionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| FlipPlay API Routes  (prefix otomatis: /api)
|--------------------------------------------------------------------------
*/

// CRUD Media: index, show, store, update (PUT/PATCH), destroy.
// ->parameters() wajib: Laravel menyingularkan "media" jadi "medium",
// sehingga route model binding Media $media tidak akan cocok tanpa ini.
Route::apiResource('media', MediaController::class)
    ->parameters(['media' => 'media']);

// Pengguna
Route::get('users', [UserController::class, 'index'])->name('users.index');
Route::post('users', [UserController::class, 'store'])->name('users.store');
Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');

// Koleksi media milik pengguna (custom action: mengubah relasi many-to-many)
Route::get('users/{user}/collection', [UserCollectionController::class, 'index'])
    ->name('users.collection.index');
Route::post('users/{user}/collection', [UserCollectionController::class, 'store'])
    ->name('users.collection.store');
Route::delete('users/{user}/collection/{media}', [UserCollectionController::class, 'destroy'])
    ->name('users.collection.destroy');
