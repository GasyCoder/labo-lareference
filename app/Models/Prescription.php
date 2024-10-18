<?php

namespace App\Models;

use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prescription extends Model
{
    use HasFactory, SoftDeletes;
    public $timestamps = true;
    protected $fillable = [

        'patient_id',
        'prescripteur_id',
        'nouveau_prescripteur_nom',
        'patient_type',
        'age',
        'unite_age',
        'poids',
        'renseignement_clinique',
        'remise',
        'status'
    ];

    protected $casts = [
        'poids' => 'decimal:2',
        'remise' => 'decimal:2',
    ];

    const STATUS_EN_ATTENTE = 'EN_ATTENTE';
    const STATUS_EN_COURS = 'EN_COURS';
    const STATUS_TERMINE = 'TERMINE';
    const STATUS_VALIDE = 'VALIDE';
    const STATUS_ARCHIVE = 'ARCHIVE';

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function prescripteur()
    {
        return $this->belongsTo(User::class, 'prescripteur_id');
    }

    public function analyses()
    {
        return $this->belongsToMany(Analyse::class, 'analyse_prescriptions')
                    ->using(AnalysePrescription::class)
                    ->withPivot('prix', 'status')
                    ->withTimestamps();
    }

    public function archivePrescription()
    {
        return DB::transaction(function () {
            // Mise à jour du statut dans la table analyse_prescriptions
            $this->analyses()->each(function ($analyse) {
                $analyse->pivot->status = AnalysePrescription::STATUS_TERMINE;
                $analyse->pivot->save();
            });

            // Archiver la prescription
            $this->status = self::STATUS_ARCHIVE;
            $this->save();

            // Soft delete de la prescription
            $this->delete();

            return $this;
        });
    }

    public function updateStatus()
    {
        $analyseStatuses = $this->analyses()->pluck('analyse_prescriptions.status');

        if ($analyseStatuses->contains(AnalysePrescription::STATUS_EN_COURS)) {
            $this->status = self::STATUS_EN_COURS;
        } elseif ($analyseStatuses->count() > 0 && $analyseStatuses->every(function ($status) {
            return $status === AnalysePrescription::STATUS_TERMINE;
        })) {
            $this->status = self::STATUS_TERMINE;
        } else {
            $this->status = self::STATUS_EN_ATTENTE;
        }

        $this->save();
    }

    public function archive()
    {
        $this->status = self::STATUS_ARCHIVE;
        $this->save();
    }

    public function isArchived()
    {
        return $this->status === self::STATUS_ARCHIVE;
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    public function resultats()
    {
        return $this->hasMany(Resultat::class);
    }
}
