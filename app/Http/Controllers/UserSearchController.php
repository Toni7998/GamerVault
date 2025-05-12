<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Friendship;

class UserSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->query('q');
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        if (empty($query) || strlen($query) < 2) {
            return response()->json([]);
        }

        // IDs de usuarios con los que ya hay relación
        $excludedIds = Friendship::where(function ($q) use ($user) {
            $q->where('sender_id', $user->id)
                ->orWhere('receiver_id', $user->id);
        })
            ->pluck('sender_id')
            ->merge(Friendship::where('receiver_id', $user->id)->pluck('sender_id'))
            ->push($user->id)
            ->unique()
            ->values();

        $users = User::where('name', 'like', "%$query%")
            ->whereNotIn('id', $excludedIds)
            ->select('id', 'name')
            ->limit(10)
            ->get();

        return response()->json($users);
    }
}
