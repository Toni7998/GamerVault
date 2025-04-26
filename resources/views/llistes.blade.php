@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <section class="dashboard-header flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">📂 Les meves llistes</h2>
        <button onclick="showNewListForm()" class="btn-create-list">➕ Nova Llista</button>
    </section>

    <section id="new-list-form" class="new-list-form mb-6 hidden">
        <div class="flex space-x-2">
            <input
                type="text"
                id="newListName"
                placeholder="Nom de la llista"
                class="input-list-name flex-1 border rounded p-2">
            <button onclick="createList()" class="btn-confirm-create bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Crear
            </button>
        </div>
    </section>

    <section id="lists-container" class="lists-grid grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
        {{-- Les llistes es carregaran aquí via JS --}}
    </section>
</div>

<script src="{{ asset('js/llistes.js') }}"></script>
@endsection