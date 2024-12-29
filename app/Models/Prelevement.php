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
        'quantite',
        'is_active'
    ];

    protected $casts = [
        'prix' => 'decimal:2',
        'quantite' => 'integer',
        'is_active' => 'boolean'
    ];

    // Relations
    public function prescriptions()
    {
        return $this->belongsToMany(Prescription::class)
            ->withPivot(['prix_unitaire', 'quantite', 'is_payer'])
            ->withTimestamps();
    }

    // Scopes
    public function scopeActif($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDisponible($query)
    {
        return $query->where('quantite', '>', 0);
    }

    // Accessors & Mutators
    public function getPrixFormateAttribute()
    {
        return number_format($this->prix, 2) . ' Ar';
    }

    public function estDisponible(): bool
    {
        return $this->quantite > 0 && $this->is_active;
    }
}
