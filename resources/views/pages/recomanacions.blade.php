@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h2 class="text-4xl font-bold mt-4 mb-4 text-center text-gray-800">🎯 Recomanacions del dia</h2>
    <ul id="recommendations-list" class="space-y-6"></ul>
</div>

<script>
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
                    <li class="bg-white rounded-2xl shadow-md hover:shadow-lg transition-shadow 
                               p-4 flex flex-col sm:flex-row gap-4 items-start sm:items-center animate-fade-in">
                        <img src="${game.background_image}" 
                             alt="${game.name}" 
                             class="w-full sm:w-40 h-24 object-cover rounded-lg shadow-sm" />
                        <div class="flex-1">
                            <h3 class="text-2xl font-semibold mb-2 text-gray-800">
                                ${game.name} 
                            </h3>

                            <h3> <span class="ml-auto text-xs bg-purple-600 text-white px-3 py-1 rounded-full shadow-sm font-medium">
                            👤 Recomanat per ${rec.sender}
                            </span> </h3>

                            <div class="text-sm text-gray-600 space-y-1">
                                <p>🎮 <span class="font-medium">Plataformes:</span> ${platforms}</p>
                                <p>📅 <span class="font-medium">Data de llançament:</span> ${releaseDate}</p>
                                <p>⭐ <span class="font-medium">Valoració RAWG:</span> ${rating}</p>
                            </div>
                            <a href="https://rawg.io/games/${game.slug}" 
                               target="_blank" 
                               class="mt-3 inline-block text-blue-600 hover:underline font-medium text-sm">
                                🔗 Veure a RAWG
                            </a>
                        </div>
                    </li>
                `;
            }));

            ul.innerHTML = listItems.filter(Boolean).join('');
        })
        .catch(error => {
            console.error("Error carregant recomanacions:", error);
            ul.innerHTML = `<li class="text-red-500 text-center">No s'han pogut carregar les recomanacions. Torna-ho a provar més tard.</li>`;
        });
</script>
@endsection