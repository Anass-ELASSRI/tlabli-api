<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtisanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->user->full_name,
            'email'      => $this->email,
            'phone'      => $this->phone ?? $this->user->phone,
            'address'    => $this->address,
            'profession' => $this->profession,
            'rating'     => $this->rating,
            'languages'  => $this->languages,
            'social'     => json_decode($this->social_links),
            'skills'     => $this->skills,
            'city'     => $this->city,
            'bio'     => $this->bio,
            'experience_years'     => $this->experience_years,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
