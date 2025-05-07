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
    container.innerHTML = ''; // Limpiar

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

    el.innerHTML = `
        <h3 class="font-semibold text-lg text-gray-800">${data.name}</h3>
        <p class="text-sm text-gray-500 mt-2">Jocs afegits: ${data.games.length}</p>
    `;

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
    container.className = "grid grid-cols-1 md:grid-cols-2 gap-6"; // 2 columnas en md+
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
        const card = document.createElement("div");
        card.className = `
            relative rounded-2xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 opacity-0
        `;

        card.innerHTML = `
            
            <div class="absolute inset-0 bg-black/60 text-white flex flex-col justify-between p-4">
                <div>
                    <h4 class="text-xl font-semibold">${game.name}</h4>
                    ${game.released ? `<p class="text-sm text-gray-300">Publicat: ${game.released}</p>` : ''}
                </div>

 <img src="${game.background_image || 'https://via.placeholder.com/400x200?text=Sense+imatge'}"
                alt="${game.name}"
                class="w-full h-60 object-cover">
            </div>

                <button class="mt-4 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg self-start transition-all">
                    Afegir 🎮
                </button>
        `;

        card.querySelector("button").addEventListener("click", () => addGameToList(game));
        container.appendChild(card);

        requestAnimationFrame(() => {
            card.classList.remove('opacity-0');
            card.classList.add('opacity-100', 'transition-opacity', 'duration-300');
        });
    });
}


function addGameToList(game) {
    fetch('/game-list', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ game })
    })
        .then(res => {
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
