<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameList;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class GameListController extends Controller
{
    // Muestra la lista de juegos del usuario, crea una nueva lista si no existe.
    public function index()
    {
        $user = Auth::user();

        // Buscar la lista del usuario
        $gameList = GameList::with('games')->where('user_id', $user->id)->first();

        // Si no existe, la crea
        if (!$gameList) {
            $gameList = GameList::create([
                'user_id' => $user->id,
                'name' => 'La meva llista',
            ]);
        }

        // Cargar relaciones si no se cargaron antes
        $gameList->load('games');

        return response()->json($gameList);
    }

    public function addGame(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|min:1',
            'title' => 'required|string',
            'background_image' => 'nullable|string'
        ]);

        $user = Auth::user();
        $gameList = GameList::where('user_id', $user->id)->first();

        if (!$gameList) {
            return response()->json(['error' => 'No tens cap llista'], 404);
        }

        // Buscar o crear el juego en la base de datos local
        $game = Game::firstOrCreate(
            ['id' => $request->input('id')],
            [
                'name' => $request->input('title'),
                'background_image' => $request->input('background_image'),
                'released' => $request->input('released'),
                'platform' => $request->input('platform'),
                'completed' => false,
            ]
        );



        // Agregar el juego a la lista si no está ya
        if (!$gameList->games->contains($game->id)) {
            $gameList->games()->attach($game->id);
        }

        return response()->json([
            'message' => 'Joc afegit correctament',
            'game' => $game
        ]);
    }


    // Eliminar un juego de la lista del usuario
    public function removeGame(Request $request)
    {
        $request->validate([
            'game_id' => 'required|exists:games,id',
        ]);

        $user = Auth::user();
        $gameId = $request->input('game_id');

        $gameList = GameList::where('user_id', $user->id)->first();

        if (!$gameList) {
            return response()->json(['error' => 'No tens cap llista'], 404);
        }

        if (!$gameList->games->contains($gameId)) {
            return response()->json(['error' => 'Aquest joc no és a la teva llista'], 400);
        }

        $gameList->games()->detach($gameId);

        return response()->json(['message' => 'Joc eliminat correctament de la teva llista']);
    }


    public function destroy($id)
    {
        $user = Auth::user();
        $gameList = GameList::where('user_id', $user->id)->first();

        if (!$gameList) {
            return response()->json(['error' => 'No tens cap llista'], 404);
        }

        if (!$gameList->games()->where('games.id', $id)->exists()) {
            return response()->json(['error' => 'Aquest joc no és a la teva llista'], 400);
        }

        $gameList->games()->detach($id);

        return response()->json(['message' => 'Joc eliminat correctament de la teva llista']);
    }


    public function updateStatus($gameId, Request $request)
    {
        $game = Game::find($gameId);

        if (!$game) {
            return response()->json(['message' => 'Game not found'], 404);
        }

        $game->status = $request->status;
        $game->save();

        return response()->json($game);
    }

    // Para guardar comentarios
    public function updateComment($gameId, Request $request)
    {
        $game = Game::find($gameId);

        if (!$game) {
            return response()->json(['message' => 'Game not found'], 404);
        }

        $game->comment = $request->input('comment');
        $game->save();

        return response()->json($game);
    }

    // Función para obtener el juego desde la API de RAWG
    private function getGameFromRawg($gameId)
    {
        $apiKey = 'a6932e9255e64cf98bfa75abde510c5d';
        $url = "https://api.rawg.io/api/games/{$gameId}?key={$apiKey}";

        $response = Http::get($url);

        if ($response->failed()) {
            return null; // Si la API no responde correctamente, retorna null
        }

        $gameData = $response->json();

        // Verifica si el juego está disponible en la respuesta
        return $gameData ?? null;
    }
}
