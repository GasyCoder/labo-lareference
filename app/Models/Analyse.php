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

    public function resultats()
    {
        return $this->hasMany(Resultat::class, 'analyse_id');
    }

    /**
     * Obtenir l'analyse parente.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Analyse::class, 'parent_code', 'code');
    }

    /**
     * Obtenir les analyses enfants directes.
     */
    public function children()
    {
        return $this->hasMany(Analyse::class, 'parent_code', 'code')
            ->orderBy('analyses.ordre', 'asc')          // Spécifier la table
            ->orderBy('analyses.created_at', 'asc')     // Spécifier la table
            ->orderBy('analyses.id', 'asc')             // Spécifier la table
            ->distinct('analyses.id');
    }

    public function getLevelValueAttribute(): string
    {
        return $this->level instanceof AnalyseLevel ? $this->level->value : $this->level;
    }

    public function allChildren($depth = 3)
    {
        if ($depth === 0) {
            return $this->hasMany(Analyse::class, 'parent_code', 'code')->whereNull('id');
        }

        return $this->hasMany(Analyse::class, 'parent_code', 'code')
            ->with(['allChildren' => function ($query) use ($depth) {
                $query->with('resultats')->orderBy('ordre');
            }])
            ->orderBy('ordre');
    }

    public function analysePrescription(): HasMany
    {
        return $this->hasMany(AnalysePrescription::class, 'analyse_id');
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

        // Le scope global
        static::addGlobalScope('order', function ($builder) {
            $builder->orderBy('analyses.ordre', 'asc')
                   ->orderBy('analyses.created_at', 'asc')
                   ->orderBy('analyses.id', 'asc');
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

    // Dans votre modèle Analyse, ajoutez ce mutateur
    /**
     * Accesseur pour les valeurs du select
     */
    public function getFormattedResultsAttribute()
    {
        $rawValue = $this->attributes['result_disponible'] ?? null;
        if (empty($rawValue)) {
            return [];
        }

        $data = is_array($rawValue) ? $rawValue : json_decode($rawValue, true);

        // Vérifier directement si nous avons un tableau de valeurs
        if (isset($data['value']) && is_array($data['value'])) {
            // S'assurer que ce n'est pas un format d'unités
            $firstValue = reset($data['value']);
            if (!is_array($firstValue) && !isset($data['value']['val_ref'])) {
                return $data['value'];
            }
        }

        return [];
    }

    /**
     * Accesseur pour les unités (garder celui-ci inchangé)
     */
    public function getResultDisponibleAttribute($value)
    {
        if (empty($value)) {
            return [];
        }

        // Première décodage pour gérer le double encodage JSON
        $data = is_string($value) ? json_decode($value, true) : $value;

        // Deuxième décodage si nécessaire
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        // Si les données sont dans une structure avec 'value'
        if (isset($data['value']) && is_array($data['value'])) {
            if (isset($data['value'][0]) && is_string($data['value'][0])) {
                $innerData = json_decode($data['value'][0], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return [
                        'val_ref' => $innerData['val_ref'] ?? null,
                        'unite' => $innerData['unite'] ?? null,
                        'suffixe' => $innerData['suffixe'] ?? null
                    ];
                }
            }
            return $data;
        }

        // Pour le format direct avec val_ref, unite et suffixe
        return [
            'val_ref' => $data['val_ref'] ?? null,
            'unite' => $data['unite'] ?? null,
            'suffixe' => $data['suffixe'] ?? null
        ];
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


    public function getFormattedResultValue()
    {
        $resultat = $this->resultats->first();
        if (!$resultat) return null;

        if ($resultat->valeur) {
            return $resultat->valeur;
        }

        if ($resultat->resultats) {
            if (is_string($resultat->resultats)) {
                $jsonData = json_decode($resultat->resultats, true);
                return $jsonData['resultats'] ?? $resultat->resultats;
            }
            return $resultat->resultats;
        }

        return null;
    }



    public function loadChildrenWithDepth($depth = 3)
    {
        if ($depth === 0) {
            return $this; // Stop au niveau actuel
        }

        $this->load(['children' => function ($query) use ($depth) {
            $query->with('children')->get()->each->loadChildrenWithDepth($depth - 1);
        }]);

        return $this;
    }

    public function hasCyclicRelation($visited = [])
    {
        if (in_array($this->id, $visited)) {
            return true; // Boucle détectée
        }

        $visited[] = $this->id;

        foreach ($this->children as $child) {
            if ($child->hasCyclicRelation($visited)) {
                return true;
            }
        }

        return false;
    }



}
