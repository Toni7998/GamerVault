const input = document.getElementById("search-input");
const results = document.getElementById("search-results");

input.addEventListener("input", () => {
    const query = input.value.trim();
    if (query.length < 2) {
        results.classList.add("hidden");
        return;
    }

    fetch(`/users/search?q=${encodeURIComponent(query)}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include'
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
    Swal.fire({
        title: 'Enviar sol·licitud?',
        text: 'Vols enviar una sol·licitud d\'amistat a aquest usuari?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, enviar-la',
        cancelButtonText: 'Cancel·lar',
        background: '#1f2937',
        color: '#f9fafb',
        iconColor: '#60a5fa',
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/friends/request', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({
                    user_id: userId
                }),
                credentials: 'include'
            })
                .then(res => {
                    if (!res.ok) return res.json().then(err => {
                        throw new Error(err.message || 'Error');
                    });

                    Swal.fire({
                        icon: 'success',
                        title: 'Sol·licitud enviada',
                        background: '#1f2937',
                        color: '#f9fafb',
                        iconColor: '#22c55e',
                        showConfirmButton: false,
                        timer: 1500
                    });

                    button.textContent = "Sol·licitud enviada";
                    button.disabled = true;
                    button.classList.add("bg-gray-400");
                })
                .catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.message || "No s'ha pogut enviar la sol·licitud.",
                        background: '#1f2937',
                        color: '#f9fafb',
                        iconColor: '#ef4444'
                    });
                });
        }
    });
}

function handleFriendResponse(senderId, action, button) {
    const url = action === 'accept' ? `/friends/accept/${senderId}` : `/friends/decline/${senderId}`;

    Swal.fire({
        title: action === 'accept' ? 'Acceptar sol·licitud?' : 'Rebutjar sol·licitud?',
        icon: action === 'accept' ? 'question' : 'warning',
        showCancelButton: true,
        confirmButtonText: action === 'accept' ? 'Acceptar' : 'Rebutjar',
        cancelButtonText: 'Cancel·lar',
        confirmButtonColor: action === 'accept' ? '#10b981' : '#ef4444',
        background: '#1f2937',
        color: '#f9fafb',
        iconColor: '#f59e0b'
    }).then(result => {
        if (!result.isConfirmed) return;

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'include'
        })
            .then(res => {
                if (!res.ok) throw new Error('Error en la resposta');
                Swal.fire({
                    icon: 'success',
                    title: action === 'accept' ? 'Amic afegit!' : 'Sol·licitud rebutjada',
                    showConfirmButton: false,
                    timer: 1500,
                    background: '#1f2937',
                    color: '#f9fafb',
                    iconColor: '#10b981'
                });
                loadRequests();
                loadFriends();
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No s\'ha pogut processar la sol·licitud.',
                    background: '#1f2937',
                    color: '#f9fafb',
                    iconColor: '#f87171'
                });
            });
    });
}

function removeFriend(userId, button) {
    Swal.fire({
        title: 'Vols eliminar aquest amic?',
        text: 'Aquesta acció no es pot desfer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancel·lar',
        background: '#1f2937',
        color: '#f9fafb',
        iconColor: '#f87171'
    }).then((result) => {
        if (!result.isConfirmed) return;

        fetch(`/friends/remove/${userId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'include'
        })
            .then(res => {
                if (!res.ok) throw new Error('Error al eliminar amic');
                Swal.fire({
                    icon: 'success',
                    title: 'Amic eliminat',
                    background: '#1f2937',
                    color: '#f9fafb',
                    iconColor: '#ef4444',
                    showConfirmButton: false,
                    timer: 1500
                });
                loadFriends();
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No s\'ha pogut eliminar l\'amic.',
                    background: '#1f2937',
                    color: '#f9fafb',
                    iconColor: '#f87171'
                });
            });
    });
}

function loadFriends() {
    fetch("/api/friends", {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include'
    })
        .then(res => res.json())
        .then(data => {
            const ul = document.getElementById("friends-list");
            ul.innerHTML = data.length ?
                data.map(friend => `
                <li class="py-2 border-b flex justify-between items-center">
                    <span>${friend.name}</span>
                    <button onclick="removeFriend(${friend.id}, this)" class="bg-red-500 text-white px-2 py-1 rounded text-sm">
                        Eliminar
                    </button>
                </li>
            `).join('') :
                '<li class="text-gray-500">Encara no tens amics 😢</li>';
        })
        .catch(error => {
            document.getElementById("friends-list").innerHTML = `
            <li class="text-red-500">
                ${error.message}
                <a href="/login" class="text-blue-500 underline">Inicia sessió</a>
            </li>`;
        });
}

function loadRequests() {
    fetch("/friends/requests", {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include'
    })
        .then(res => res.json())
        .then(data => {
            const list = document.getElementById("friend-requests");
            if (!data.length) {
                list.innerHTML = '<li class="text-gray-500">No tens sol·licituds pendents</li>';
                list.classList.remove("hidden");
                return;
            }

            list.innerHTML = data.map(req => `
            <li class="flex justify-between items-center py-2 border-b">
                <span>${req.sender.name}</span>
                <div class="space-x-2">
                    <button onclick="handleFriendResponse(${req.sender.id}, 'accept', this)" class="bg-green-500 text-white px-2 py-1 rounded">Acceptar</button>
                    <button onclick="handleFriendResponse(${req.sender.id}, 'decline', this)" class="bg-red-500 text-white px-2 py-1 rounded">Rebutjar</button>
                </div>
            </li>
        `).join('');
            list.classList.remove("hidden");
        });
}

// Inicialización
loadFriends();
loadRequests();
