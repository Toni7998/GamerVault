<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('games', function (Blueprint $table) {
            // Primero eliminamos la foreign key
            $table->dropForeign(['game_list_id']);

            // Luego eliminamos la columna
            $table->dropColumn('game_list_id');
        });
    }


    public function down()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->unsignedBigInteger('game_list_id')->nullable();
        });
    }
};
