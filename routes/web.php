<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Api\GameListController;
use App\Http\Controllers\GameSearchController;
use App\Http\Controllers\UserSearchController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\Api\PersonalRankingController;
use App\Http\Controllers\ForumController;
use App\Models\ForumThread;
use Illuminate\Http\Request;
use App\Models\Game;
use App\Models\GameRating;

/*
|----------------------------------------------------------------------
| Web Routes
|----------------------------------------------------------------------
|
| Aquí es donde puedes registrar las rutas web para tu aplicación.
| Estas rutas son cargadas por el RouteServiceProvider y están 
| dentro del grupo de middleware "web".
|
*/

// Ruta principal de bienvenida
Route::get('/', function () {
    $thread = ForumThread::with('posts.user')->where('title', 'General')->first();
    return view('welcome', compact('thread'));
});


// Ruta del dashboard (solo accesible para usuarios autenticados)
Route::get('/dashboard', function () {
    return view('llistes');
})->middleware(['auth'])->name('dashboard');


// Rutas protegidas por middleware de autenticación
Route::middleware('auth')->group(function () {

    // Ruta para mostrar el perfil
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

    // Ruta para mostrar el formulario de edición del perfil
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');

    // Ruta para actualizar el perfil
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Ruta para eliminar el perfil
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Ruta para la vista de amigos (debería cargar `pages.friends`), protegida por autenticación
    Route::view('/friends', 'pages.friends')->name('friends');
    Route::get('/api/friends', [FriendController::class, 'index'])->name('friends.list');

    // Rutas de solicitudes de amigos (envío, aceptación, rechazo)
    Route::post('/friends/send/{receiverId}', [FriendController::class, 'sendRequest'])->name('friends.send');
    Route::post('/friends/accept/{senderId}', [FriendController::class, 'acceptRequest'])->name('friends.accept');
    Route::post('/friends/decline/{senderId}', [FriendController::class, 'declineRequest'])->name('friends.decline');

    // Otras vistas protegidas por autenticación
    Route::view('/recomanacions', 'pages.recomanacions')->name('recomanacions');
    Route::view('/ranking', 'pages.ranking')->name('ranking');
});


// Rutas SSO con Github para autenticación (login con Github)
Route::get('auth/github', function () {
    return Socialite::driver('github')->redirect();
});

Route::get('auth/github/callback', function () {
    $githubUser = Socialite::driver('github')->user();

    // Buscar o crear el usuario basado en el email de Github
    $user = User::updateOrCreate(
        ['email' => $githubUser->getEmail()],
        [
            'name' => $githubUser->getName() ?? $githubUser->getNickname(),
            'email' => $githubUser->getEmail(),
            'github_id' => $githubUser->getId(),
            'avatar' => $githubUser->getAvatar(),
        ]
    );

    Auth::login($user);

    return redirect('/dashboard');
});


Route::get('/api/ranking', function () {
    $response = Http::get('https://api.rawg.io/api/games', [
        'key' => 'a6932e9255e64cf98bfa75abde510c5d',
        'ordering' => '-rating',
        'page_size' => 6,
    ]);

    $games = $response->json()['results'];

    // Omitir el primer juego
    $gamesSinPrimero = array_slice($games, 1);

    return response()->json($gamesSinPrimero);
});


//  Nuevas recomendaciones
Route::get('/api/recommendations', function () {
    $user = Auth::user();
    if (!$user) return response()->json([], 401);

    $RAWG_API_KEY = 'a6932e9255e64cf98bfa75abde510c5d';

    // 1. Obtener los juegos valorados >= 4 en game_ratings
    $likedGameRatings = GameRating::where('user_id', $user->id)
        ->where('rating', '>=', 4)
        ->get();

    // Traemos los juegos completos para acceder al id, nombre, etc
    $likedGames = Game::whereIn('id', $likedGameRatings->pluck('game_id'))->get();

    // Juegos que ya tiene el usuario para excluirlos de las recomendaciones
    $excludedIds = GameRating::where('user_id', $user->id)->pluck('game_id')->toArray();

    $recommendations = [];
    $addedIds = [];

    // 2. Recomendaciones similares a juegos favoritos (máx 3 juegos base)
    foreach ($likedGames->shuffle()->take(3) as $liked) {
        $res = Http::get("https://api.rawg.io/api/games/{$liked->id}/suggested", [
            'key' => $RAWG_API_KEY,
            'page_size' => 5,
        ]);

        $suggested = $res->json()['results'] ?? [];

        foreach ($suggested as $game) {
            if (in_array($game['id'], $excludedIds) || in_array($game['id'], $addedIds)) continue;

            $recommendations[] = [
                'sender' => "🧠 Perquè t'agrada *{$liked->name}*",
                'game' => $game['name'],
            ];
            $addedIds[] = $game['id'];

            if (count($recommendations) >= 10) break 2;
        }
    }

    // 3. Relleno: géneros favoritos + top
    if (count($recommendations) < 10) {
        $genreCounts = [];

        foreach ($likedGames as $game) {
            $res = Http::get("https://api.rawg.io/api/games/{$game->id}", [
                'key' => $RAWG_API_KEY,
            ]);

            $genres = $res->json()['genres'] ?? [];
            foreach ($genres as $g) {
                $slug = $g['slug'];
                $genreCounts[$slug] = ($genreCounts[$slug] ?? 0) + 1;
            }
        }

        arsort($genreCounts);
        $topGenres = array_keys(array_slice($genreCounts, 0, 2));

        foreach ($topGenres as $genre) {
            $res = Http::get('https://api.rawg.io/api/games', [
                'key' => $RAWG_API_KEY,
                'genres' => $genre,
                'ordering' => '-rating',
                'page_size' => 10,
            ]);

            $games = $res->json()['results'] ?? [];

            foreach ($games as $game) {
                if (in_array($game['id'], $excludedIds) || in_array($game['id'], $addedIds)) continue;

                $recommendations[] = [
                    'sender' => "🔥 Top del gènere *$genre*",
                    'game' => $game['name'],
                ];
                $addedIds[] = $game['id'];

                if (count($recommendations) >= 10) break 2;
            }
        }
    }

    // 4. Último recurso: tendencias
    if (count($recommendations) < 10) {
        $res = Http::get('https://api.rawg.io/api/games', [
            'key' => $RAWG_API_KEY,
            'ordering' => '-added',
            'page_size' => 10,
        ]);

        $games = $res->json()['results'] ?? [];

        foreach ($games as $game) {
            if (in_array($game['id'], $excludedIds) || in_array($game['id'], $addedIds)) continue;

            $recommendations[] = [
                'sender' => '📈 Tendència actual',
                'game' => $game['name'],
            ];
            $addedIds[] = $game['id'];

            if (count($recommendations) >= 10) break;
        }
    }

    return response()->json($recommendations);
});


// 📩 Ruta para mostrar y enviar el formulario de contacto
Route::get('/contacte', [ContactController::class, 'create'])->name('contacte');
Route::post('/contacte', [ContactController::class, 'store']);


// 🔒 API REST para gestionar la lista de juegos del usuario autenticado
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/game-list', [GameListController::class, 'index']);
    Route::post('/game-list', [GameListController::class, 'addGame']);
    Route::delete('/game-list/{id}', [GameListController::class, 'removeGame']);
});


// 🔍 Ruta que busca juegos desde RAWG según el término del usuario (AJAX)
Route::get('/search-games', [GameSearchController::class, 'search']);


// Política de privacidad (provisional)
Route::get('/privacy-policy', function () {
    return response('<h1>Política de Privacitat</h1><p>Contingut provisional de la política de privacitat.</p>', 200)
        ->header('Content-Type', 'text/html');
})->name('privacy-policy');


// Condiciones de uso (provisional)
Route::get('/terms-of-service', function () {
    return response('<h1>Condicions d\'ús</h1><p>Contingut provisional de les condicions d\'ús.</p>', 200)
        ->header('Content-Type', 'text/html');
})->name('terms-of-service');


// Ruta para eliminar juegos de las listas
Route::delete('/game-list/{id}', [GameListController::class, 'destroy'])->name('game-list.destroy');


// Ruta de los estados del juego
Route::put('/game-list/{gameId}/status', [GameListController::class, 'updateStatus']);


// En routes/web.php o routes/api.php
Route::put('/game-list/{gameId}/comment', [GameListController::class, 'updateComment']);


// routes/web.php o routes/api.php
Route::middleware(['auth'])->group(function () {
    Route::get('/users/search', [UserSearchController::class, 'search'])->name('users.search');
});


//  Ruta para enviar solicitud de amistad a la gente
Route::middleware('auth:sanctum')->post('/friends/request', [FriendController::class, 'sendRequest']);


//  Rutas para recibir solicitudes de amistad
Route::get('/friends/requests', [FriendController::class, 'receivedRequests']);
Route::post('/friends/accept/{senderId}', [FriendController::class, 'acceptRequest']);
Route::post('/friends/decline/{senderId}', [FriendController::class, 'declineRequest']);

//  Ruta para borrar amigos
Route::post('/friends/remove/{user}', [FriendController::class, 'removeFriend']);

// Ruta para la API de ranking personal
Route::get('/api/personal-ranking', [PersonalRankingController::class, 'index']);
Route::post('/api/personal-ranking', [PersonalRankingController::class, 'store']);


//  Ruta del chat
Route::middleware(['auth'])->group(function () {
    Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
    Route::post('/forum/thread/{thread}/post', [ForumController::class, 'storePost'])->name('forum.post');
});


// Mostrar los posts de un hilo en JSON (para AJAX)
Route::get('/forum/{thread}/posts', [ForumController::class, 'getPosts']);


// Guardar post nuevo (ya tienes storePost, solo que debe aceptar JSON)
Route::post('/forum/{thread}/posts', [ForumController::class, 'storePost'])->middleware('auth');

//  Conseguir ruta de la api de RAWG
Route::get('/api/rawg/details/{id}', [\App\Http\Controllers\RawgController::class, 'getGameDetails']);

//  Ruta para los comentarios
Route::post('/test-update', function (Request $request) {
    return response()->json(['success' => true]);
});

// Cargar rutas adicionales de autenticación (como las de login)
require __DIR__ . '/auth.php';
