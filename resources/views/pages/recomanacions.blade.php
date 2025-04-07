@extends('layouts.app')

@section('content')
<div class="container">
    <h2>🎯 Recomanacions</h2>
    <ul id="recommendations-list"></ul>
</div>

<script>
    fetch("/api/recommendations")
        .then(res => res.json())
        .then(data => {
            const ul = document.getElementById("recommendations-list");
            data.forEach(r => {
                const li = document.createElement("li");
                li.innerHTML = `<strong>${r.sender}</strong> recomana <em>${r.game}</em>`;
                ul.appendChild(li);
            });
        });
</script>
@endsection