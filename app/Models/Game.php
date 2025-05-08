<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'id',
        'name',
        'released',
        'background_image',
        'platform',
        'completed',
        'game_list_id'
    ];

    public function list()
    {
        return $this->belongsTo(GameList::class, 'game_list_id');
    }
}
