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
    public function children(): HasMany
    {
        return $this->hasMany(Analyse::class, 'parent_code');
    }

    /**
     * Obtenir tous les enfants récursivement.
     */
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
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
}
