<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profession extends Model
{
    use HasFactory;

    protected $fillable = ['ar', 'fr', 'en', 'value'];

    public function skills()
    {
        return $this->hasMany(Skill::class);
    }
}
