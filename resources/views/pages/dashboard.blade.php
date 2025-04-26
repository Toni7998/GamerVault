@extends('layouts.app')

@section('content')
<div class="container">
    <section class="dashboard-header">
        <h2>📂 Les meves llistes</h2>
        <button onclick="showNewListForm()" class="btn-create-list">➕ Nova Llista</button>
    </section>

    <section id="new-list-form" style="display: none;" class="new-list-form">
        <input
            type="text"
            id="newListName"
            placeholder="Nom de la llista"
            class="input-list-name">
        <button onclick="createList()" class="btn-confirm-create">Crear</button>
    </section>

    <section id="lists-container" class="lists-grid mt-6">
        {{-- Les llistes es carregaran aquí via JS --}}
    </section>
</div>

<script src="{{ asset('js/dashboard.js') }}"></script>
@endsection