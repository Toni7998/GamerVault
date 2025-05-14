
//  Hacemos que cuando arranca la web se haga lo que esta dentro del código
document.addEventListener('DOMContentLoaded', function () {
    fetchUserGameList();
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


function generateSlug(name) {
    return name
        .toLowerCase()
        .normalize("NFD").replace(/[\u0300-\u036f]/g, "") // quitar acentos
        .replace(/[^a-z0-9\s-]/g, '') // quitar símbolos no alfanuméricos
        .trim()
        .replace(/\s+/g, '-') // reemplazar espacios por guiones
        .replace(/-+/g, '-'); // evitar guiones duplicados
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

        <a href="https://rawg.io/games/${generateSlug(game.name)}" target="_blank"
   class="text-blue-600 text-sm underline mt-2 hover:text-blue-800 text-center">
   🔗 Veure a RAWG
</a>
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
 * Funció per afegir els jocs a la llista
 * @param {*} game 
 */
function addGameToList(game) {
    Swal.fire({
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
    }).then((result) => {
        if (result.isConfirmed) {
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
                    if (!res.ok) {
                        return res.text().then(text => {
                            if (text.startsWith("<!DOCTYPE")) {
                                throw new Error("Error: S'ha rebut una pàgina HTML en lloc de JSON.");
                            }
                            throw new Error("Error al afegir el joc: " + text);
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    console.log("Joc afegit:", data);
                    fetchUserGameList();
                    Swal.fire({
                        icon: 'success',
                        title: 'Afegit!',
                        text: `El joc "${game.name}" ha estat afegit a la teva llista!`,
                        timer: 2000,
                        showConfirmButton: false,
                        background: '#1e1e1e',
                        color: '#f0f0f0'
                    });

                    localStorage.setItem(`game-name-${game.id}`, game.name);
                    localStorage.setItem(`game-image-${game.id}`, game.background_image || '');
                    localStorage.setItem(`game-slug-${game.id}`, game.slug || '');
                    localStorage.setItem(`game-platforms-${game.id}`, game.platforms?.map(p => p.platform.name).join(', ') || '');
                    localStorage.setItem(`game-released-${game.id}`, game.released || '');
                })
                .catch(err => {
                    console.error("Error:", err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.message,
                        background: '#1e1e1e',
                        color: '#f0f0f0'
                    });
                });
        }
    });
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
        })
        .catch(err => {
            console.error("Error al guardar el comentari:", err);
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
