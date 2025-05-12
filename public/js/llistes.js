document.addEventListener('DOMContentLoaded', function () {
    fetchUserGameList();
});

function fetchUserGameList() {
    fetch('/game-list', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
        .then(response => {
            console.log(response); // Verifica la respuesta
            if (!response.ok) {
                return response.text().then(text => {
                    if (text.startsWith("<!DOCTYPE")) {
                        throw new Error("Error: Se ha recibido una página HTML en lugar de JSON. Esto podría ser un error en la ruta o una redirección.");
                    }
                    throw new Error('Error: ' + text);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log("Lista de juegos:", data); // Verifica los datos recibidos
            renderGameList(data);
        })
        .catch(error => {
            console.error("Error:", error);
            const container = document.getElementById("lists-container");
            container.innerHTML = "<p class='text-red-500 text-center col-span-4'>No s'ha pogut carregar la llista. 😢</p>";
        });
}

function renderGameList(data) {
    const container = document.getElementById("lists-container");
    container.innerHTML = ''; // Limpiar contenido actual

    if (!data || Object.keys(data).length === 0) {
        container.innerHTML = "<p class='text-gray-600 text-center col-span-4'>No tens cap llista encara. 🗂️</p>";
        return;
    }

    const el = document.createElement("div");
    el.classList.add(
        "list-card",
        "p-6",
        "bg-white",
        "border",
        "border-gray-200",
        "rounded-lg",
        "shadow-md",
        "hover:shadow-lg",
        "transition-all",
        "duration-300"
    );

    // Información de la lista
    el.innerHTML = `
        <h3 class="font-semibold text-lg text-gray-800">${data.name}</h3>
        <p class="text-sm text-gray-500 mt-2">Jocs afegits: ${data.games.length}</p>
    `;

    const gamesContainer = document.createElement("div");
    gamesContainer.classList.add("mt-4", "grid", "grid-cols-1", "sm:grid-cols-2", "lg:grid-cols-3", "gap-6");

    data.games.forEach(game => {
        const gameCard = document.createElement("div");
        gameCard.classList.add(
            "game-card",
            "p-4",
            "mb-4",
            "bg-gray-100",
            "border",
            "border-gray-300",
            "rounded-lg",
            "shadow-sm",
            "transition-all",
            "duration-300"
        );

        const savedStatus = localStorage.getItem(`game-status-${game.id}`);
        const savedComment = localStorage.getItem(`game-comment-${game.id}`);
        const savedRating = localStorage.getItem(`game-rating-${game.id}`);
        const savedTimesFinished = localStorage.getItem(`game-times-finished-${game.id}`) || 0;

        gameCard.innerHTML = `
            <img src="${game.background_image || 'https://via.placeholder.com/150x150?text=Sense+imatge'}"
                 alt="${game.name}" class="mb-3 rounded shadow">
            <div class="text-center">
                <h4 class="font-semibold text-lg mb-1">${game.name}</h4>
                <div class="mt-2 text-xs text-gray-500 space-y-1">
                    <label class="block mt-2 text-gray-700">
                        Estat:
                        <select data-game-id="${game.id}" class="status-selector mt-1 p-1 rounded border">
                            <option value="pendiente" ${savedStatus === "pendiente" ? "selected" : game.status === "pendiente" ? "selected" : ""}>🎯 Pendiente</option>
                            <option value="jugando" ${savedStatus === "jugando" ? "selected" : game.status === "jugando" ? "selected" : ""}>🎮 Jugando</option>
                            <option value="completado" ${savedStatus === "completado" ? "selected" : game.status === "completado" ? "selected" : ""}>✅ Completado</option>
                        </select>
                    </label>

                    <label class="block mt-2 text-gray-700">Valoració personal:</label>
                    <div class="star-rating flex space-x-1 mt-1 mb-2 justify-center" data-game-id="${game.id}">
                        ${Array.from({ length: 5 }, (_, i) => {
            const value = i + 1;
            const filled = savedRating >= value ? 'text-yellow-400' : 'text-gray-300';
            return `<span data-value="${value}" class="cursor-pointer text-2xl ${filled}">★</span>`;
        }).join('')}
                    </div>

                    <label class="block mt-2 text-gray-700">
                        Vegades completat:
                        <input type="number" min="0" value="${savedTimesFinished}" 
                               data-game-id="${game.id}" class="times-finished mt-1 p-1 rounded border w-full">
                    </label>
                </div>

                <label class="block mt-2 text-gray-700">
                    Comentaris:
                    <textarea data-game-id="${game.id}" class="comment-box mt-1 p-1 w-full rounded border" rows="2"
                              placeholder="Escriu una nota...">${savedComment || game.comment || ''}</textarea>
                </label>
                
                <br>
                <button data-game-id="${game.id}" class="remove-game mt-4 bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded">
                    🗑️ Eliminar
                </button>
            </div>
        `;

        gameCard.querySelector('.status-selector').addEventListener('change', e => {
            const gameId = e.target.dataset.gameId;
            updateGameStatus(gameId, e.target.value);
        });

        gameCard.querySelector('.comment-box').addEventListener('blur', e => {
            const gameId = e.target.dataset.gameId;
            updateGameComment(gameId, e.target.value);
        });

        gameCard.querySelector('.remove-game').addEventListener('click', e => {
            const gameId = e.target.dataset.gameId;
            removeGameFromList(gameId);
        });

        gameCard.querySelector('.times-finished').addEventListener('blur', e => {
            const gameId = e.target.dataset.gameId;
            const timesFinished = e.target.value;
            localStorage.setItem(`game-times-finished-${gameId}`, timesFinished); // Guardar el valor actualizado
        });

        gameCard.querySelectorAll('.star-rating span').forEach(star => {
            star.addEventListener('click', e => {
                const selectedValue = parseInt(e.target.dataset.value);
                const gameId = e.target.closest('.star-rating').dataset.gameId;
                
                // Guardar la valoración en localStorage
                localStorage.setItem(`game-rating-${gameId}`, selectedValue);
                
                // Actualizar todas las estrellas
                const allStars = e.target.parentElement.querySelectorAll('span');
                allStars.forEach((s, i) => {
                    const isFilled = i < selectedValue;
                    s.classList.toggle('text-yellow-400', isFilled);
                    s.classList.toggle('text-gray-300', !isFilled);
                    s.classList.toggle('selected', isFilled);
                });
            });
        });

        // Recuperar la valoración guardada y aplicar la clase correcta en la carga inicial
        const initialRating = localStorage.getItem(`game-rating-${game.id}`);
        if (initialRating) {
            const allStars = gameCard.querySelectorAll('.star-rating span');
            allStars.forEach((star, i) => {
                const ratingValue = parseInt(initialRating);
                star.classList.toggle('text-yellow-400', i < ratingValue); // Aplicar amarillo
                star.classList.toggle('text-gray-300', i >= ratingValue); // Aplicar gris
            });
        }

        gamesContainer.appendChild(gameCard);
    });

    el.appendChild(gamesContainer);
    container.appendChild(el);
}

const searchInput = document.getElementById("search-input");
let timeout;

searchInput.addEventListener("input", function () {
    clearTimeout(timeout);

    const query = searchInput.value.trim();

    timeout = setTimeout(() => searchGames(query), 500);
});

function searchGames(query) {
    fetch(`/search-games?query=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(games => {
            console.log("Resultats:", games); // debug
            renderSearchResults(games);
        })
        .catch(err => {
            console.error("Error en la API:", err);
        });
}

function renderSearchResults(games) {
    const container = document.getElementById("search-results");
    container.className = "lists-grid"; // usa el mismo grid que las listas
    container.innerHTML = '';

    if (!games.length) {
        container.innerHTML = `
            <div class="col-span-full text-center text-gray-400 italic py-12 text-lg">
                No s'han trobat jocs amb aquesta cerca. 😕
            </div>
        `;
        return;
    }

    games.forEach(game => {
        const platforms = game.platforms?.map(p => p.platform.name).join(', ') || 'Desconegudes';
        const genres = game.genres?.map(g => g.name).join(', ') || 'Sense gènere';
        const rating = game.rating ? `${game.rating}/5` : 'Sense puntuació';
        const developers = game.developers?.map(d => d.name).join(', ') || 'Desconegut';

        const card = document.createElement("div");
        card.className = "list-card";

        card.innerHTML = `
            <img src="${game.background_image || 'https://via.placeholder.com/400x200?text=Sense+imatge'}"
                 alt="${game.name}">
            
            <h4 class="text-white text-lg font-semibold text-center mb-1">${game.name}</h4>
            ${game.released ? `<p class="text-gray-400 text-sm text-center">Publicat: ${game.released}</p>` : ''}
            <p class="text-gray-300 text-sm mt-2"><strong>Plataformes:</strong> ${platforms}</p>
            <p class="text-gray-300 text-sm"><strong>Gèneres:</strong> ${genres}</p>
            <p class="text-gray-300 text-sm"><strong>Puntuació:</strong> ${rating}</p>

            <button class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition-all mt-auto">
                Afegir 🎮
            </button>
        `;

        card.querySelector("button").addEventListener("click", () => addGameToList(game));
        container.appendChild(card);
    });
}

function addGameToList(game) {
    fetch('/game-list', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify(game)
    })
        .then(res => {
            console.log(res.body);
            if (!res.ok) {
                return res.text().then(text => {
                    // Verifica si la respuesta es HTML, y muestra detalles si es el caso
                    if (text.startsWith("<!DOCTYPE")) {
                        console.error("Error: Se recibió una página HTML en lugar de JSON.");
                        console.error(text); // Verifica el contenido del HTML
                        throw new Error("Error: Se ha recibido una página HTML en lugar de JSON. Esto podría ser un error de ruta o redireccionamiento.");
                    }
                    // Si no es HTML, lanza el error normal
                    throw new Error("Error al agregar el juego: " + text);
                });
            }
            return res.json(); // Intenta parsear la respuesta como JSON
        })
        .then(data => {
            console.log("Juego agregado:", data); // Verifica los datos recibidos
            fetchUserGameList(); // Refresca la lista
            showNotification(`Joc "${game.name}" afegit a la teva llista!`);
        })
        .catch(err => {
            console.error("Error:", err);
        });
}

function showNotification(message) {
    const notif = document.getElementById("notification");
    const msg = document.getElementById("notification-message");
    msg.textContent = message;
    notif.classList.remove("hidden");
    setTimeout(() => notif.classList.add("hidden"), 3000);
}

function updateGameStatus(gameId, status) {
    // Guardar en localStorage
    localStorage.setItem(`game-status-${gameId}`, status);

    // Enviar al servidor
    fetch(`/game-list/${gameId}/status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ status })
    })
        .then(res => res.json())
        .then(data => {
            console.log("Estat actualitzat", data);
            showNotification("Estat actualitzat correctament ✅");
        });
}

function updateGameComment(gameId, comment) {
    // Guardar en localStorage
    localStorage.setItem(`game-comment-${gameId}`, comment);

    fetch(`/game-list/${gameId}/comment`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ comment })
    })
        .then(res => {
            if (!res.ok) {
                return res.text().then(text => {
                    if (text.startsWith("<!DOCTYPE")) {
                        throw new Error("Error HTML (probable fallo en backend)");
                    }
                    throw new Error("Error: " + text);
                });
            }
            return res.json();
        })
        .then(data => {
            console.log("Comentari guardat", data);
            showNotification("Comentari guardat 📝");
        })
        .catch(err => {
            console.error("Error al guardar el comentari:", err);
            showNotification("Error al guardar el comentari ⚠️");
        });
}

function removeGameFromList(gameId) {
    // Preguntar al usuario si está seguro de eliminar el juego
    if (confirm("Estàs segur de voler eliminar aquest joc de la teva llista?")) {
        fetch(`/game-list/${gameId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
            .then(res => {
                if (!res.ok) {
                    return res.text().then(text => {
                        if (text.startsWith("<!DOCTYPE")) {
                            throw new Error("Error HTML (probable fallo en backend)");
                        }
                        throw new Error("Error: " + text);
                    });
                }
                return res.json();
            })
            .then(data => {
                console.log("Eliminat:", data);
                fetchUserGameList();
                showNotification("Joc eliminat correctament 🗑️");
            })
            .catch(err => {
                console.error("Error al eliminar:", err);
            });
    } else {
        console.log("Eliminació cancel·lada");
    }
}
