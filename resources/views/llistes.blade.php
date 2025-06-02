@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <section class="dashboard-header mb-6">
        <h2 class="text-3xl font-semibold text-gray-800">📂 La meva llista</h2>
        <p class="text-gray-600 mt-2">Aquesta és la teva única llista personal de videojocs. 🎮</p>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    </section>

    <!-- Barra de búsqueda -->
    <div class="search-container">
        <input type="text" id="search-input" placeholder="Buscar..." class="search-input">
    </div>

    <!-- Resultats de la cerca -->
    <div id="search-results"
        class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-6">
    </div>

    <br>

    <!-- Contenedor donde se cargará la lista -->
    <section id="lists-container" class="space-y-6">
        {{-- La llista es carregarà aquí via JS --}}
    </section>

    <!-- Notificació (opcional, si vols mostrar missatges) -->
    <div id="notification" class="hidden fixed top-4 left-1/2 transform -translate-x-1/2 bg-green-500 text-white px-6 py-3 rounded-lg shadow-md">
        <p id="notification-message"></p>
    </div>

    <div id="recommendations-container" class="mt-8">
        <!-- Les recomanacions es carregaran aquí -->
    </div>
</div>

<script src="{{ asset('js/llistes.js') }}"></script>
@endsection