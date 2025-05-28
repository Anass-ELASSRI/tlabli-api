<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;


class Craftsman extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;



    // Optional: define media collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('identity_docs')->singleFile();
        $this->addMediaCollection('profile_picture')->singleFile();
        $this->addMediaCollection('legal_docs');
        $this->addMediaCollection('past_projects');
    }



    /**
     * The legal status of craftsman.
     *
     * @var string
     */
    const LEGAL_STATUS_UNVERIFIED = 0;
    const LEGAL_STATUS_COMPANY = 1;
    const LEGAL_STATUS_AUTO_ENTREPRENEUR = 2;


    const STEP_BASIC_INFO = 1;
    const STEP_DOCS = 2;
    const STEP_COMPLETE = 3;


    /**
     * User status account.
     */
    const PROFILE_INCOMPLETE = 1;
    const PROFILE_COMPLETE = 2;
    const PROFILE_APPROVED = 3;
    const PROFILE_REJECTED = 4;
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
        'city',
        'languages',
        'social_links',
        'bio',
        'rating',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function requests()
    {
        return $this->hasMany(CraftsmanRequest::class);
    }

    public function hasPendingRequestFrom($client)
    {
        return $this->requests()->where('user_id', $client->id)->whereIn('status', CraftsmanRequest::WORK_IN_STATUS)->exists();
    }
}
