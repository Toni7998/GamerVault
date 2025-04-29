@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h2 class="text-4xl font-bold mt-4 mb-4 text-center text-gray-800">🎯 Recomanacions del dia</h2>

    <ul id="recommendations-list" class="space-y-6">
        <!-- Les recomanacions es carregaran aquí -->
    </ul>
</div>

<script>
    const RAWG_API_KEY = "a6932e9255e64cf98bfa75abde510c5d";

    const currentDay = new Date().getDay();

    fetch("/api/recommendations?day=" + currentDay)
        .then(res => res.json())
        .then(recommendations => {
            const ul = document.getElementById("recommendations-list");

            recommendations.forEach((rec) => {
                fetch(`https://api.rawg.io/api/games?key=${RAWG_API_KEY}&page_size=1&search=${encodeURIComponent(rec.game)}`)
                    .then(res => res.json())
                    .then(gameData => {
                        const game = gameData.results?.[0];
                        if (!game || !game.background_image) return; // ❗️Filtra si no té imatge

                        const li = document.createElement("li");
                        li.className = `
                            bg-white rounded-2xl shadow-md hover:shadow-lg transition-shadow 
                            p-4 flex flex-col sm:flex-row gap-4 items-start sm:items-center
                            animate-fade-in
                        `;

                        li.innerHTML = `
                            <img src="${game.background_image}" 
                                 alt="${game.name}" 
                                 class="w-full sm:w-40 h-24 object-cover rounded-lg shadow-sm" />

                            <div class="flex-1">
                                <h3 class="text-2xl font-semibold mb-2 text-gray-800">
                                    ${game.name} <span class="text-sm text-gray-500">— Recomanat per ${rec.sender}</span>
                                </h3>
                                <div class="text-sm text-gray-600 space-y-1">
                                    <p>🎮 <span class="font-medium">Plataformes:</span> 
                                        ${game.platforms?.map(p => p.platform.name).join(", ") || 'Desconegudes'}
                                    </p>
                                    <p>📅 <span class="font-medium">Data de llançament:</span> 
                                        ${game.released ? new Date(game.released).toLocaleDateString('ca-ES') : 'Desconeguda'}
                                    </p>
                                    <p>⭐ <span class="font-medium">Valoració RAWG:</span> ${game.rating ?? 'Sense valoració'}</p>
                                </div>
                                <a href="https://rawg.io/games/${game.slug}" 
                                   target="_blank" 
                                   class="mt-3 inline-block text-blue-600 hover:underline font-medium text-sm">
                                    🔗 Veure a RAWG
                                </a>
                            </div>
                        `;

                        ul.appendChild(li);
                    });
            });
        })
        .catch(error => {
            console.error("Error carregant recomanacions:", error);
            const ul = document.getElementById("recommendations-list");
            ul.innerHTML = `<li class="text-red-500 text-center">No s'han pogut carregar les recomanacions. Torna-ho a provar més tard.</li>`;
        });
</script>

@endsection