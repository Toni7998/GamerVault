@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Ajustamos el margen superior y quitamos el margen inferior -->
    <h2 class="text-4xl font-bold mt-4 mb-4 text-center text-gray-800">🏆 Jocs més votats</h2>

    <ul id="ranking-list" class="space-y-6">
        <!-- Els jocs es carregaran aquí -->
    </ul>
</div>

<script>
    const RAWG_API_KEY = "a6932e9255e64cf98bfa75abde510c5d";

    fetch("/api/ranking")
        .then(res => res.json())
        .then(games => {
            const ul = document.getElementById("ranking-list");

            games.forEach((game, index) => {
                const li = document.createElement("li");

                // Format de la data si existeix
                const releaseDate = game.released ?
                    new Date(game.released).toLocaleDateString('ca-ES') :
                    "Per anunciar";

                li.className = `
                    bg-white rounded-2xl shadow-md hover:shadow-lg transition-shadow 
                    p-4 flex flex-col sm:flex-row gap-4 items-start sm:items-center
                `;

                li.innerHTML = `
                    <img src="${game.background_image}" 
                         alt="${game.name}" 
                         class="w-full sm:w-40 h-24 object-cover rounded-lg shadow-sm" />

                    <div class="flex-1">
                        <h3 class="text-2xl font-semibold mb-2 text-gray-800">
                            ${index + 1}. ${game.name}
                        </h3>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p>🎮 <span class="font-medium">Plataformes:</span> 
                                ${game.platforms?.map(p => p.platform.name).join(", ") || 'Desconegudes'}
                            </p>
                            <p>📅 <span class="font-medium">Llançament:</span> ${releaseDate}</p>
                            <p>⭐ <span class="font-medium">Puntuació RAWG:</span> ${game.rating ?? 'Sense valoració'}</p>
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
</script>

@endsection