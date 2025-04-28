<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>GamerVault</title>

    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>

<body>

    <nav class="navbar">
        <div class="navbar-content">
            <h1 class="navbar-title">🎮 GamerVault</h1>
            <ul class="navbar-links">
                <li><a href="#caracteristiques" class="nav-link">Característiques</a></li>
                <li><a href="#popular" class="nav-link">Populars</a></li>
                <li><a href="#contacte" class="nav-link">Contacte</a></li>
                <li>
                    @auth
                    <form method="POST" action="{{ route('logout') }}" class="inline-form">
                        @csrf
                        <button type="submit" class="link-button">Tancar sessió</button>
                    </form>
                    @else
                    <a href="{{ route('login') }}" class="link-button">Inicia sessió</a>
                    @endauth
                </li>
            </ul>
        </div>
    </nav>

    <header class="container fade-in" style="text-align: center; padding-top: 3rem;">
        <h1 class="text-4xl font-bold mb-4">Benvingut a GamerVault</h1>
        <p class="text-lg mb-6">La teva plataforma definitiva per gestionar, descobrir i compartir videojocs.</p>

        @auth
        <a href="{{ route('dashboard') }}">
            <button>Entrar al Dashboard</button>
        </a>
        @else
        <a href="{{ route('register') }}">
            <button>Crear un compte gratuït</button>
        </a>
        @endauth
    </header>

    <section id="caracteristiques" class="container fade-in" style="margin-top: 5rem;">
        <h2 class="text-3xl font-bold mb-4">🔹 Què ofereix GamerVault?</h2>
        <div class="lists-grid">
            <div class="list-card">
                <h3>Organitza</h3>
                <p>Crea i administra les teves pròpies llistes de videojocs fàcilment.</p>
            </div>
            <div class="list-card">
                <h3>Comparteix</h3>
                <p>Ensenya als teus amics què estàs jugant o què recomanes.</p>
            </div>
            <div class="list-card">
                <h3>Descobreix</h3>
                <p>Explora recomanacions personalitzades segons els teus gustos.</p>
            </div>
        </div>
    </section>

    <section id="popular" class="container fade-in" style="margin-top: 5rem;">
        <h2 class="text-3xl font-bold mb-4">🔥 Llistes Populars</h2>
        <div class="lists-grid">
            <div class="list-card">
                <h3>Top RPGs 2025</h3>
                <p>Els jocs de rol que més ho estan petant aquest any.</p>
            </div>
            <div class="list-card">
                <h3>Millors Shooters</h3>
                <p>Intensitat, acció i adrenalina garantida.</p>
            </div>
            <div class="list-card">
                <h3>Indie Jewels</h3>
                <p>Petites obres mestres que no et pots perdre.</p>
            </div>
        </div>
    </section>

    <section id="contacte" class="container fade-in" style="margin-top: 5rem; text-align: center;">
        <h2 class="text-3xl font-bold mb-4">📩 Contacta amb nosaltres</h2>
        <p class="mb-6">Tens preguntes, suggeriments o simplement vols dir hola? Envia'ns un correu!</p>
        <a href="mailto:contacte@gamervault.com">
            <button>Contactar</button>
        </a>
    </section>

</body>

</html>