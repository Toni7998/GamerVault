<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GameList;
use Illuminate\Support\Facades\Auth;

class GameListController extends Controller
{

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
            'game_id' => 'required|exists:games,id',
        ]);

        $list = GameList::where('user_id', Auth::id())->first();

        if (!$list) {
            return response()->json(['error' => 'No tens cap llista'], 404);
        }

        $list->games()->syncWithoutDetaching([$request->game_id]);

        return response()->json(['message' => 'Joc afegit correctament']);
    }

    public function removeGame(Request $request)
    {
        $request->validate([
            'game_id' => 'required|exists:games,id',
        ]);

        $list = GameList::where('user_id', Auth::id())->first();

        if (!$list) {
            return response()->json(['error' => 'No tens cap llista'], 404);
        }

        $list->games()->detach($request->game_id);

        return response()->json(['message' => 'Joc eliminat correctament']);
    }
}
