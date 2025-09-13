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

    const MaxAttempts = 5;
    const ThrottleTimes = [0, 60, 300, 3600, 86400];  // minutes: immediate, 1m, 5m, 1h, 24h


    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $fillable = [
        'type',
        'code',
        'attempts',
        'last_attempt_at',
        'expires_at',
        'send_count'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    public function hasTooManyAttempts()
    {
        return $this->attempts >= self::MaxAttempts;
    }
    public function normalizedSendCount(): int
    {
        if (now()->diffInSeconds($this->created_at) > last(self::ThrottleTimes)) {
            return 0;
        }
        return $this->send_count;
    }

    public static function waitTimeForSendCount(int $sendCount): int
    {
        return self::ThrottleTimes[min($sendCount, count(self::ThrottleTimes) - 1)];
    }

    public function incrementAttempts()
    {
        $this->update([
            'attempts' => $this->attempts + 1,
            'last_attempt_at' => now(),
        ]);
    }
}
