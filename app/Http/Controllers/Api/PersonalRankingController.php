<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;
use App\Models\GameRating;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PersonalRankingController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $userId = $user ? $user->id : null;

        $games = Game::leftJoin('game_ratings as gr_global', 'games.id', '=', 'gr_global.game_id')
            ->leftJoin('game_ratings as gr_user', function ($join) use ($userId) {
                $join->on('games.id', '=', 'gr_user.game_id')
                    ->where('gr_user.user_id', '=', $userId);
            })
            ->select(
                'games.id',
                'games.name',
                'games.slug',
                'games.background_image',
                'games.platform',
                'games.released',
                DB::raw('AVG(gr_global.rating) as average_rating_global'),
                DB::raw('MAX(gr_user.rating) as user_rating')
            )
            ->groupBy('games.id', 'games.name', 'games.slug', 'games.background_image', 'games.platform', 'games.released')
            ->havingRaw('COUNT(gr_global.rating) > 0')
            ->orderByDesc('average_rating_global')
            ->get()
            ->map(function ($game) {
                $game->platform = $game->platform ?: 'Desconegudes';
                $game->released = $game->released ?: 'Per anunciar';
                return $game;
            });

        return response()->json($games);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'game_id' => 'required|exists:games,id',
            'rating' => 'required|numeric|min:0|max:5',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'No autenticat'], 401);
        }

        $rating = GameRating::updateOrCreate(
            [
                'user_id' => $user->id,
                'game_id' => $validated['game_id']
            ],
            [
                'rating' => $validated['rating']
            ]
        );

        return response()->json([
            'message' => 'Valoració guardada correctament',
            'data' => $rating
        ]);
    }
}
