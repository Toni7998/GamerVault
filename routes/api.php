<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameSearchController;
use App\Http\Controllers\FriendController;

Route::get('/lists', function () {
    return response()->json([
        ['name' => 'Jocs Favorits'],
        ['name' => 'Per jugar'],
        ['name' => 'Acabats']
    ]);
});

Route::post('/lists', function () {
    return response()->json(['success' => true]);
});

Route::get('/recommendations', function () {
    return response()->json([
        ['sender' => 'Roger', 'game' => 'Elden Ring'],
        ['sender' => 'Deme', 'game' => 'Hollow Knight'],
    ]);
});

Route::middleware('auth:sanctum')->get('/friends', [FriendController::class, 'index']);

Route::get('/search-games', [GameSearchController::class, 'search']);
