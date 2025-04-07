<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    public function gameList()
    {
        return $this->belongsTo(GameList::class);
    }
    
}
