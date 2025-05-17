<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class RawgController extends Controller
{
    public function getGameDetails($id)
    {
        try {
            $apiKey = env('RAWG_API_KEY');
            $response = Http::get("https://api.rawg.io/api/games/{$id}?key={$apiKey}");

            if ($response->failed()) {
                return response()->json(['error' => 'No s\'han pogut obtenir detalls del joc.'], 404);
            }

            $data = $response->json();

            return [
                'id' => $data['id'],
                'name' => $data['name'],
                'slug' => $data['slug'] ?? null,
                'released' => $data['released'] ?? null,
                'background_image' => $data['background_image'] ?? null,
                'platforms' => collect($data['platforms'] ?? [])->pluck('platform.name'),
                'website_url' => $data['website'] ?? null,
            ];
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error intern del servidor: ' . $e->getMessage()
            ], 500);
        }
    }
}
