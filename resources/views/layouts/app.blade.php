<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>GamerVault</title>

    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>

<body class="font-sans">
    <nav class="navbar">
        <h1 class="text-2xl font-bold">🎮 GamerVault</h1>
        <ul class="flex space-x-4 items-center">
            <li><a href="{{ route('dashboard') }}" class="nav-link">Llistes</a></li>
            <li><a href="{{ route('friends') }}" class="nav-link">Amics</a></li>
            <li><a href="{{ route('recomanacions') }}" class="nav-link">Recomanacions</a></li>
            <li><a href="{{ route('ranking') }}" class="nav-link">Pòdium</a></li>
            <li>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="nav-link link-button">
                        Tancar sessió
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    <main class="container p-4 fade-in">
        @yield('content')
    </main>
</body>

</html>