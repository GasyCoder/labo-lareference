<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AnalysePrescription extends Pivot
{
    protected $table = 'analyse_prescriptions';

    public $incrementing = true;

    protected $fillable = [
        'prescription_id',
        'analyse_id',
        'prix',
        'status'
    ];

    const STATUS_EN_ATTENTE = 'EN_ATTENTE';
    const STATUS_EN_COURS = 'EN_COURS';
    const STATUS_TERMINE = 'TERMINE';
    const STATUS_VALIDE = 'VALIDE';
    const STATUS_ARCHIVE = 'ARCHIVE';

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function analyse()
    {
        return $this->belongsTo(Analyse::class);
    }

    public function updateStatus($newStatus)
    {
        if (!in_array($newStatus, [self::STATUS_EN_ATTENTE, self::STATUS_EN_COURS, self::STATUS_TERMINE])) {
            throw new \InvalidArgumentException("Statut invalide");
        }

        $this->status = $newStatus;
        $this->save();

        // Mettre à jour le statut de la prescription parente
        $this->prescription->updateStatus();
    }
}
