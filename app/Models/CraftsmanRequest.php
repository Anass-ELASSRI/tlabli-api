<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CraftsmanRequest extends Model
{
    use HasFactory;

    CONST STATUS_PENDING = 1;
    CONST STATUS_REJECTED = 2;
    CONST STATUS_ACCEPTED = 3;
    CONST STATUS_IN_PROGRESS = 4;
    CONST STATUS_COMPLETED = 5;
    // CONST STATUS_UNDER_REVIEW = 6;
    CONST STATUS_CANCELLED = 7;
    CONST STATUS_EXPIRED = 8;

    CONST WORK_IN_STATUS = [
        self::STATUS_PENDING,
        self::STATUS_ACCEPTED,
        self::STATUS_IN_PROGRESS,
    ];

     protected $fillable = [
        'craftsman_id',
        'user_id',
        'subject',
        'message',
        'status',
    ];

    public function craftsman()
    {
        return $this->belongsTo(Craftsman::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
