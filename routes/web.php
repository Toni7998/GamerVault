<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Api\GameListController;

/*
|---------------------------------------------------------------------------
| Web Routes
|---------------------------------------------------------------------------
|
| Aquí es donde puedes registrar las rutas web para tu aplicación.
| Estas rutas son cargadas por el RouteServiceProvider y están 
| dentro del grupo de middleware "web".
|
*/

// Ruta principal de bienvenida
Route::get('/', function () {
    return view('welcome');
});

// Ruta del dashboard (usando la Opción 1, pasando el usuario directamente desde la ruta)
Route::get('/dashboard', function () {
    return view('llistes');
})->middleware(['auth'])->name('dashboard');


// Rutas de perfil, protegidas por middleware de autenticación
Route::middleware('auth')->group(function () {
    // Ruta para mostrar el perfil
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');  // Esta línea es nueva

    // Ruta para mostrar el formulario de edición
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');

    // Ruta para actualizar el perfil
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Ruta para eliminar el perfil
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

//  Rutas SSO Github
Route::get('auth/github', function () {
    return Socialite::driver('github')->redirect();
});

Route::get('auth/github/callback', function () {
    $githubUser = Socialite::driver('github')->user();

    // Buscar usuario por email o crear uno nuevo
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

Route::view('/friends', 'pages.friends')->middleware(['auth'])->name('friends');
Route::view('/recomanacions', 'pages.recomanacions')->middleware(['auth'])->name('recomanacions');
Route::view('/ranking', 'pages.ranking')->middleware(['auth'])->name('ranking');


Route::get('/api/ranking', function () {
    $response = Http::get('https://api.rawg.io/api/games', [
        'key' => 'a6932e9255e64cf98bfa75abde510c5d',
        'ordering' => '-rating',
        'page_size' => 10,
    ]);

    $games = $response->json()['results'];

    return response()->json($games);
});

Route::get('/api/recommendations', function () {
    $day = now()->dayOfWeek;

    // Criteris d’ordenació diferents segons el dia
    $orderingOptions = [
        '-rating',      // Diumenge
        '-added',       // Dilluns
        '-released',    // Dimarts
        '-updated',     // Dimecres
        'name',         // Dijous
        '-metacritic',  // Divendres
        'released'      // Dissabte
    ];

    // Noms bonics en català segons el dia
    $dayNames = [
        '✨ Diumenges de clàssics',
        '🚀 Dilluns futuristes',
        '🔫 Dimarts d\'acció',
        '🧠 Dimecres estratègics',
        '🎨 Dijous creatius',
        '🏆 Divendres top',
        '🎮 Dissabtes d\'aventures'
    ];

    $ordering = $orderingOptions[$day];
    $sender = $dayNames[$day];

    $response = Http::get('https://api.rawg.io/api/games', [
        'key' => 'a6932e9255e64cf98bfa75abde510c5d',
        'ordering' => $ordering,
        'page_size' => 5,
    ]);

    $games = $response->json()['results'] ?? [];

    $recommendations = [];
    foreach ($games as $game) {
        $recommendations[] = [
            'sender' => $sender,
            'game' => $game['name'],
        ];
    }

    return response()->json($recommendations);
});

Route::get('/contacte', [ContactController::class, 'create'])->name('contacte');
Route::post('/contacte', [ContactController::class, 'store']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/game-list', [GameListController::class, 'index']);
    Route::post('/game-list', [GameListController::class, 'addGame']);
    Route::delete('/game-list/{id}', [GameListController::class, 'removeGame']);
});



// Cargar rutas adicionales de autenticación
require __DIR__ . '/auth.php';