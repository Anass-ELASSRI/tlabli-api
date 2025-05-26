<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    /**
     * The type of users.
     *
     * @var string
     */


    const ROLE_ADMIN = 1;
    const ROLE_USER = 2;
    const ROLE_CRAFTMAN = 3;

    const STATUS_PENDING = 1;
    const STATUS_ACTIVE = 2;
    const STATUS_SUSPENDED = 3;







    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_verified',
        'legal_status',
        'status',
        'current_step',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'email_verified_at',
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


    // Scope for Craftsmen
    public function scopeCraftmen(Builder $query): Builder
    {
        return $query->where('role', User::ROLE_CRAFTMAN);
    }

    // Scope for Clients
    public function scopeClients(Builder $query): Builder
    {
        return $query->where('role', SELF::ROLE_USER);
    }


    public function craftman()
    {
        return $this->hasOne(Craftman::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
