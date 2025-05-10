@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h2 class="text-2xl font-bold mb-4">👥 Els meus amics</h2>

    <ul id="friends-list" class="list-disc list-inside bg-white rounded shadow p-4">
        <li>Carregant amics...</li>
    </ul>
</div>

<script>
    fetch("/api/friends", {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'include' // Importante para enviar la sesión/cookie
        })
        .then(res => {
            if (!res.ok) {
                if (res.status === 401) {
                    throw new Error('No estàs autenticat. Si us plau, inicia sessió.');
                }
                throw new Error(`Error ${res.status}: ${res.statusText}`);
            }
            return res.json();
        })
        .then(data => {
            const ul = document.getElementById("friends-list");
            ul.innerHTML = '';

            if (data.length === 0) {
                ul.innerHTML = '<li class="text-gray-500">Encara no tens amics 😢</li>';
            } else {
                data.forEach(friend => {
                    const li = document.createElement("li");
                    li.className = "py-2 border-b";
                    li.textContent = friend.name;
                    ul.appendChild(li);
                });
            }
        })
        .catch(error => {
            const ul = document.getElementById("friends-list");
            ul.innerHTML = `
            <li class="text-red-500">
                ${error.message}
                <a href="/login" class="text-blue-500 underline">Inicia sessió</a>
            </li>`;
        });
</script>
@endsection