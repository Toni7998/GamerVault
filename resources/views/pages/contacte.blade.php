@extends('layouts.app')

@section('content')
<main class="container mx-auto py-12 px-6 text-center text-white">
    <h1 class="text-5xl font-bold mb-6 neon-text">📩 Contacta amb nosaltres</h1>
    <p class="text-lg mb-10">Tens preguntes, suggeriments o simplement vols dir hola? 💬 Envia'ns un missatge i et respondrem tan aviat com puguem!</p>

    <!-- Formulario de contacto -->
    <form action="{{ route('contacte') }}" method="POST" class="max-w-xl mx-auto bg-gray-900 p-8 rounded-2xl shadow-lg border border-yellow-400/20">
        @csrf
        <!-- Campos del formulario -->
        <div class="mb-5 text-left">
            <label for="name" class="block text-lg mb-2">👤 Nom:</label>
            <input type="text" id="name" name="name" class="w-full p-3 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="Escriu el teu nom" required>
        </div>

        <div class="mb-5 text-left">
            <label for="email" class="block text-lg mb-2">📧 Correu electrònic:</label>
            <input type="email" id="email" name="email" class="w-full p-3 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="exemple@correu.com" required>
        </div>

        <div class="mb-6 text-left">
            <label for="message" class="block text-lg mb-2">💬 Missatge:</label>
            <textarea id="message" name="message" rows="5" class="w-full p-3 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="Escriu el teu missatge aquí..." required></textarea>
        </div>

        <button type="submit" class="bg-yellow-500 hover:bg-yellow-400 text-black font-semibold px-6 py-3 rounded-lg transition-all duration-300 shadow-md hover:scale-105">
            ✉️ Enviar missatge
        </button>
    </form>

    @if(session('success'))
    <div class="mb-6 text-green-500 font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- Información de contacto -->
    <section class="mt-16">
        <h2 class="text-3xl font-semibold mb-4">📬 Altres maneres de contactar</h2>
        <p class="text-lg">També ens pots escriure directament a: <br>
            <a href="mailto:antonio.ruiz@insbaixcamp.cat" class="text-yellow-400 hover:underline font-medium">contacte@gamervault.com</a>
        </p>
    </section>
</main>
@endsection