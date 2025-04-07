@extends('layouts.app')

@section('content')
<div class="container">
    <h2>🏆 Jocs més votats</h2>
    <ul id="ranking-list"></ul>
</div>

<script>
    fetch("/api/ranking")
        .then(res => res.json())
        .then(data => {
            const ul = document.getElementById("ranking-list");
            data.forEach((game, index) => {
                const li = document.createElement("li");
                li.textContent = `${index + 1}. ${game.name} - ${game.votes} vots`;
                ul.appendChild(li);
            });
        });
</script>
@endsection