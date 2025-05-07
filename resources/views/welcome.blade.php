<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GamerVault</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
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
                            🚪 Tancar sessió
                        </button>
                    </form>
                    @else
                    <a href="{{ route('login') }}" class="link-button text-lg hover:text-yellow-400">
                        🔐 Inicia sessió
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

        <section id="caracteristiques" class="fade-in" style="margin-top: 5rem;">
            <h2 class="text-3xl font-bold mb-4">🔹 Què ofereix GamerVault?</h2>
            <div class="lists-grid">
                <div class="list-card">
                    <h3>📂 Organitza</h3>
                    <p>Crea i administra les teves pròpies llistes de videojocs fàcilment.</p>
                </div>
                <div class="list-card">
                    <h3>🔗 Comparteix</h3>
                    <p>Ensenya als teus amics què estàs jugant o què recomanes.</p>
                </div>
                <div class="list-card">
                    <h3>🔍 Descobreix</h3>
                    <p>Explora recomanacions personalitzades segons els teus gustos.</p>
                </div>
            </div>
        </section>

        <section class="mt-20 fade-in">
            <h2 class="text-3xl font-bold mb-6 text-center">🎯 Recomanacions destacades</h2>
            <div class="relative">
                <div id="carousel-recomanacions" class="carousel-track">
                    <!-- Recomanacions es carregaran aquí -->
                </div>
            </div>
        </section>

        <section class="mt-20 fade-in">
            <h2 class="text-3xl font-bold mb-6 text-center">🏆 Top del ranking</h2>
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

</body>

<footer class="bg-gray-800 text-white text-center py-6 mt-10">
    <div class="container mx-auto">
        <p>&copy; 2025 GamerVault. Tots els drets reservats.</p>
        <div class="mt-4">
            <a href="{{ route('privacy-policy') }}" class="text-yellow-400 hover:text-yellow-500 mx-2">Política de Privacitat</a>|
            <a href="{{ route('terms-of-service') }}" class="text-yellow-400 hover:text-yellow-500 mx-2">Condicions d'ús</a>|
            <a href="mailto:antonio.ruiz@insbaixcamp.cat" class="text-yellow-400 hover:text-yellow-500 mx-2">Contacta'ns</a>
        </div>
        <div class="mt-4">
            <a href="https://twitter.com/gamervault" class="text-yellow-400 hover:text-yellow-500 mx-2" target="_blank">Twitter</a>
            <a href="https://facebook.com/gamervault" class="text-yellow-400 hover:text-yellow-500 mx-2" target="_blank">Facebook</a>
        </div>
    </div>
</footer>

<script>
    function scrollCarousel(id, direction) {
        const el = document.getElementById('carousel-' + id);
        const cardWidth = el.querySelector('.carousel-card')?.offsetWidth || 300;
        el.scrollBy({
            left: direction * (cardWidth + 16),
            behavior: 'smooth'
        });
    }

    const RAWG_API_KEY = "a6932e9255e64cf98bfa75abde510c5d";

    // Recomanacions
    fetch("/api/recommendations")
        .then(res => res.json())
        .then(recs => {
            const container = document.getElementById("carousel-recomanacions");
            recs.slice(0, 3).forEach(rec => {
                fetch(`https://api.rawg.io/api/games?key=${RAWG_API_KEY}&search=${encodeURIComponent(rec.game)}&page_size=1`)
                    .then(res => res.json())
                    .then(data => {
                        const game = data.results?.[0];
                        if (!game || !game.background_image) return;

                        const card = document.createElement("div");
                        card.className = "carousel-card";
                        card.innerHTML = `
                            <img src="${game.background_image}" alt="${game.name}">
                            <div class="p-4">
                                <h3 class="font-bold text-lg mb-2">${game.name}</h3>
                                <p class="text-sm text-gray-600">👤 ${rec.sender}</p>
                                <a href="https://rawg.io/games/${game.slug}" target="_blank" class="text-blue-600 text-sm hover:underline">🔗 RAWG</a>
                            </div>
                        `;
                        container.appendChild(card);
                    });
            });
        });

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

    // Detectar la visibilidad de los carruseles
    function checkCarouselVisibility() {
        const carousels = document.querySelectorAll('.relative');
        carousels.forEach(carousel => {
            const rect = carousel.getBoundingClientRect();
            const isVisible = rect.top < window.innerHeight && rect.bottom >= 0;

            // Mostrar las flechas si el carrusel es visible
            const leftBtn = carousel.querySelector('.carousel-btn.left');
            const rightBtn = carousel.querySelector('.carousel-btn.right');

            if (isVisible) {
                leftBtn.style.display = 'flex';
                rightBtn.style.display = 'flex';
            } else {
                leftBtn.style.display = 'none';
                rightBtn.style.display = 'none';
            }
        });
    }

    // Llamar a la función cuando el documento se carga 
    document.addEventListener('DOMContentLoaded', checkCarouselVisibility);
</script>

</html>