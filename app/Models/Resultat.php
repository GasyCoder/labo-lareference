<?php

namespace App\Models;

use Carbon\Carbon;
use App\Services\GermeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Resultat extends Model
{
    use HasFactory;

    /**
     * Indique si les timestamps sont automatiquement gérés
     */
    public $timestamps = true;

    /**
     * Constantes pour les types d'interprétation
     */
    const INTERPRETATION_NORMAL = 'NORMAL';
    const INTERPRETATION_PATHOLOGIQUE = 'PATHOLOGIQUE';

    const STATUS_EN_ATTENTE = 'EN_ATTENTE';
    const STATUS_EN_COURS = 'EN_COURS';
    const STATUS_TERMINE = 'TERMINE';
    const STATUS_VALIDE = 'VALIDE';
    const STATUS_ARCHIVE = 'ARCHIVE';


    // public function getFormattedResultatsAttribute(): string
    // {
    //     if (!$this->analyse || $this->analyse->analyseType->name !== 'GERME') {
    //         return $this->resultats;
    //     }

    //     return $this->formatGermeForDisplay($this->resultats);
    // }

    /**
     * Les attributs qui sont mass assignable.
     */
    protected $fillable = [
        'prescription_id',
        'analyse_id',
        'resultats',
        'valeur',
        'interpretation',
        'conclusion',
        'validated_by',
        'validated_at',
        'status',
    ];

    /**
     * Les attributs qui doivent être castés.
     */
    protected $casts = [
        // 'resultats' => 'json',  // Pour automatiquement encoder/décoder JSON
        'valeur' => 'json',
        'validated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Les attributs qui doivent être mutés.
     */
    protected $appends = [
        'is_validated',
        'formatted_value'
    ];

    /**
     * Relation avec la prescription
     */
    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    /**
     * Relation avec l'analyse
     */
    public function analyse(): BelongsTo
    {
        return $this->belongsTo(Analyse::class, 'analyse_id');
    }

    /**
     * Relation avec le validateur
     */
    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Vérifie si le résultat est validé
     */
    public function getIsValidatedAttribute(): bool
    {
        return !is_null($this->validated_at);
    }

    /**
     * Formate la valeur selon le type d'analyse
     */
    public function getFormattedValueAttribute(): ?string
    {
        if (is_null($this->valeur)) {
            return null;
        }

        // Si la valeur est un JSON valide, on le décode
        if ($this->isJson($this->valeur)) {
            $decodedValue = json_decode($this->valeur, true);
            return $this->formatJsonValue($decodedValue);
        }

        return $this->valeur;
    }

    /**
     * Vérifie si une chaîne est du JSON valide
     */
    private function isJson($string): bool
    {
        if (!is_string($string)) {
            return false;
        }
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }

    /**
     * Formate une valeur JSON selon sa structure
     */
    private function formatJsonValue($value): string
    {
        if (isset($value['valeur'], $value['polynucleaires'], $value['lymphocytes'])) {
            return sprintf(
                "Valeur: %s, Polynucléaires: %s%%, Lymphocytes: %s%%",
                $value['valeur'],
                $value['polynucleaires'],
                $value['lymphocytes']
            );
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Scope pour les résultats validés
     */
    public function scopeValidated($query)
    {
        return $query->whereNotNull('validated_at');
    }

    /**
     * Scope pour les résultats non validés
     */
    public function scopeNotValidated($query)
    {
        return $query->whereNull('validated_at');
    }

    /**
     * Valide le résultat
     */
    public function validate(int $userId): bool
    {
        return $this->update([
            'validated_by' => $userId,
            'validated_at' => Carbon::now()
        ]);
    }

    /**
     * Vérifie si l'interprétation est valide
     */
    public static function isValidInterpretation(?string $interpretation): bool
    {
        return in_array($interpretation, [
            self::INTERPRETATION_NORMAL,
            self::INTERPRETATION_PATHOLOGIQUE,
            null
        ]);
    }

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

}
