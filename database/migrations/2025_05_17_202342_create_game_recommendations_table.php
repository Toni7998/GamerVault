<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('game_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade'); // quien recomienda
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade'); // a quien recomienda
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');    // juego recomendado
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_recommendations');
    }
};
