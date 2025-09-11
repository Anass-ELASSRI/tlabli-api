<?php

namespace App\Models;

use App\Enums\UserRoles;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;


class User extends Authenticatable
{
    use HasFactory, Notifiable;

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
        'verified_at' => 'datetime',
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

    public function isVerfied()
    {
        return $this->is_verified;
    }

    public function verifications()
    {
        return $this->hasMany(UserVerification::class);
    }

    public function phoneVerification()
    {
        return $this->hasOne(UserVerification::class)->where('type', 'phone');
    }

    public function tokens()
    {
        return $this->hasMany(UserToken::class);
    }
}
