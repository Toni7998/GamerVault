@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Les meves llistes</h2>
    <div id="lists-container" class="lists-grid"></div>

    <button onclick="showNewListForm()">➕ Nova Llista</button>

    <div id="new-list-form" style="display:none;">
        <input type="text" id="newListName" placeholder="Nom de la llista">
        <button onclick="createList()">Crear</button>
    </div>
</div>

<script src="{{ asset('js/dashboard.js') }}"></script>
@endsection