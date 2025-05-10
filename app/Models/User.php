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
            ->withPivot('created_at', 'updated_at')
            ->withTimestamps();
    }

    /**
     * Get the combined list of friends (both sent and accepted).
     */
    public function allFriends()
    {
        $userId = $this->id;

        $sent = DB::table('users')
            ->join('friendships', 'users.id', '=', 'friendships.receiver_id')
            ->where('friendships.sender_id', $userId)
            ->where('friendships.status', 'accepted')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'friendships.sender_id as pivot_sender_id',
                'friendships.receiver_id as pivot_receiver_id'
            );

        $received = DB::table('users')
            ->join('friendships', 'users.id', '=', 'friendships.sender_id')
            ->where('friendships.receiver_id', $userId)
            ->where('friendships.status', 'accepted')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'friendships.sender_id as pivot_sender_id',
                'friendships.receiver_id as pivot_receiver_id'
            );

        return $sent->union($received);
    }
}
