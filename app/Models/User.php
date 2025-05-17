<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\GameList;
use App\Models\Friendship;
use Illuminate\Support\Facades\DB;
use App\Models\UserGame;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the game list associated with the user.
     */
    public function gameList()
    {
        return $this->hasOne(GameList::class, 'user_id'); // Especifica user_id como clave foránea
    }

    /**
     * Get all friend requests sent by this user.
     */
    public function sentFriendRequests()
    {
        return $this->hasMany(Friendship::class, 'sender_id')
            ->where('status', 'pending');
    }

    /**
     * Get all friend requests received by this user.
     */
    public function receivedFriendRequests()
    {
        return $this->hasMany(Friendship::class, 'receiver_id')
            ->where('status', 'pending');
    }

    /**
     * Get all accepted friends of this user.
     */
    public function friends()
    {
        return $this->belongsToMany(User::class, 'friendships', 'sender_id', 'receiver_id')
            ->wherePivot('status', 'accepted')
            ->withPivot('status');
    }

    /**
     * Get the combined list of friends (both sent and accepted).
     */
    public function allFriends()
    {
        return User::whereIn('id', function ($query) {
            $query->selectRaw('CASE 
                WHEN sender_id = ? THEN receiver_id 
                ELSE sender_id 
            END', [$this->id])
                ->from('friendships')
                ->where(function ($q) {
                    $q->where('sender_id', $this->id)
                        ->orWhere('receiver_id', $this->id);
                })
                ->where('status', 'accepted');
        });
    }

    public function friendships()
    {
        return $this->hasMany(Friendship::class, 'sender_id')->orWhere('receiver_id', $this->id);
    }

    public function userGames()
    {
        return $this->hasMany(UserGame::class);
    }

    public function receivedRecommendations()
    {
        return $this->hasMany(GameRecommendation::class, 'receiver_id');
    }
}
