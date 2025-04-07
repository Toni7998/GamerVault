<?php

use Illuminate\Support\Facades\Route;
use app\Http\Controllers\API\GameController;
use app\Http\Controllers\API\GameListController;

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

Route::get('/ranking', function () {
    return response()->json([
        ['name' => 'The Witcher 3', 'votes' => 124],
        ['name' => 'God of War', 'votes' => 98],
        ['name' => 'Zelda: BOTW', 'votes' => 87],
    ]);
});

Route::get('/recommendations', function () {
    return response()->json([
        ['sender' => 'Roger', 'game' => 'Elden Ring'],
        ['sender' => 'Deme', 'game' => 'Hollow Knight'],
    ]);
});

Route::get('/friends', function () {
    return response()->json([
        ['name' => 'Lin'],
        ['name' => 'JaniraLaPrimera'],
        ['name' => 'Jagger'],
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('lists', GameListController::class);
    Route::apiResource('games', GameController::class);
});
