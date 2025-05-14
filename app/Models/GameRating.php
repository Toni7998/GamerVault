<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameRating extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'game_id', 'rating'];

    // Relación con el modelo User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con el modelo Game
    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
