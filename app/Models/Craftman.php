<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Craftman extends Model
{
    use HasFactory;

    protected $table = 'craftsman';


    /**
     * The legal status of craftman.
     *
     * @var string
     */
    CONST LEGAL_STATUS_UNVERIFIED = 0;
    CONST LEGAL_STATUS_COMPANY = 1;
    CONST LEGAL_STATUS_AUTO_ENTREPRENEUR = 2;


    CONST STEP_BASIC_INFO = 1;
    CONST STEP_DOCS = 2;
    CONST STEP_COMPLETE = 3;
    

    /**
     * User status account.
     */
    CONST PROFILE_INCOMPLETE = 1;
    CONST PROFILE_COMPLETE = 2;
    CONST PROFILE_APPROVED = 3;
    CONST PROFILE_REJECTED = 4;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'profession',
        'skills',
        'phone',
        'legal_status',
        'status',
        'user_id',
        'current_step',
        'experience_years',
    ];

     public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
