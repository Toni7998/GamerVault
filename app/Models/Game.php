<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\UserGame;

class Game extends Model
{
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'name',
        'slug',  // Añadir slug al fillable
        'released',
        'background_image',
        'platform',  // Mantener el campo platform
        'completed',
    ];


    // Relación con las valoraciones de los juegos
    public function ratings()
    {
        return $this->hasMany(GameRating::class, 'game_id');
    }

    public function userGames()
    {
        return $this->hasMany(UserGame::class);
    }

    public function gameLists()
    {
        return $this->belongsToMany(GameList::class, 'game_game_list');
    }
}
