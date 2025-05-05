document.addEventListener('DOMContentLoaded', function () {

    fetchUserGameList();
});

function fetchUserGameList() {
    fetch('/game-list') // corregido aquí
        .then(response => {
            if (!response.ok) {
                throw new Error('Error carregant la llista');
            }
            return response.json();
        })
        .then(data => {
            renderGameList(data);
        })
        .catch(error => {
            console.error(error);
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
        "transition-all"
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
    if (query.length >= 3) {
        timeout = setTimeout(() => searchGames(query), 500); // debounce
    }
});

function searchGames(query) {
    fetch(`/search-games?query=${encodeURIComponent(query)}`) // <- esta línea corregida
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
    container.innerHTML = '';

    if (!games.length) {
        container.innerHTML = "<p class='text-center text-gray-500 col-span-3'>No s'han trobat jocs.</p>";
        return;
    }

    games.forEach(game => {
        const card = document.createElement("div");
        card.className = "p-4 bg-white border rounded shadow hover:shadow-md transition";

        card.innerHTML = `
            <h4 class="text-lg font-semibold text-gray-800">${game.name}</h4>
            ${game.background_image ? `<img src="${game.background_image}" alt="${game.name}" class="mt-2 rounded w-full h-40 object-cover">` : ""}
            <button class="mt-4 w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" onclick='addGameToList(${JSON.stringify(game)})'>Afegir 🎮</button>
        `;

        container.appendChild(card);
    });
}

function addGameToList(game) {
    fetch('/game-list/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ game })
    })
        .then(res => {
            if (!res.ok) throw new Error("Error afegint joc");
            return res.json();
        })
        .then(data => {
            fetchUserGameList(); // refresca la llista
            showNotification(`Joc "${game.name}" afegit a la teva llista!`);
        })
        .catch(err => {
            console.error(err);
        });
}

function showNotification(message) {
    const notif = document.getElementById("notification");
    const msg = document.getElementById("notification-message");
    msg.textContent = message;
    notif.classList.remove("hidden");
    setTimeout(() => notif.classList.add("hidden"), 3000);
}
