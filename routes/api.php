<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameSearchController;

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

Route::get('/friends', function () {
    return response()->json([
        ['name' => 'Lin'],
        ['name' => 'JaniraLaPrimera'],
        ['name' => 'Jagger'],
    ]);
});

Route::get('/search-games', [GameSearchController::class, 'search']);
