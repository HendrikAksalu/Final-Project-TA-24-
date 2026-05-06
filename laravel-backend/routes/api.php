<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\AlbumShareController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MemoryController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/albums', [AlbumController::class, 'index']);
    Route::post('/albums', [AlbumController::class, 'store']);
    Route::get('/albums/{album}', [AlbumController::class, 'show']);
    Route::patch('/albums/{album}', [AlbumController::class, 'update']);
    Route::delete('/albums/{album}', [AlbumController::class, 'destroy']);

    Route::get('/albums/{album}/collaborators', [AlbumShareController::class, 'index']);
    Route::post('/albums/{album}/share', [AlbumShareController::class, 'store']);
    Route::delete('/albums/{album}/share/{user}', [AlbumShareController::class, 'destroy']);

    Route::get('/albums/{album}/memories', [MemoryController::class, 'index']);
    Route::post('/albums/{album}/memories', [MemoryController::class, 'store']);
    Route::patch('/memories/{memory}', [MemoryController::class, 'update']);
    Route::delete('/memories/{memory}', [MemoryController::class, 'destroy']);
});
