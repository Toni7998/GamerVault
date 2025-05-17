<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserGame;
use Illuminate\Support\Facades\Auth;

class UserGameController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $games = $user->userGames()->with('game')->get()->map(function ($userGame) {
            return [
                'id' => $userGame->game->id,
                'name' => $userGame->game->name,
                'background_image' => $userGame->game->background_image,
                'comment' => $userGame->comment,
                'rating' => $userGame->rating,
                'status' => $userGame->status,
                'times_finished' => $userGame->times_finished,
            ];
        });

        return response()->json([
            'name' => $user->name,
            'games' => $games
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'game_id' => 'required|exists:games,id',
            'comment' => 'nullable|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'status' => 'nullable|string',
            'times_finished' => 'nullable|integer|min:0',
        ]);

        $userId = Auth::id();

        $userGame = UserGame::updateOrCreate(
            ['user_id' => $userId, 'game_id' => $request->game_id],
            [
                'comment' => $request->comment,
                'rating' => $request->rating,
                'status' => $request->status,
                'times_finished' => $request->times_finished,
            ]
        );

        return response()->json($userGame);
    }

    public function updateComment(Request $request)
    {
        $request->validate([
            'game_id' => 'required|integer|exists:games,id',
            'comment' => 'nullable|string',
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $userGame = UserGame::where('user_id', $user->id)
            ->where('game_id', $request->game_id)
            ->first();

        if (!$userGame) {
            return response()->json(['error' => 'Relación no encontrada'], 404);
        }

        $userGame->comment = $request->comment;
        $userGame->save();

        return response()->json(['success' => true, 'comment' => $userGame->comment]);
    }
    
}
