<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resultat extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = ['prescription_id', 'analyse_id', 'valeur', 'interpretation', 'validated_by', 'validated_at'];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function analyse()
    {
        return $this->belongsTo(Analyse::class, 'analyse_id');
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
