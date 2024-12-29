<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Patient extends Model
{
    use HasFactory, SoftDeletes;
    public $timestamps = true;
    protected $fillable = [
        'ref',
        'nom',
        'prenom',
        'date_naissance',
        'sexe',
        'adresse',
        'telephone',
        'email'
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    /**
     * Boot the model.
     * Automatically generates a unique reference number for each patient
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($patient) {
            if (empty($patient->ref)) {
                $year = date('Y');

                // Rechercher la dernière référence de l'année en cours
                $lastPatient = static::whereYear('created_at', $year)
                    ->whereNotNull('ref')  // S'assurer que ref n'est pas null
                    ->where('ref', 'LIKE', "LAB-$year-%")  // Filtrer par le bon format
                    ->orderByDesc('ref')  // Trier par ref décroissante
                    ->first();

                if ($lastPatient && preg_match('/LAB-\d{4}-(\d{2})/', $lastPatient->ref, $matches)) {
                    // Si une référence existe, incrémenter le numéro
                    $sequence = intval($matches[1]) + 1;
                } else {
                    // Sinon, commencer à 1
                    $sequence = 1;
                }

                // Générer la nouvelle référence
                $patient->ref = sprintf('LAB-%s-%02d', $year, $sequence);
            }
        });
    }

    public function getFormattedRefAttribute()
    {
        // Vérifier si la référence existe
        if ($this->ref && preg_match('/LAB-(\d{4})-(\d{2})/', $this->ref, $matches)) {
            $year = substr($matches[1], -2); // Récupérer les deux derniers chiffres de l'année
            $number = intval($matches[2]);   // Convertir le numéro en entier

            // Retourner le format souhaité : #Réf-YY-XXX
            return sprintf('%s-%03d', $year, $number);
        }

        // Si la référence est invalide ou absente
        return 'Non-Défini';
    }


    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function getFullNameAttribute()
    {
        return "{$this->nom} {$this->prenom}";
    }

    /**
     * Scope pour rechercher par référence
     */
    public function scopeByRef($query, $ref)
    {
        return $query->where('ref', $ref);
    }

}
