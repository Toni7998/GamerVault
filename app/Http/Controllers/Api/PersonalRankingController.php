<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;

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
}
