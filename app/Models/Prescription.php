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

        'secretaire_id',
        'patient_id',
        'prescripteur_id',
        'nouveau_prescripteur_nom',
        'patient_type',
        'age',
        'unite_age',
        'poids',
        'renseignement_clinique',
        'remise',
        'status',
        'is_archive'
    ];

    protected $casts = [
        'poids' => 'decimal:2',
        'remise' => 'decimal:2',
        'is_archive' => 'boolean'
    ];

    public static function countArchived()
    {
        return static::where('is_archive', true)
                    ->where('status', self::STATUS_ARCHIVE)
                    ->count();
    }

    // Dans votre méthode archive() du modèle Prescription
    public function archive()
    {
        return DB::transaction(function () {
            DB::table('analyse_prescriptions')
                ->where('prescription_id', $this->id)
                ->update(['status' => AnalysePrescription::STATUS_ARCHIVE]);

            $result = $this->update([
                'status' => self::STATUS_ARCHIVE,
                'is_archive' => true
            ]);

            // Émettre l'événement pour mettre à jour le compteur
            if ($result) {
                event('archive-counter-updated');
            }

            return $result;
        });
    }

    // Dans votre méthode unarchive()
    public function unarchive()
    {
        return DB::transaction(function () {
            DB::table('analyse_prescriptions')
                ->where('prescription_id', $this->id)
                ->update(['status' => AnalysePrescription::STATUS_VALIDE]);

            $result = $this->update([
                'status' => self::STATUS_VALIDE,
                'is_archive' => false
            ]);

            // Émettre l'événement pour mettre à jour le compteur
            if ($result) {
                event('archive-counter-updated');
            }

            return $result;
        });
    }


    public function analyseResults()
    {
        return $this->hasManyThrough(
            Resultat::class,
            AnalysePrescription::class,
            'prescription_id',
            'analyse_prescription_id'
        );
    }

    // Si vous avez besoin d'accéder aux détails des analyses :
    public function analysesWithDetails()
    {
        return $this->belongsToMany(Analyse::class, 'analyse_prescriptions')
                    ->using(AnalysePrescription::class)
                    ->withPivot(['prix', 'status', 'resultat', 'is_payer'])
                    ->withTimestamps();
    }

    const STATUS_EN_ATTENTE = 'EN_ATTENTE';
    const STATUS_EN_COURS = 'EN_COURS';
    const STATUS_TERMINE = 'TERMINE';
    const STATUS_VALIDE = 'VALIDE';
    const STATUS_ARCHIVE = 'ARCHIVE';

    // Ajoutez cette relation
    public function prelevements()
    {
        return $this->belongsToMany(Prelevement::class)
                    ->withPivot('prix_unitaire', 'quantite', 'is_payer')
                    ->withTimestamps();
    }

    // Dans le modèle Prescription
    public function hasValidatedResults()
    {
        return $this->resultats()
            ->whereNotNull('validated_by')
            ->whereNotNull('validated_at')
            ->exists();
    }

    public function analyses()
    {
        return $this->belongsToMany(Analyse::class, 'analyse_prescriptions')
                    ->using(AnalysePrescription::class)
                    ->withPivot(['prix', 'is_payer', 'status'])
                    ->withTimestamps();
    }

    public function hasValidatedResultsByBiologiste()
    {
        return $this->resultats()
            ->whereNotNull('validated_by') // Validés par un biologiste
            ->exists();
    }



    public function hasUnvalidatedResults(): bool
    {
        return $this->resultats()
            ->whereNull('validated_by')
            ->exists();
    }

    public function allAnalysesCompleted(): bool
    {
        return $this->analyses()
            ->wherePivot('status', 'TERMINE')
            ->count() === $this->analyses()->count();
    }

    // Ajoutez cette méthode pour calculer le total
    public function calculateTotal()
    {
        $totalAnalyses = $this->analyses->sum('pivot.prix');
        $totalPrelevements = $this->prelevements->sum('pivot.prix_unitaire');

        return $totalAnalyses + $totalPrelevements;
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function prescripteur()
    {
        return $this->belongsTo(User::class, 'prescripteur_id');
    }

    public function secretaire()
    {
        return $this->belongsTo(User::class, 'secretaire_id');
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
        } elseif ($analyseStatuses->every(function ($status) {
            return $status === AnalysePrescription::STATUS_VALIDE;
        })) {
            $this->status = self::STATUS_VALIDE;
        } elseif ($analyseStatuses->every(function ($status) {
            return $status === AnalysePrescription::STATUS_TERMINE || $status === AnalysePrescription::STATUS_VALIDE;
        })) {
            $this->status = self::STATUS_TERMINE;
        } else {
            $this->status = self::STATUS_EN_ATTENTE;
        }

        $this->save();
    }


    public function isTermined()
    {
        return $this->status === self::STATUS_TERMINE;
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
