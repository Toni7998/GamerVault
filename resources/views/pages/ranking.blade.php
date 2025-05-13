@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h2 class="text-4xl font-bold mt-4 mb-4 text-center text-gray-800">🏆 Jocs més votats</h2>
    <ul id="ranking-list" class="space-y-6"></ul>
</div>

<script>
    const RAWG_API_KEY = "a6932e9255e64cf98bfa75abde510c5d";

    fetch("/api/ranking")
        .then(res => res.json())
        .then(games => {
            const ul = document.getElementById("ranking-list");

            ul.innerHTML = games.map((game, index) => {
                const releaseDate = game.released ?
                    new Date(game.released).toLocaleDateString('ca-ES') :
                    "Per anunciar";

                const platforms = game.platforms?.map(p => p.platform.name).join(", ") || "Desconegudes";
                const rating = game.rating ?? "Sense valoració";

                return `
                    <li class="bg-white rounded-2xl shadow-md hover:shadow-lg transition-shadow 
                               p-4 flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                        <img src="${game.background_image}" 
                             alt="${game.name}" 
                             class="w-full sm:w-40 h-24 object-cover rounded-lg shadow-sm" />

                        <div class="flex-1">
                            <h3 class="text-2xl font-semibold mb-2 text-gray-800">
                                ${index + 1}. ${game.name}
                            </h3>
                            <div class="text-sm text-gray-600 space-y-1">
                                <p>🎮 <span class="font-medium">Plataformes:</span> ${platforms}</p>
                                <p>📅 <span class="font-medium">Llançament:</span> ${releaseDate}</p>
                                <p>⭐ <span class="font-medium">Puntuació RAWG:</span> ${rating}</p>
                            </div>
                            <a href="https://rawg.io/games/${game.slug}" 
                               target="_blank" 
                               class="mt-3 inline-block text-blue-600 hover:underline font-medium text-sm">
                                🔗 Veure a RAWG
                            </a>
                        </div>
                    </li>
                `;
            }).join('');
        })
        .catch(error => {
            console.error("Error carregant el rànquing:", error);
            document.getElementById("ranking-list").innerHTML = `
                <li class="text-center text-red-600 font-medium">Error carregant el rànquing. Torna-ho a intentar més tard.</li>
            `;
        });
</script>
@endsection