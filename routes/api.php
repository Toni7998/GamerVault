<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameSearchController;
use App\Http\Controllers\FriendController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

Route::middleware('auth')->get('/friends', [FriendController::class, 'index']);

Route::get('/search-games', [GameSearchController::class, 'search']);

use App\Http\Controllers\Api\PersonalRankingController;

Route::get('personal-ranking', [PersonalRankingController::class, 'index']);

Route::post('/api/recommendations/send', function (Request $request) {
    $user = Auth::user();
    if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

    $request->validate([
        'receiver_id' => 'required|exists:users,id',
        'game_id' => 'required|exists:games,id',
    ]);

    $alreadyRecommended = DB::table('game_recommendations')
        ->where('sender_id', $user->id)
        ->where('receiver_id', $request->receiver_id)
        ->where('game_id', $request->game_id)
        ->exists();

    if ($alreadyRecommended) {
        return response()->json(['message' => 'Ya has recomendado este juego a este usuario'], 409);
    }

    DB::table('game_recommendations')->insert([
        'sender_id' => $user->id,
        'receiver_id' => $request->receiver_id,
        'game_id' => $request->game_id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json(['message' => 'Recomendación enviada']);
});

use App\Http\Controllers\RecommendationController;

Route::middleware('auth:sanctum')->post('/recommend-game', [RecommendationController::class, 'recommend']);

// routes/api.php
Route::middleware('auth:sanctum')->get('/recommendations', function (Request $request) {
    $user = $request->user();

    $recommendations = DB::table('game_recommendations')
        ->join('users as senders', 'senders.id', '=', 'game_recommendations.sender_id')
        ->join('games', 'games.id', '=', 'game_recommendations.game_id')
        ->where('game_recommendations.receiver_id', $user->id)
        ->select(
            'game_recommendations.id',
            'senders.id as sender_id',
            'senders.name as sender_name',
            'games.id as game_id',
            'games.name as game_name',
            'games.cover_image_url',
            'game_recommendations.created_at'
        )
        ->orderBy('game_recommendations.created_at', 'desc')
        ->get();

    return response()->json($recommendations);
});
