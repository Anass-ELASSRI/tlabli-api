<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    use HasFactory;

    protected $table = 'user_tokens';

    protected $fillable = [
        'user_id',
        'refresh_token',
        'ip_address',
        'user_agent',
        'device_name',
        'expires_at',
        'revoked'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Relationship: token belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the token is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
