@extends('layouts.app')

@section('content')
<div class="container">
    <h2>👥 Els meus amics</h2>
    <ul id="friends-list"></ul>
</div>

<script>
    fetch("/api/friends")
        .then(res => res.json())
        .then(data => {
            const ul = document.getElementById("friends-list");
            data.forEach(friend => {
                const li = document.createElement("li");
                li.textContent = friend.name;
                ul.appendChild(li);
            });
        });
</script>
@endsection