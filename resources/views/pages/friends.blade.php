@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h2 class="text-2xl font-bold mb-4">👥 Els meus amics</h2>

    <ul id="friends-list" class="list-disc list-inside bg-white rounded shadow p-4 mb-6">
        <li>Carregant amics...</li>
    </ul>

    <h3 class="text-xl font-semibold mb-2">🔍 Buscar usuaris per afegir</h3>
    <input type="text" id="search-input" placeholder="Escriu un nom..." class="w-full p-2 border rounded mb-4">

    <ul id="search-results" class="bg-white rounded shadow p-4 hidden"></ul>
</div>

<script>
    // Mostrar amigos actuales
    fetch("/api/friends", {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'include'
        })
        .then(res => {
            if (!res.ok) throw new Error('No es pot carregar la llista.');
            return res.json();
        })
        .then(data => {
            const ul = document.getElementById("friends-list");
            ul.innerHTML = data.length ?
                data.map(friend => `<li class="py-2 border-b">${friend.name}</li>`).join('') :
                '<li class="text-gray-500">Encara no tens amics 😢</li>';
        })
        .catch(error => {
            document.getElementById("friends-list").innerHTML = `
            <li class="text-red-500">
                ${error.message}
                <a href="/login" class="text-blue-500 underline">Inicia sessió</a>
            </li>`;
        });

    // Buscar usuarios
    const input = document.getElementById("search-input");
    const results = document.getElementById("search-results");

    input.addEventListener("input", () => {
        const query = input.value.trim();
        if (query.length < 2) {
            results.classList.add("hidden");
            return;
        }

        fetch(`/users/search?q=${encodeURIComponent(query)}`, { // ← Usa la ruta correcta
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'include' // Importante para enviar la sesión/cookie de autenticación
            })
            .then(res => res.json())
            .then(users => {
                results.innerHTML = users.length ?
                    users.map(user => `
                    <li class="flex justify-between items-center py-2 border-b">
                        <span>${user.name}</span>
                        <button class="bg-blue-500 text-white px-2 py-1 rounded" onclick="sendFriendRequest(${user.id}, this)">Afegir</button>
                    </li>
                `).join('') :
                    '<li class="text-gray-500">Cap resultat trobat</li>';

                results.classList.remove("hidden");
            });
    });

    function sendFriendRequest(userId, button) {
        fetch('/api/friends/request', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    user_id: userId
                }),
                credentials: 'include'
            })
            .then(res => {
                if (!res.ok) throw new Error('Error en enviar la sol·licitud');
                button.textContent = "Sol·licitud enviada";
                button.disabled = true;
                button.classList.add("bg-gray-400");
            })
            .catch(() => alert("No s'ha pogut enviar la sol·licitud."));
    }
</script>
@endsection