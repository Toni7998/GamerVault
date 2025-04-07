<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GamerVault</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>

<body>
    <nav class="navbar">
        <h1>🎮 GamerVault</h1>
        <ul>
            <li><a href="/dashboard">Llistes</a></li>
            <li><a href="/friends">Amics</a></li>
            <li><a href="/recomanacions">Recomanacions</a></li>
            <li><a href="/ranking">Pòdium</a></li>
            <li><a href="/logout">Tancar sessió</a></li>
        </ul>
    </nav>

    <main>
        @yield('content')
    </main>
</body>

</html>