<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVerification extends Model
{
    use HasFactory;
    
    const TYPE_PHONE = 'phone';
    const TYPE_EMAIL = 'email';
    const TYPE_2FA = '2fa';




    protected $fillable = [
        'type', 'code', 'attempts', 'last_attempt_at', 'expires_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
