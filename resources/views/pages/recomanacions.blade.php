@extends('layouts.app')

@section('content')
<div id="recommendations-container" class="container mx-auto px-4 py-8">
    <h2 class="text-4xl font-bold mt-4 mb-4 text-center text-gray-800">🎯 Recomanacions del dia</h2>
    <ul id="recommendations-list" class="space-y-6"></ul>
</div>

<!-- JS I SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const RAWG_API_KEY = "a6932e9255e64cf98bfa75abde510c5d";
        const currentDay = new Date().getDay();
        const ul = document.getElementById("recommendations-list");

        fetch(`/api/recommendations?day=${currentDay}`)
            .then(res => res.json())
            .then(async recommendations => {
                const listItems = await Promise.all(recommendations.map(async rec => {
                    const res = await fetch(`https://api.rawg.io/api/games?key=${RAWG_API_KEY}&page_size=1&search=${encodeURIComponent(rec.game)}`);
                    const gameData = await res.json();
                    const game = gameData.results?.[0];

                    if (!game?.background_image) return null;

                    const releaseDate = game.released ?
                        new Date(game.released).toLocaleDateString('ca-ES') :
                        "Desconeguda";

                    const platforms = game.platforms?.map(p => p.platform.name).join(", ") || "Desconegudes";
                    const rating = game.rating ?? "Sense valoració";

                    return `
    <li id="rec-${rec.id}" class="list-card bg-white rounded-2xl shadow-md hover:shadow-lg transition-shadow
                                  p-5 flex flex-col sm:flex-row gap-6 items-start sm:items-center animate-fade-in">

        <img src="${game.background_image}"
             alt="${game.name}"
             class="w-full sm:w-64 h-40 object-cover rounded-xl shadow-sm" />

        <div class="flex-1 space-y-4 text-gray-700">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <h3 class="text-2xl font-bold text-gray-800">${game.name}</h3>
                <span class="text-xs bg-purple-600 text-white px-3 py-1 rounded-full shadow-sm font-medium">
                    👤 Recomanat per ${rec.sender}
                </span>
            </div>

            <ul class="text-sm space-y-1">
                <li>🎮 <span class="font-medium">Plataformes:</span> ${platforms}</li>
                <li>📅 <span class="font-medium">Data de llançament:</span> ${releaseDate}</li>
                <li>⭐ <span class="font-medium">Valoració RAWG:</span> ${rating}</li>
            </ul>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-start gap-3 border-t border-gray-200 mt-4 pt-4">
                <a href="https://rawg.io/games/${game.slug || ''}"
                   target="_blank"
                   class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg transition font-medium text-center">
                    🔗 Veure a RAWG
                </a>

                <br><br>

                <button
    class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded-lg transition font-medium"
    data-recommendation-id="${rec.id}"
    onclick='addGameToList(${JSON.stringify(game)}, document.getElementById("rec-${rec.id}"), ${rec.id})'>
    ➕ Afegir a la teva llista
</button>

            </div>
        </div>
    </li>`;
                }));

                ul.innerHTML = listItems.filter(Boolean).join('');
            })
            .catch(error => {
                console.error("Error carregant recomanacions:", error);
                ul.innerHTML = `<li class="text-red-500 text-center">No s'han pogut carregar les recomanacions. Torna-ho a provar més tard.</li>`;
            });
    });

    /**
     * Función para agregar juegos a la lista y eliminar la recomendación
     * @param {*} game 
     * @param {HTMLElement} [recommendationCard] - Elemento de la tarjeta de recomendación a eliminar
     */
    async function addGameToList(game, recommendationCard, recommendationId) {
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
            const rawgResponse = await fetch(`/rawg/details/${game.id}`);
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
</script>
@endsection