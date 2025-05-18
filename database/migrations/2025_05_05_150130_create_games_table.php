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
        Schema::create('games', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); // Usamos el ID de la API RAWG
            $table->string('name');
            $table->date('released')->nullable();
            $table->string('background_image')->nullable();
            $table->string('slug')->nullable();
            $table->string('website_url')->nullable(); 

            // Opcionales per la app
            $table->string('platform')->nullable();
            $table->boolean('completed')->default(false);
            $table->text('comment')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
