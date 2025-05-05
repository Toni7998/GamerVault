<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    public function lists()
    {
        return $this->belongsToMany(GameList::class)->withTimestamps();
    }
}
