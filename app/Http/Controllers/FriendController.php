<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Friendship;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class FriendController extends Controller
{
    /**
     * Get the authenticated user's friends
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // Opción 1: Con type hint específico
        /** @var User $user */
        $user = Auth::user();

        // Obtener amigos con la relación allFriends
        $friends = $user->allFriends()->get(['users.id', 'users.name']);

        return response()->json($friends);
    }

    public function sendRequest(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|not_in:' . Auth::id(),
        ]);

        /** @var User $sender */
        $sender = Auth::user();
        $receiverId = $request->input('user_id');

        // Prevenir duplicados en ambos sentidos (amistades o solicitudes pendientes)
        $exists = Friendship::where(function ($query) use ($sender, $receiverId) {
            $query->where('sender_id', $sender->id)
                ->where('receiver_id', $receiverId)
                ->whereIn('status', ['pending', 'accepted']);  // Comprobamos también si ya es amigo
        })->orWhere(function ($query) use ($sender, $receiverId) {
            $query->where('sender_id', $receiverId)
                ->where('receiver_id', $sender->id)
                ->whereIn('status', ['pending', 'accepted']);  // Verificamos también en dirección contraria
        })->exists();

        if ($exists) {
            return response()->json(['message' => 'Ja hi ha una sol·licitud o amistat.'], 409);
        }

        // Crear solicitud de amistad
        Friendship::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiverId,
            'status' => 'pending'
        ]);

        return response()->json(['message' => 'Sol·licitud enviada']);
    }

    public function acceptRequest($senderId)
    {
        /** @var User $user */
        $user = Auth::user();

        $friendship = Friendship::where('sender_id', $senderId)
            ->where('receiver_id', $user->id)
            ->firstOrFail();

        $friendship->update(['status' => 'accepted']);

        return back()->with('success', 'Solicitud aceptada');
    }

    public function declineRequest($senderId)
    {
        /** @var User $user */
        $user = Auth::user();

        Friendship::where('sender_id', $senderId)
            ->where('receiver_id', $user->id)
            ->delete(); // O usar update(['status' => 'declined'])

        return back()->with('info', 'Solicitud rechazada');
    }

    public function receivedRequests(): JsonResponse
    {
        $user = Auth::user();

        $requests = Friendship::with('sender:id,name')
            ->where('receiver_id', $user->id)
            ->where('status', 'pending')
            ->get();

        return response()->json($requests);
    }

    public function removeFriend(User $user)
    {
        /** @var User $auth */
        $auth = Auth::user();

        Friendship::where(function ($query) use ($auth, $user) {
            $query->where('sender_id', $auth->id)
                ->where('receiver_id', $user->id);
        })->orWhere(function ($query) use ($auth, $user) {
            $query->where('sender_id', $user->id)
                ->where('receiver_id', $auth->id);
        })->delete();

        return response()->json(['message' => 'Amic eliminat']);
    }

    
}
