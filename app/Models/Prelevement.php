<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prelevement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nom',
        'description',
        'prix',
        'is_active',
        'quantite'
    ];

    protected $casts = [
        'prix' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // Relation avec les prescriptions
    public function prescriptions()
    {
        return $this->belongsToMany(Prescription::class)
                    ->withPivot(['prix_unitaire', 'quantite'])
                    ->withTimestamps();
    }

    // Scope pour les prélèvements actifs
    public function scopeActif($query)
    {
        return $query->where('is_active', true);
    }

    // Méthode pour formater le prix
    public function getPrixFormate()
    {
        return number_format($this->prix, 2, ',', ' ') . ' Ariary';
    }
}
