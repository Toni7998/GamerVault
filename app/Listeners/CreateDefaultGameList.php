<?php

namespace App\Listeners;

use App\Models\GameList;
use Illuminate\Auth\Events\Registered;

class CreateDefaultGameList
{
    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        $user = $event->user;
        $user->gameList()->create([
            'name' => 'Llista de ' . $user->name,
        ]);
    }
}
