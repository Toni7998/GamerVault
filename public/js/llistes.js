
//  Hacemos que cuando arranca la web se haga lo que esta dentro del código
document.addEventListener('DOMContentLoaded', async () => {

    // Primero cargar la lista de juegos del usuario
    fetchUserGameList();

    // Luego cargar las recomendaciones, filtrando las que ya están en la lista
    try {
        const recommendations = await fetchReceivedRecommendations();

        // Obtener la lista actual de juegos del usuario para filtrar
        const userGamesResponse = await fetch('/game-list', {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        let userGames = [];
        if (userGamesResponse.ok) {
            const userData = await userGamesResponse.json();
            userGames = userData.games || [];
        }

        // Filtrar recomendaciones que ya están en la lista
        const filteredRecommendations = recommendations.filter(rec => {
            return !userGames.some(game => game.id === rec.game.id);
        });

        renderReceivedRecommendations(filteredRecommendations);
    } catch (error) {
        console.error("Error inicial:", error);
    }

});

/**
 * Funció perquè l'usuari només tingui una llista que sigui la seva
 */
function fetchUserGameList() {
    fetch('/game-list', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
        .then(response => {
            console.log(response);
            if (!response.ok) {
                return response.text().then(text => {
                    if (text.startsWith("<!DOCTYPE")) {
                        throw new Error("Error: S'ha rebut una pàgina HTML en lloc de JSON. Pot ser un error de ruta o una redirecció.");
                    }
                    throw new Error('Error: ' + text);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log("Llista de jocs:", data);
            renderGameList(data);
        })
        .catch(error => {
            console.error("Error:", error);
            const container = document.getElementById("lists-container");
            container.innerHTML = "<p class='text-red-500 text-center col-span-4'>No s'ha pogut carregar la llista. 😢</p>";
        });
}

/**
 * Funció per mostrar la llista
 * @param {*} data 
 * @returns 
 */
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
            "flex",
            "flex-col",
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
    <div class="flex flex-col items-center">
        <img src="${game.background_image || 'https://via.placeholder.com/150x150?text=Sense+imatge'}"
             alt="${game.name}" class="w-40 h-40 object-cover rounded shadow">

        <a href="https://rawg.io/games/${game.id}" target="_blank"
   class="text-blue-600 text-sm underline mt-2 hover:text-blue-800 text-center">
   🔗 Veure a RAWG
</a>
<br>
<button data-game-id="${game.id}" class="recommend-game bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded mt-2 self-center">
  📩 Recomanar a un amic
</button>

    </div>

                <div class="mt-2 text-xs text-gray-500 space-y-2">

                    <h4 class="font-semibold text-lg mt-2 text-center">${game.name}</h4>

                <label class="block text-gray-700">
                    Estat:
                    <select data-game-id="${game.id}" class="status-selector mt-1 p-1 rounded border w-full">
                        <option value="pendiente" ${savedStatus === "pendiente" ? "selected" : game.status === "pendiente" ? "selected" : ""}>🎯 Pendent</option>
                        <option value="jugando" ${savedStatus === "jugando" ? "selected" : game.status === "jugando" ? "selected" : ""}>🎮 Jugant</option>
                        <option value="completado" ${savedStatus === "completado" ? "selected" : game.status === "completado" ? "selected" : ""}>✅ Completat</option>
                    </select>
                </label>

                <label class="block text-gray-700">
                    Valoració personal:
                    <div class="star-rating flex justify-center space-x-1 mt-1" data-game-id="${game.id}">
                        ${Array.from({ length: 5 }, (_, i) => {
            const value = i + 1;
            const filled = savedRating >= value ? 'text-yellow-400' : 'text-gray-300';
            return `<span data-value="${value}" class="cursor-pointer text-2xl ${filled}">★</span>`;
        }).join('')}
                    </div>
                </label>

                <label class="block text-gray-700">
                    Vegades completat:
                    <input type="number" min="0" value="${savedTimesFinished}"
                           data-game-id="${game.id}" class="times-finished mt-1 p-1 rounded border w-full">
                </label>

                <label class="block text-gray-700">
                    Comentaris:
                    <textarea data-game-id="${game.id}" class="comment-box mt-1 p-1 w-full rounded border" rows="2"
                              placeholder="Escriu una nota...">${savedComment || game.comment || ''}</textarea>
                </label>

                            <button data-game-id="${game.id}" class="remove-game delete-button bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded mt-4 self-center">
                🗑️
            </button>
            </div>


        `;

        // Eventos
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
            localStorage.setItem(`game-times-finished-${gameId}`, e.target.value);
        });

        gameCard.querySelectorAll('.star-rating span').forEach(star => {
            star.addEventListener('click', e => {
                const selectedValue = parseInt(e.target.dataset.value);
                const gameId = e.target.closest('.star-rating').dataset.gameId;
                localStorage.setItem(`game-rating-${gameId}`, selectedValue);

                // Asegúrate de que el token CSRF está presente en el <meta> tag en tu HTML
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

                if (!csrfToken) {
                    console.error("CSRF token no encontrado");
                }

                // Enviar al servidor el nuevo valor
                fetch('/api/personal-ranking', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,  // Asegúrate de que el token CSRF sea válido
                    },
                    body: JSON.stringify({
                        game_id: gameId,
                        rating: selectedValue
                    })
                })
                    .then(response => {
                        if (!response.ok) {
                            // Si la respuesta no es 2xx, lanzamos un error
                            throw new Error(`Error en la solicitud: ${response.status} ${response.statusText}`);
                        }
                        return response.json(); // Intenta convertir la respuesta a JSON
                    })
                    .then(data => {
                        console.log("Valoración enviada correctamente:", data);
                        // Puedes manejar la respuesta aquí si es necesario
                    })
                    .catch(error => {
                        console.error("Error al enviar la valoración:", error);
                    });

                // Actualizar las estrellas visualmente
                const allStars = e.target.parentElement.querySelectorAll('span');
                allStars.forEach((s, i) => {
                    const isFilled = i < selectedValue;
                    s.classList.toggle('text-yellow-400', isFilled);
                    s.classList.toggle('text-gray-300', !isFilled);
                    s.classList.toggle('selected', isFilled);
                });
            });

        });

        // Aplicar estrellas al iniciar
        if (savedRating) {
            const allStars = gameCard.querySelectorAll('.star-rating span');
            allStars.forEach((star, i) => {
                const ratingValue = parseInt(savedRating);
                star.classList.toggle('text-yellow-400', i < ratingValue);
                star.classList.toggle('text-gray-300', i >= ratingValue);
            });
        }

        gameCard.querySelector('.recommend-game').addEventListener('click', async (e) => {
            const gameId = e.target.dataset.gameId;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            let friends = [];
            try {
                const friendsResponse = await fetch('/api/friends', {
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    credentials: 'include'
                });
                if (!friendsResponse.ok) throw new Error('No s\'han pogut carregar els amics');
                friends = await friendsResponse.json();

                // Ensure friends array contains both types of relationships
                friends = friends.map(friend => {
                    // Normalize friend data structure
                    return {
                        id: friend.id || friend.user_id,
                        name: friend.name || friend.username || friend.email,
                        // Add any other necessary fields
                    };
                });
            } catch (err) {
                return Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.message,
                    background: '#1e1e1e',
                    color: '#f0f0f0',
                    customClass: { popup: 'swal2-dark' }
                });
            }

            if (friends.length === 0) {
                return Swal.fire({
                    icon: 'info',
                    title: 'Cap amic',
                    text: 'No tens amics per recomanar aquest joc.',
                    background: '#1e1e1e',
                    color: '#f0f0f0',
                    customClass: { popup: 'swal2-dark' }
                });
            }

            const friendOptions = friends.reduce((obj, f) => {
                obj[f.id] = f.name;
                return obj;
            }, {});

            const { value: friendId } = await Swal.fire({
                title: 'Recomanar joc a un amic',
                input: 'select',
                inputOptions: friendOptions,
                inputPlaceholder: 'Selecciona un amic',
                showCancelButton: true,
                confirmButtonText: 'Enviar',
                cancelButtonText: 'Cancel·la',
                background: '#1e1e1e',
                color: '#f0f0f0',
                customClass: {
                    popup: 'swal2-dark',
                    input: 'swal2-select-dark'
                },
                inputValidator: (value) => {
                    if (!value) {
                        return 'Si us plau, selecciona un amic!';
                    }
                }
            });

            if (friendId) {
                try {
                    const response = await fetch('/recommend-game', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'include',
                        body: JSON.stringify({ game_id: gameId, friend_id: friendId })
                    });

                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.message || 'Error enviant la recomanació');
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Recomanació enviada!',
                        text: 'Has recomanat el joc al teu amic.',
                        timer: 2000,
                        showConfirmButton: false,
                        background: '#1e1e1e',
                        color: '#f0f0f0',
                        customClass: { popup: 'swal2-dark' }
                    });
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'No s\'ha pogut enviar la recomanació.',
                        background: '#1e1e1e',
                        color: '#f0f0f0',
                        customClass: { popup: 'swal2-dark' }
                    });
                }
            }
        });


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


/**
 * Funció per buscar els jocs a la barra de cerca
 * @param {*} query 
 */
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


/**
 * Funció perquè apareguin els jocs de la cerca
 * @param {*} games 
 * @returns 
 */
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


/**
 * Función para agregar juegos a la lista y eliminar la recomendación
 * @param {*} game 
 * @param {HTMLElement} [recommendationCard] - Elemento de la tarjeta de recomendación a eliminar
 */
async function addGameToList(game, recommendationCard) {
    const result = await Swal.fire({
        title: 'Afegir joc a la llista?',
        text: `Vols afegir "${game.name}" a la teva llista?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, afegeix-lo!',
        cancelButtonText: 'Cancel·la',
        background: '#1e1e1e',
        color: '#f0f0f0',
        confirmButtonColor: '#4caf50',
        cancelButtonColor: '#f44336'
    });

    if (!result.isConfirmed) return;

    // Pantalla de càrrega
    Swal.fire({
        title: 'Afegint joc...',
        html: 'Espera mentre es comprova la informació i s’afegeix el joc.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
        background: '#1e1e1e',
        color: '#f0f0f0'
    });

    try {
        // PAS 1: Obtenir dades extres del joc des de RAWG
        const rawgResponse = await fetch(`/api/rawg/details/${game.id}`);
        if (!rawgResponse.ok) {
            throw new Error("No s'han pogut obtenir detalls del joc des de RAWG.");
        }
        const detailedGame = await rawgResponse.json();

        // PAS 2: Enviar a la teva BD
        const postResponse = await fetch('/game-list', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                id: detailedGame.id,
                title: detailedGame.name,
                background_image: detailedGame.background_image || 'https://placehold.co/150x150?text=Sense+imatge',
                released: detailedGame.released,
                platform: detailedGame.platforms?.[0]?.platform?.name || null
            })
        });

        if (!postResponse.ok) {
            const text = await postResponse.text();
            if (text.startsWith("<!DOCTYPE")) {
                throw new Error("Error: S'ha rebut una pàgina HTML en lloc de JSON.");
            }
            throw new Error("Error al afegir el joc: " + text);
        }

        const data = await postResponse.json();

        // Eliminar la recomendación si existe
        if (recommendationCard) {
            // Obtener el ID de la recomendación del botón de eliminar
            const deleteButton = recommendationCard.querySelector('button[data-recommendation-id]');
            const recommendationId = deleteButton ? deleteButton.dataset.recommendationId : null;

            if (recommendationId) {
                // Eliminar la recomendación del servidor
                await fetch(`/api/recommendations/${recommendationId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
            }

            // Eliminar visualmente la tarjeta
            recommendationCard.remove();

            // Actualizar contador de recomendaciones
            const container = document.getElementById('recommendations-container');
            const remainingCards = container.querySelectorAll('.list-card');
            const title = container.querySelector('h3');

            if (remainingCards.length === 0) {
                container.innerHTML = `
                    <div class="list-card" style="text-align: center; padding: 2rem;">
                        <p style="font-size: 1.125rem; color: #fff; margin-bottom: 0.5rem;">🎉 Ja no tens recomanacions pendents</p>
                        <p style="font-size: 0.875rem; color: #a0a0a0;">El joc s'ha afegit correctament a la teva llista</p>
                    </div>
                `;
            } else if (title) {
                title.innerHTML = `🎮 Jocs recomanats (${remainingCards.length})`;
            }
        }

        // Refrescar llista
        await fetchUserGameList();

        // Refrescar las recomendaciones
        const updatedRecommendations = await fetchReceivedRecommendations();
        renderReceivedRecommendations(updatedRecommendations);

        Swal.fire({
            icon: 'success',
            title: 'Afegit!',
            text: `El joc "${detailedGame.name}" ha estat afegit a la teva llista!`,
            timer: 2000,
            showConfirmButton: false,
            background: '#1e1e1e',
            color: '#f0f0f0'
        });

        // Guarda info bàsica localment
        localStorage.setItem(`game-name-${game.id}`, game.name);
        localStorage.setItem(`game-image-${game.id}`, game.background_image || 'https://placehold.co/150x150?text=Sense+imatge');
        localStorage.setItem(`game-platforms-${game.id}`, game.platforms?.map(p => p.platform.name).join(', ') || '');
        localStorage.setItem(`game-released-${game.id}`, game.released || '');

    } catch (err) {
        console.error("Error afegint joc:", err);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: err.message || 'Hi ha hagut un problema afegint el joc.',
            background: '#1e1e1e',
            color: '#f0f0f0'
        });
    }
}



/**
 * Funció per actualitzar l'estat del joc
 * @param {*} gameId 
 * @param {*} status 
 */
function updateGameStatus(gameId, status) {
    localStorage.setItem(`game-status-${gameId}`, status);

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
            Swal.fire({
                icon: 'success',
                title: 'Estat actualitzat ✅',
                timer: 1500,
                showConfirmButton: false,
                background: '#1e1e1e',
                color: '#f0f0f0'
            });
        });
}


/**
 * Funció per actualitzar els comentaris
 * @param {*} gameId 
 * @param {*} comment 
 */
function updateGameComment(gameId, comment) {
    fetch('/test-update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ test: true })
    })
        .then(response => {
            if (!response.ok) {
                throw new Error("Error al guardar el comentari");
            }
            return response.json();
        })
        .then(data => {
            console.log("Comentari actualitzat:", data);
        })
        .catch(error => {
            console.error("Error al guardar el comentari", error);
        });
}


/**
 * Funció per esborrar els jocs de la llista
 * @param {*} gameId 
 */
function removeGameFromList(gameId) {
    Swal.fire({
        title: 'Estàs segur?',
        text: "Aquest joc serà eliminat de la teva llista.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, elimina-ho!',
        cancelButtonText: 'Cancel·la',
        background: '#1e1e1e',
        color: '#f0f0f0'
    }).then((result) => {
        if (result.isConfirmed) {
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
                    Swal.fire({
                        icon: 'success',
                        title: 'Eliminat!',
                        text: 'El joc ha estat eliminat correctament 🗑️',
                        timer: 2000,
                        showConfirmButton: false,
                        background: '#1e1e1e',
                        color: '#f0f0f0'
                    });
                })
                .catch(err => {
                    console.error("Error al eliminar:", err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No s\'ha pogut eliminar el joc.',
                        background: '#1e1e1e',
                        color: '#f0f0f0'
                    });
                });
        }
    });
}

// Función unificada para obtener recomendaciones
async function fetchRecommendations() {
    try {
        const response = await fetch('/api/recommendations', {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            credentials: 'include'
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        // Depuración: Ver los datos recibidos
        console.log("Datos de recomendaciones recibidos:", data);

        // Normalizar la estructura de datos
        return data.map(item => ({
            game: {
                id: item.game?.id || item.game_id,
                name: item.game?.name || item.game_name || "Joc desconegut",
                background_image: item.game?.background_image || item.game_image || 'https://via.placeholder.com/150x200?text=No+Imatge'
            },
            sender: {
                id: item.sender?.id || item.sender_id,
                name: item.sender?.name || item.sender_name || "Amic desconegut"
            },
            message: item.message || item.note || "Sense missatge específic",
            created_at: item.created_at || new Date().toISOString()
        }));

    } catch (error) {
        console.error("Error al cargar recomendaciones:", error);
        return [];
    }
}

// Función mejorada para renderizar recomendaciones
async function renderRecommendations(recommendations) {
    const container = document.getElementById('recommendations-container');
    if (!container) return;

    // 1. Obtener la lista actual de juegos del usuario
    let userGames = [];
    try {
        const response = await fetch('/game-list', {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        if (response.ok) {
            const data = await response.json();
            userGames = data.games || [];
        }
    } catch (error) {
        console.error("Error al obtener la lista de juegos:", error);
    }

    // 2. Filtrar recomendaciones para excluir juegos ya en la lista
    const filteredRecommendations = recommendations.filter(rec => {
        return !userGames.some(game => game.id === rec.game.id);
    });

    // Limpiar el contenedor
    container.innerHTML = '';

    // Mostrar mensaje si no hay recomendaciones válidas
    if (filteredRecommendations.length === 0) {
        const message = recommendations.length === 0 ?
            'Quan un amic et recomani un joc, apareixerà aquí.' :
            'Tots els jocs recomanats ja estan a la teva llista.';

        container.innerHTML = `
            <div class="list-card" style="text-align: center; padding: 2rem;">
                <p style="font-size: 1.125rem; color: #fff; margin-bottom: 0.5rem;">
                    ${recommendations.length === 0 ? '📭 No tens recomanacions pendents' : '🎉 Tots els jocs afegits'}
                </p>
                <p style="font-size: 0.875rem; color: #a0a0a0;">${message}</p>
            </div>
        `;
        return;
    }

    // Título de sección
    const title = document.createElement('h3');
    title.style.fontSize = '1.5rem';
    title.style.fontWeight = '600';
    title.style.color = '#ffffff';
    title.style.marginBottom = '1.5rem';
    title.innerHTML = `🎮 Jocs recomanats (${filteredRecommendations.length})`;
    container.appendChild(title);

    // Agrupar recomendaciones por juego
    const recommendationsByGame = filteredRecommendations.reduce((acc, rec) => {
        const gameId = rec.game.id;
        if (!acc[gameId]) {
            acc[gameId] = {
                game: rec.game,
                recommendations: []
            };
        }
        acc[gameId].recommendations.push({
            sender: rec.sender,
            message: rec.message,
            date: rec.created_at
        });
        return acc;
    }, {});

    // Crear tarjeta para cada juego recomendado
    const cardsContainer = document.createElement('div');
    container.appendChild(cardsContainer);

    Object.values(recommendationsByGame).forEach(item => {
        const card = document.createElement('div');
        card.className = 'list-card';
        card.style.marginBottom = '1.5rem';
        card.style.padding = '1.5rem';
        card.dataset.gameId = item.game.id; // Para referencia fácil

        card.innerHTML = `
            <div style="display: flex; gap: 1rem; align-items: flex-start;">
                <div style="flex-shrink: 0;">
                    <img src="${item.game.background_image || 'https://via.placeholder.com/150x200?text=No+Imatge'}" 
                         alt="${item.game.name}"
                         style="width: 96px; height: 96px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4); transition: transform 0.3s ease;">
                </div>
                
                <div style="flex-grow: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <h4 style="font-size: 1.125rem; font-weight: 600; color: #fff; margin-bottom: 0.25rem;">
                                ${item.game.name}
                            </h4>
                            <p style="font-size: 0.875rem; color: #a0a0a0;">
                                Recomanat per ${item.recommendations.length} amic${item.recommendations.length > 1 ? 's' : ''}
                            </p>
                        </div>
                    </div>
                    
                    <div style="max-height: 120px; overflow-y: auto; margin: 0.5rem 0;">
                        ${item.recommendations.map(rec => `
                            <div style="border-left: 2px solid #3b82f6; padding-left: 0.5rem; margin-bottom: 0.5rem;">
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="font-weight: 500; color: #60a5fa;">${rec.sender.name}</span>
                                    <span style="font-size: 0.75rem; color: #6b7280;">
                                        ${new Date(rec.date).toLocaleDateString('ca-ES')}
                                    </span>
                                </div>
                                ${rec.message ? `
                                    <p style="font-size: 0.75rem; color: #d1d5db; font-style: italic; margin-top: 0.25rem;">
                                        "${rec.message}"
                                    </p>
                                ` : ''}
                            </div>
                        `).join('')}
                    </div>
                    
                    <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
                        <button data-game-id="${item.game.id}"
                                style="padding: 0.5rem 1rem; background-color: #10b981; color: white; font-weight: 500; 
                                       border-radius: 0.375rem; border: none; cursor: pointer; transition: background-color 0.3s;"
                                onmouseover="this.style.backgroundColor='#059669'" 
                                onmouseout="this.style.backgroundColor='#10b981'">
                            Afegir a la meva llista
                        </button>
                        
                        <a href="https://rawg.io/games/${item.game.id}" 
                           target="_blank"
                           style="padding: 0.5rem 1rem; background-color: #3b82f6; color: white; font-weight: 500; 
                                  border-radius: 0.375rem; text-decoration: none; display: flex; align-items: center; 
                                  transition: background-color 0.3s;"
                           onmouseover="this.style.backgroundColor='#2563eb'" 
                           onmouseout="this.style.backgroundColor='#3b82f6'">
                            <span style="margin-right: 0.25rem;">🔗</span>
                            Veure a RAWG
                        </a>
                    </div>
                </div>
            </div>
        `;

        // Efecto hover para la imagen
        const img = card.querySelector('img');
        img.onmouseover = () => img.style.transform = 'scale(1.05)';
        img.onmouseout = () => img.style.transform = 'scale(1)';

        // Evento para añadir a la lista
        card.querySelector('button').addEventListener('click', async () => {
            try {
                // Añadir el juego a la lista
                await addGameToList({
                    id: item.game.id,
                    name: item.game.name,
                    background_image: item.game.background_image
                });

                // Eliminar la tarjeta
                card.remove();

                // Actualizar el contador
                const remainingCards = cardsContainer.querySelectorAll('.list-card');
                if (remainingCards.length === 0) {
                    container.innerHTML = `
                        <div class="list-card" style="text-align: center; padding: 2rem;">
                            <p style="font-size: 1.125rem; color: #fff; margin-bottom: 0.5rem;">🎉 Ja no tens recomanacions pendents</p>
                            <p style="font-size: 0.875rem; color: #a0a0a0;">Tots els jocs s'han afegit a la teva llista</p>
                        </div>
                    `;
                } else {
                    title.innerHTML = `🎮 Jocs recomanats (${remainingCards.length})`;
                }

            } catch (error) {
                console.error("Error al añadir el juego:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No s\'ha pogut afegir el joc a la llista',
                    background: '#1e1e1e',
                    color: '#f0f0f0',
                    customClass: { popup: 'swal2-dark' }
                });
            }
        });

        cardsContainer.appendChild(card);
    });
}


// Función para obtener recomendaciones recibidas
async function fetchReceivedRecommendations() {
    try {
        const response = await fetch('/received-recommendations', {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            credentials: 'include'
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || 'Error al cargar recomendaciones');
        }

        return await response.json();

    } catch (error) {
        console.error("Error:", error);

        // Mostrar error al usuario
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'No s\'han pogut carregar les recomanacions',
            background: '#1e1e1e',
            color: '#f0f0f0',
            customClass: { popup: 'swal2-dark' }
        });

        return [];
    }
}


// Función para mostrar recomendaciones recibidas
function renderReceivedRecommendations(recommendations) {
    const container = document.getElementById('recommendations-container');
    if (!container) return;

    function showNoRecommendationsMessage() {
        container.innerHTML = `
            <div class="list-card" style="text-align: center; padding: 2rem;">
                <p style="font-size: 1.125rem; color: #fff; margin-bottom: 0.5rem;">📭 No tens recomanacions pendents</p>
                <p style="font-size: 0.875rem; color: #a0a0a0;">Quan un amic et recomani un joc, apareixerà aquí.</p>
            </div>
        `;
    }

    container.innerHTML = '';
    container.style.width = '100%';

    if (!recommendations || recommendations.length === 0) {
        showNoRecommendationsMessage();
        return;
    }

    const title = document.createElement('h3');
    title.style.fontSize = '1.5rem';
    title.style.fontWeight = '600';
    title.style.color = '#ffffff';
    title.style.marginBottom = '1.5rem';
    title.innerHTML = '🎮 Jocs recomanats pels teus amics';
    container.appendChild(title);

    const cardsContainer = document.createElement('div');
    container.appendChild(cardsContainer);

    recommendations.forEach(rec => {
        if (!rec.game || !rec.sender) return;

        const card = document.createElement('div');
        card.className = 'list-card';
        card.style.marginBottom = '1.5rem';
        card.style.padding = '1.5rem';

        card.innerHTML = `
            <div style="display: flex; gap: 1rem; align-items: flex-start;">
                <div style="flex-shrink: 0;">
                    <img src="${rec.game.background_image || 'https://via.placeholder.com/150x200?text=No+Imatge'}" 
                         alt="${rec.game.name}"
                         style="width: 96px; height: 96px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);">
                </div>
                <div style="flex-grow: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <h4 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.25rem;">
                                <a href="https://rawg.io/games/${rec.game.id}" target="_blank"
                                   style="color: #ffffff; text-decoration: none; transition: 0.3s;"
                                   onmouseover="this.style.color='#60a5fa'"
                                   onmouseout="this.style.color='#ffffff'">
                                    ${rec.game.name}
                                </a>
                            </h4>
                            <p style="font-size: 0.875rem; color: #a0a0a0;">
                                Recomanat per <span style="font-weight: 500; color: #60a5fa;">${rec.sender.name}</span>
                            </p>
                        </div>
                        <span style="font-size: 0.75rem; color: #6b7280;">
                            ${new Date(rec.created_at).toLocaleDateString('ca-ES')}
                        </span>
                    </div>
                    <div style="display: flex; gap: 0.75rem; margin-top: 1rem;">
                        <button data-game-id="${rec.game.id}"
                                style="padding: 0.6rem 1.2rem; background-color: #10b981; color: white; font-weight: 600; border-radius: 0.5rem; border: none; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 6px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 0.5rem;"
                                onmouseover="this.style.backgroundColor='#059669'" 
                                onmouseout="this.style.backgroundColor='#10b981'">
                            ✅ Afegir a la meva llista
                        </button>
                        <button data-recommendation-id="${rec.id}" 
                                style="padding: 0.6rem 1.2rem; background-color: #ef4444; color: white; font-weight: 600; border-radius: 0.5rem; border: none; cursor: pointer; transition: 0.3s; box-shadow: 0 2px 6px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 0.5rem;"
                                onmouseover="this.style.backgroundColor='#b91c1c'"
                                onmouseout="this.style.backgroundColor='#ef4444'">
                            🗑️ Eliminar
                        </button>
                    </div>
                </div>
            </div>
        `;

        const img = card.querySelector('img');
        img.style.transition = 'transform 0.3s ease';
        img.onmouseover = () => img.style.transform = 'scale(1.05)';
        img.onmouseout = () => img.style.transform = 'scale(1)';

        card.querySelector('button[data-game-id]').addEventListener('click', () => {
            addGameToList({
                id: rec.game.id,
                name: rec.game.name,
                background_image: rec.game.background_image
            }, card); // Pasamos la tarjeta como segundo parámetro
        });

        card.querySelector('button[data-recommendation-id]').addEventListener('click', async (e) => {
            const recommendationId = e.target.dataset.recommendationId;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            const result = await Swal.fire({
                title: 'Confirmar eliminació',
                text: 'Segur que vols eliminar aquesta recomanació?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancel·lar',
                customClass: { popup: 'swal2-dark' }
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/api/recommendations/${recommendationId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });

                    if (!response.ok) {
                        throw new Error('No s\'ha pogut eliminar la recomanació.');
                    }

                    await Swal.fire({
                        title: 'Eliminada!',
                        text: 'La recomanació ha estat eliminada.',
                        icon: 'success',
                        customClass: { popup: 'swal2-dark' }
                    });

                    card.remove();
                    if (cardsContainer.children.length === 0) {
                        showNoRecommendationsMessage();
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Error',
                        text: error.message || 'Error eliminant la recomanació.',
                        icon: 'error',
                        customClass: { popup: 'swal2-dark' }
                    });
                }
            }
        });

        cardsContainer.appendChild(card);
    });
}
