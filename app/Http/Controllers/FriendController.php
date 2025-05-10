<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Friendship;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

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
        $friends = $user->allFriends()
            ->select('users.id', 'users.name') // Selecciona solo los campos necesarios
            ->get();

        return response()->json($friends);
    }

    public function sendRequest($receiverId)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($receiverId == $user->id) {
            return back()->with('error', 'No puedes agregarte a ti mismo');
        }

        Friendship::firstOrCreate([
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'status' => 'pending' // Asegúrate de incluir el estado inicial
        ]);

        return back()->with('success', 'Solicitud enviada');
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
}
