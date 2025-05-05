<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GameSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (!$query || strlen($query) < 3) {
            return response()->json([]);
        }

        $response = Http::get('https://api.rawg.io/api/games', [
            'key' => env('RAWG_API_KEY'),
            'search' => $query,
            'page_size' => 10,
        ]);

        if (!$response->successful()) {
            return response()->json([], 500);
        }

        return response()->json($response->json()['results'] ?? []);
    }
}
