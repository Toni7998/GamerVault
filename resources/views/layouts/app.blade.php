<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>GamerVault - @yield('title', 'Inici')</title>

    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>

<body class="font-sans bg-gray-50 text-gray-800">

    <nav class="navbar bg-gray-800 text-white shadow-md">
        <div class="navbar-inner flex justify-between items-center px-6 py-3">
            <a href="{{ url('/') }}">
                <img src="{{ asset('media/logo-gamer_vault_horizontal.png') }}" alt="GamerVault Logo" class="h-10">
            </a>

            <ul class="flex space-x-6 bg-customPurple items-center">
                <li><a href="{{ route('dashboard') }}" class="nav-link text-lg hover:text-yellow-400">📋 Llistes</a></li>
                <li><a href="{{ route('friends') }}" class="nav-link text-lg hover:text-yellow-400">👯 Amics</a></li>
                <li><a href="{{ route('recomanacions') }}" class="nav-link text-lg hover:text-yellow-400">🔎 Recomanacions</a></li>
                <li><a href="{{ route('ranking') }}" class="nav-link text-lg hover:text-yellow-400">🏆 Ranking</a></li>
                <li><a href="{{ route('contacte') }}" class="nav-link text-lg hover:text-yellow-400">📩 Contacte</a></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" class="inline-form">
                        @csrf
                        <button type="submit" class="nav-link link-button text-lg hover:text-red-400">
                            🚪 Tancar sessió
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 py-12 text-center fade-in">
        @yield('content') <!-- Este es el contenido específico de cada página -->
    </main>

    <!-- Footer -->
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

</body>

</html>