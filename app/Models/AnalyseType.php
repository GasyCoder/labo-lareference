<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnalyseType extends Model
{
    use HasFactory, SoftDeletes;
    public $timestamps = true;
    protected $fillable = ['name', 'libelle', 'status'];

    public function analyse()
    {
        return $this->hasMany(Analyse::class);
    }


    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }
}
