<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Paiement extends Model
{
    use HasFactory, SoftDeletes;
    public $timestamps = true;
    protected $fillable = ['prescription_id', 'montant', 'mode_paiement', 'recu_par'];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function recepteur()
    {
        return $this->belongsTo(User::class, 'recu_par');
    }
}
