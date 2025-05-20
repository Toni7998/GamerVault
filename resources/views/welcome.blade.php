<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GamerVault</title>
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
</head>

<body>

    <nav class="navbar bg-gray-800 text-white shadow-md" style="background-color: #1f1f1f;">
        <div class="navbar-inner flex justify-between items-center px-6 py-3">
            <a href="{{ url('/') }}">
                <img src="{{ asset('media/logo-gamer_vault_horizontal.png') }}" alt="GamerVault Logo" class="h-10">
            </a>
            <ul class="navbar-links bg-customPurple flex space-x-6 items-center">
                <li><a href="{{ route('dashboard') }}" class="nav-link text-lg hover:text-yellow-400">📋 Llistes</a></li>
                <li><a href="{{ route('friends') }}" class="nav-link text-lg hover:text-yellow-400">👯 Amics</a></li>
                <li><a href="{{ route('recomanacions') }}" class="nav-link text-lg hover:text-yellow-400">🔎 Recomanacions</a></li>
                <li><a href="{{ route('ranking') }}" class="nav-link text-lg hover:text-yellow-400">🏆 Ranking</a></li>
                <li><a href="{{ route('contacte') }}" class="nav-link text-lg hover:text-yellow-400">📩 Contacte</a></li>
                <li>
                    @auth
                    <form method="POST" action="{{ route('logout') }}" class="inline-form">
                        @csrf
                        <button type="submit" class="link-button text-lg hover:text-red-400">
                            🚪 Tancar Sessió
                        </button>
                    </form>
                    @else
                    <a href="{{ route('login') }}" class="link-button text-lg hover:text-yellow-400">
                        🔑 Inicia Sessió
                    </a>
                    @endauth
                </li>
            </ul>
        </div>
    </nav>

    <main class="container fade-in" style="text-align: center; padding-top: 3rem;">
        <header>
            <h1 class="text-4xl font-bold mb-4">👾 Benvingut a GamerVault</h1>
            <p class="text-lg mb-6">La teva plataforma definitiva per gestionar, descobrir i compartir videojocs.</p>
            @auth
            <a href="{{ route('dashboard') }}">
                <button>🎮 Entrar al Dashboard</button>
            </a>
            @else
            <a href="{{ route('register') }}">
                <button>🆕 Crear un compte gratuït</button>
            </a>
            @endauth
        </header>

        <h2 class="section-title text-center mb-12">Què ofereix GamerVault?</h2>

        <div class="features-grid">
            <div class="feature-card organize">
                <h3 class="feature-title">📂 Organitza</h3>
                <p class="feature-description">Administra la teva propia llista de videojocs fàcilment.</p>
            </div>

            <div class="feature-card share">
                <h3 class="feature-title">🔗 Comparteix</h3>
                <p class="feature-description">Recomana videojocs als teus amics.</p>
            </div>

            <div class="feature-card discover">
                <h3 class="feature-title">🔍 Descobreix</h3>
                <p class="feature-description">Explora recomanacions personalitzades segons els teus gustos.</p>
            </div>
        </div>

        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold mb-4">💬 Fòrum GamerVault</h1>

            @if($thread)
            <div class="bg-gray-900 text-white p-4 rounded-lg shadow mb-6" id="thread-{{ $thread->id }}">
                <h2 class="text-xl font-semibold">{{ $thread->title }}</h2>
                <div class="space-y-3 mt-4 posts" id="posts-thread-{{ $thread->id }}">
                    @foreach($thread->posts as $post)
                    <div class="bg-gray-800 p-3 rounded">
                        <p class="text-sm text-gray-400">{{ $post->user->name }} - {{ $post->created_at->diffForHumans() }}</p>
                        <p>{{ $post->content }}</p>
                    </div>
                    @endforeach
                </div>

                @auth
                <form class="post-form mt-4" data-thread-id="{{ $thread->id }}">
                    @csrf
                    <textarea name="content" rows="2" class="w-full p-2 rounded text-black" placeholder="Escriu un missatge..." required></textarea>
                    <button class="mt-2 bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700" type="submit">Enviar</button>
                </form>
                @else
                <p class="text-gray-400 mt-4">Has de <a href="{{ route('login') }}" class="text-purple-500 underline">iniciar sessió</a> per poder escriure missatges.</p>
                @endauth

            </div>
            @else
            <p class="text-white">No s'ha trobat el fil del fòrum general.</p>
            @endif
        </div>

        <br>

        <section class="mt-20 fade-in">
            <h2 class="text-3xl font-bold mb-6 text-center">Top del ranking</h2>
            <div class="relative">
                <div id="carousel-ranking" class="carousel-track">
                    <!-- Ranking es carregarà aquí -->
                </div>
            </div>
        </section>

        <section id="contacte" class="fade-in" style="margin-top: 5rem; text-align: center;">
            <h2 class="text-3xl font-bold mb-4">📩 Contacta amb nosaltres</h2>
            <p class="mb-6">Tens preguntes, suggeriments o simplement vols dir hola? Envia'ns un correu!</p>
            <a href="mailto:antonio.ruiz@insbaixcamp.cat">
                <button>📬 Contactar</button>
            </a>
        </section>
    </main>

    <!-- Incluye tu JS modular del foro -->
    <script src="{{ asset('js/forum-chat.js') }}"></script>
</body>

<footer class="bg-gray-800 text-white text-center py-6 mt-10">
    <div class="container mx-auto">
        <p>&copy; 2025 GamerVault. Tots els drets reservats.</p>
        <div class="mt-4">
            <a href="{{ route('privacy-policy') }}" class="text-yellow-400 hover:text-yellow-500 mx-2">Política de Privacitat</a>|
            <a href="{{ route('terms-of-service') }}" class="text-yellow-400 hover:text-yellow-500 mx-2">Condicions d'ús</a>|
            <a href="mailto:antonio.ruiz@insbaixcamp.cat" class="text-yellow-400 hover:text-yellow-500 mx-2">Contacta'ns</a>
        </div>
    </div>
</footer>

<script>
    const RAWG_API_KEY = "a6932e9255e64cf98bfa75abde510c5d";

    // Ranking
    fetch("/api/ranking")
        .then(res => res.json())
        .then(games => {
            const container = document.getElementById("carousel-ranking");
            games.slice(0, 3).forEach(game => {
                const card = document.createElement("div");
                card.className = "carousel-card";
                card.innerHTML = `
                    <img src="${game.background_image}" alt="${game.name}">
                    <div class="p-4">
                        <h3 class="font-bold text-lg mb-2">${game.name}</h3>
                        <p class="text-sm text-gray-600">⭐ ${game.rating ?? 'N/A'}</p>
                        <a href="https://rawg.io/games/${game.slug}" target="_blank" class="text-blue-600 text-sm hover:underline">🔗 RAWG</a>
                    </div>
                `;
                container.appendChild(card);
            });
        });
</script>

</html>