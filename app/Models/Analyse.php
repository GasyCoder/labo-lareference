<?php

namespace App\Models;

use App\Enums\AnalyseLevel;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Analyse extends Model
{
    use HasFactory, SoftDeletes;
    public $timestamps = true;
    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'level',
        'abr',
        'parent_code',
        'designation',
        'description',
        'prix',
        'is_bold',
        'examen_id',
        'analyse_type_id',
        'result_disponible',
        'ordre',
        'status',
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'prix' => 'decimal:2',
        'is_bold' => 'boolean',
        'status' => 'boolean',
        'result_disponible' => 'array',
        'level' => AnalyseLevel::class,
    ];


    /**
     * Obtenir l'analyse parente.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Analyse::class, 'parent_code');
    }

    /**
     * Obtenir les analyses enfants directes.
     */

     public function children()
     {
         return $this->hasMany(Analyse::class, 'parent_code', 'code')->orderBy('ordre');
     }


    public function allChildren()
    {
             return $this->hasMany(Analyse::class, 'parent_code', 'code')
                    ->with('allChildren')
                    ->orderBy('ordre');
    }

    /**
     * Obtenir l'examen associé.
     */
    public function examen(): BelongsTo
    {
        return $this->belongsTo(Examen::class);
    }

    /**
     * Obtenir le type d'analyse associé.
     */
    public function analyseType(): BelongsTo
    {
        return $this->belongsTo(AnalyseType::class);
    }

    public function prescriptions()
    {
        return $this->belongsToMany(Prescription::class, 'analyse_prescriptions')
                    ->using(AnalysePrescription::class)
                    ->withPivot('prix', 'status')
                    ->withTimestamps();
    }
    /**
     * Scope pour les analyses actives.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope pour les analyses de niveau parent.
     */
    public function scopeParents($query)
    {
        return $query->where('level', AnalyseLevel::PARENT);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($analyse) {
            if (empty($analyse->code)) {
                $analyse->code = self::generateUniqueCode();
            }
        });
    }

    public static function generateUniqueCode()
    {
        $lastCode = self::max('code');
        $nextCode = $lastCode ? intval($lastCode) + 1 : 547; // Commencer à 547 si la table est vide

        while (self::where('code', (string) $nextCode)->exists()) {
            $nextCode++;
        }

        return (string) $nextCode;
    }


    public function scopeWithHierarchy($query, $typeId = null, $resultDisponible = null)
    {
        return $query->with(['allChildren' => function ($query) use ($typeId, $resultDisponible) {
            if ($typeId) {
                $query->where('analyse_type_id', $typeId);
            }

            if ($resultDisponible) {
                $query->whereJsonContains('result_disponible', $resultDisponible);
            }
        }])
        ->where(function ($query) use ($typeId, $resultDisponible) {
            if ($typeId) {
                $query->where('analyse_type_id', $typeId);
            }

            if ($resultDisponible) {
                $query->whereJsonContains('result_disponible', $resultDisponible);
            }
        })
        ->orderBy('ordre');
    }


    public function getFormattedResultsAttribute()
    {
        if (!isset($this->result_disponible['value'])) {
            return [];
        }

        $value = $this->result_disponible['value'];

        // Cas spécifique pour les valeurs avec "25 par champ"
        if (str_contains($value, '25 par champ')) {
            return [
                '> 25 par champ',
                '< 25 par champ'
            ];
        }

        // Extraire toutes les options distinctes
        return $this->extractOptions($value);
    }

    private function extractOptions($value)
    {
        // 1. Nettoyer la chaîne de base
        $value = trim($value);

        // 2. Séparation initiale basée sur les mots qui commencent par une majuscule
        $parts = preg_split('/(?=(?<!^)[A-Z][a-z])/', $value);

        // 3. Nettoyage et reconstruction des options
        $options = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if (!empty($part)) {
                $options[] = $part;
            }
        }

        return array_unique($options);
    }

    private function parseOptionsString($value)
    {
        // Première étape : normaliser les espaces avant les majuscules
        $value = preg_replace('/([a-z])([A-Z])/', '$1 $2', $value);

        // Deuxième étape : gérer les cas spéciaux
        $specialCases = [
            'GRAM' => ' GRAM ',  // Assure des espaces autour de GRAM
            'à GRAM' => 'à GRAM' // Préserve "à GRAM" ensemble
        ];

        foreach ($specialCases as $search => $replace) {
            $value = str_replace($search, $replace, $value);
        }

        // Troisième étape : séparer en options
        $options = array_map('trim', explode(' ', $value));

        // Quatrième étape : reconstruire les options complètes
        return $this->reconstructOptions($options);
    }

    private function reconstructOptions($parts)
    {
        $options = [];
        $currentOption = '';

        foreach ($parts as $part) {
            if ($this->isStartOfNewOption($part)) {
                if (!empty($currentOption)) {
                    $options[] = trim($currentOption);
                }
                $currentOption = $part;
            } else {
                $currentOption .= ' ' . $part;
            }
        }

        if (!empty($currentOption)) {
            $options[] = trim($currentOption);
        }

        return $options;
    }

    private function isStartOfNewOption($part)
    {
        // Définir les mots qui commencent une nouvelle option
        $optionStarters = ['Cocci', 'Bacille', 'Autre'];
        return in_array($part, $optionStarters);
    }

}
