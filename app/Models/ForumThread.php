<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumThread extends Model
{
    protected $fillable = ['title', 'user_id'];

    public function posts(): HasMany
    {
        return $this->hasMany(ForumPost::class);
    }
}
