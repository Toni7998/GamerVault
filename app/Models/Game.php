<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\UserGame;

class Game extends Model
{
    protected $fillable = [
        'id',
        'name',
        'slug',  // Añadir slug al fillable
        'released',
        'background_image',
        'platform',  // Mantener el campo platform
        'completed',
        'game_list_id'
    ];

    // Relación con la lista de juegos
    public function list()
    {
        return $this->belongsTo(GameList::class, 'game_list_id');
    }

    // Relación con las valoraciones de los juegos
    public function ratings()
    {
        return $this->hasMany(GameRating::class, 'game_id');
    }

    public function userGames()
    {
        return $this->hasMany(UserGame::class);
    }

}
