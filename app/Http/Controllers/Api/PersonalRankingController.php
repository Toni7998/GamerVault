<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;
use App\Models\GameRating;
use Illuminate\Support\Facades\Auth;

class PersonalRankingController extends Controller
{
    public function index()
    {
        // Obtener juegos con la valoración promedio
        $games = Game::withAvg('ratings', 'rating') // Obtener juegos con la valoración promedio
            ->join('game_ratings', 'games.id', '=', 'game_ratings.game_id') // Asegurarse de que el juego tiene valoraciones
            ->select('games.id', 'games.name', 'games.slug', 'games.background_image', 'games.platform', 'games.released') // Asegúrate de seleccionar 'slug'
            ->get()
            ->sortByDesc('ratings_avg_rating'); // Ordenar por promedio de valoración descendente

        // Modificar los campos de plataforma y fecha de lanzamiento
        $games = $games->map(function ($game) {
            // Asignar valor por defecto si no está presente
            $game->platform = $game->platform ?: 'Desconegudes';
            $game->released = $game->released ? $game->released : 'Per anunciar';
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

        // Asegúrate de que el usuario esté autenticado
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'No autenticat'], 401);
        }

        // Guardar o actualizar valoración
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
