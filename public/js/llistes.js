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
