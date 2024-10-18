<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BacteryFamily extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'antibiotics', 'bacteries', 'status'];
    public $timestamps = true;
    protected $casts = [
        'antibiotics' => 'array',
        'bacteries' => 'array',
        'status' => 'boolean',
    ];

    public function setAntibioticsAttribute($value)
    {
        $this->attributes['antibiotics'] = is_string($value)
            ? json_encode(array_map('trim', explode(',', $value)))
            : json_encode($value);
    }

    public function setBacteriesAttribute($value)
    {
        $this->attributes['bacteries'] = is_string($value)
            ? json_encode(array_map('trim', explode(',', $value)))
            : json_encode($value);
    }

    public function getAntibioticsAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function getBacteriesAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }
}
