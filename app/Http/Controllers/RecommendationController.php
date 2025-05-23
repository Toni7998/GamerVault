<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\GameRecommendation;
use App\Models\User;
use App\Models\Game;
use App\Models\Friendship;

class RecommendationController extends Controller
{
    public function recommend(Request $request)
    {
        $request->validate([
            'game_id' => 'required|integer|exists:games,id',
            'friend_id' => 'required|integer|exists:users,id',
        ]);

        $user = Auth::user();

        // Verificar si existe una amistad aceptada en cualquier dirección
        $areFriends = Friendship::where(function ($query) use ($user, $request) {
            $query->where('sender_id', $user->id)
                ->where('receiver_id', $request->friend_id);
        })
            ->orWhere(function ($query) use ($user, $request) {
                $query->where('sender_id', $request->friend_id)
                    ->where('receiver_id', $user->id);
            })
            ->where('status', 'accepted')
            ->exists();

        if (!$areFriends) {
            return response()->json(['message' => 'No pots recomanar a algú que no és amic teu.'], 403);
        }

        $friendHasGame = DB::table('user_games')
            ->where('user_id', $request->friend_id)
            ->where('game_id', $request->game_id)
            ->exists();

        if ($friendHasGame) {
            return response()->json(['message' => 'Aquest amic ja té aquest joc a la seva llista.'], 400);
        }

        GameRecommendation::create([
            'sender_id' => $user->id,
            'receiver_id' => $request->friend_id,
            'game_id' => $request->game_id,
        ]);

        return response()->json(['message' => 'Recomanació enviada correctament.']);
    }



    public function getReceivedRecommendations(Request $request)
    {
        try {
            $user = Auth::user();

            $recommendations = GameRecommendation::with(['sender', 'game'])
                ->where('receiver_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($rec) {
                    return [
                        'id' => $rec->id,
                        'game' => [
                            'id' => $rec->game->id,
                            'name' => $rec->game->name,
                            'background_image' => $rec->game->background_image ?? null
                        ],
                        'sender' => [
                            'id' => $rec->sender->id,
                            'name' => $rec->sender->name
                        ],
                        'created_at' => $rec->created_at->toDateTimeString()
                    ];
                });

            return response()->json($recommendations);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener recomendaciones',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();

        $recommendation = GameRecommendation::where('id', $id)
            ->where('receiver_id', $user->id)  // solo puede borrar quien la recibió
            ->first();

        if (!$recommendation) {
            return response()->json(['message' => 'Recomendación no encontrada'], 404);
        }

        $recommendation->delete();

        return response()->json(['message' => 'Recomendación eliminada correctamente']);
    }
}
