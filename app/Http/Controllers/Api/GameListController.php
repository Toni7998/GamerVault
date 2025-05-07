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

    // Agregar un juego a la lista del usuario
    public function addGame(Request $request)
    {
        $request->validate([
            'game_id' => 'required|exists:games,id', // Validar si el juego existe en la base de datos
        ]);

        $user = Auth::user();
        $gameId = $request->input('game_id');

        // Buscar la lista del usuario
        $gameList = GameList::where('user_id', $user->id)->first();

        if (!$gameList) {
            return response()->json(['error' => 'No tens cap llista'], 404);
        }

        // Verifica si el juego ya está en la base de datos, si no lo está, lo busca en la API de RAWG
        $game = Game::find($gameId);

        if (!$game) {
            $game = $this->getGameFromRawg($gameId);

            if (!$game) {
                return response()->json(['error' => 'No s\'ha pogut trobar el joc a RAWG'], 404);
            }

            // Crea el juego en la base de datos si no existe
            $game = Game::create([
                'id' => $game['id'],
                'name' => $game['name'],
                'released' => $game['released'] ?? null,
                'background_image' => $game['background_image'] ?? null,
            ]);
        }

        // Agregar el juego a la lista del usuario
        $gameList->games()->syncWithoutDetaching([$game->id]);

        return response()->json(['message' => 'Joc afegit correctament', 'game' => $game]);
    }

    // Eliminar un juego de la lista del usuario
    public function removeGame(Request $request)
    {
        $request->validate([
            'game_id' => 'required|exists:games,id', // Validar si el juego existe
        ]);

        $user = Auth::user();
        $gameId = $request->input('game_id');

        // Buscar la lista del usuario
        $gameList = GameList::where('user_id', $user->id)->first();

        if (!$gameList) {
            return response()->json(['error' => 'No tens cap llista'], 404);
        }

        // Eliminar el juego de la lista
        $gameList->games()->detach($gameId);

        return response()->json(['message' => 'Joc eliminat correctament']);
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
        return $gameData ? $gameData['results'][0] : null;
    }
}
