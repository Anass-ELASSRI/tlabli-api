<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserRoles;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public $incrementing = false; // UUID is not auto-incrementing
    protected $keyType = 'string'; // Key is a string, not integer

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'password',
        'role',
        'phone',
        'status',
        'city',
        'is_verified',
        'is_deleted',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'verified_at',
        'is_deleted',
        'is_verified'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status' => UserStatus::class,
        'role' => UserRoles::class
    ];


    // Scope for Artisans
    public function scopeArtisans(Builder $query): Builder
    {
        return $query->where('role', UserRoles::Artisan);
    }

    // Scope for Clients
    public function scopeClients(Builder $query): Builder
    {
        return $query->where('role', UserRoles::Client);
    }


    public function artisan()
    {
        return $this->hasOne(Artisan::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
