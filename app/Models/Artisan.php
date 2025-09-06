<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;


class Artisan extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'artisans';



    // Optional: define media collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('identity_docs')->singleFile();
        $this->addMediaCollection('profile_picture')->singleFile();
        $this->addMediaCollection('certifications');
    }



    /**
     * The legal status of artisan.
     *
     * @var string
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'profession',
        'skills',
        'contact',
        'user_id',
        'experience_years',
        'contact',
        'languages',
    ];

    protected $casts = [
        'skills' => 'array'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // public function requests()
    // {
    //     return $this->hasMany(ArtisanRequest::class);
    // }

    // public function hasPendingRequestFrom($client)
    // {
    //     return $this->requests()->where('user_id', $client->id)->whereIn('status', ArtisanRequest::WORK_IN_STATUS)->exists();
    // }
}
