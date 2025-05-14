@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h2 class="text-2xl font-bold mb-4">👥 Els meus amics</h2>

    <ul id="friends-list" class="list-disc list-inside bg-white rounded shadow p-4 mb-6">
        <li>Carregant amics...</li>
    </ul>

    <h3 class="text-xl font-semibold mb-2">📩 Sol·licituds rebudes</h3>
    <ul id="friend-requests" class="bg-white rounded shadow p-4 mb-6 hidden">
        <li>Carregant sol·licituds...</li>
    </ul>

    <h3 class="text-xl font-semibold mb-2">🔍 Buscar usuaris per afegir</h3>
    <input type="text" id="search-input" placeholder="Escriu un nom..." class="w-full p-2 border rounded mb-4">

    <ul id="search-results" class="bg-white rounded shadow p-4 hidden"></ul>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    window.csrfToken = '{{ csrf_token() }}';
</script>

<script src="{{ asset('js/friends.js') }}"></script>
@endsection