<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameList extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function games()
    {
        return $this->hasMany(Game::class);
    }
}
