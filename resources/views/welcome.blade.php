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
            <h1 class="logo neon-text text-2xl font-bold">🎮 GamerVault</h1>
            <ul class="navbar-links flex space-x-6 items-center">
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

        <section id="popular" class="fade-in" style="margin-top: 5rem;">
            <h2 class="text-3xl font-bold mb-4">🔥 Llistes Populars</h2>
            <div class="lists-grid">
                <div class="list-card">
                    <h3>🏆 Top RPGs 2025</h3>
                    <p>Els jocs de rol que més ho estan petant aquest any.</p>
                </div>
                <div class="list-card">
                    <h3>💥 Millors Shooters</h3>
                    <p>Intensitat, acció i adrenalina garantida.</p>
                </div>
                <div class="list-card">
                    <h3>💎 Indie Jewels</h3>
                    <p>Petites obres mestres que no et pots perdre.</p>
                </div>
            </div>
        </section>

        <section id="contacte" class="fade-in" style="margin-top: 5rem; text-align: center;">
            <h2 class="text-3xl font-bold mb-4">📩 Contacta amb nosaltres</h2>
            <p class="mb-6">Tens preguntes, suggeriments o simplement vols dir hola? Envia'ns un correu!</p>
            <a href="mailto:contacte@gamervault.com">
                <button>📬 Contactar</button>
            </a>
        </section>
    </main>

</body>

</html>