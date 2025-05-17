<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameRecommendation extends Model
{
    protected $fillable = ['sender_id', 'receiver_id', 'game_id'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id');
    }
}
