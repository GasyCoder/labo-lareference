<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = [
        'user_id',
        'sexe',
        'adresse',
        'ville',
        'province',
        'photo_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
