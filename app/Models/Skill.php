<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;


    protected $fillable = ['profession_id', 'ar', 'fr', 'en', 'value'];

    public function profession()
    {
        return $this->belongsTo(Profession::class);
    }
}
