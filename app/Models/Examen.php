<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Examen extends Model
{
    use HasFactory, SoftDeletes;
    public $timestamps = true;
    protected $fillable = ['name', 'abr', 'status'];

    public function analyses()
    {
        return $this->hasMany(Analyse::class, 'examen_id');
    }


    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }



     // Méthode pour récupérer les examens avec analyses et vérifier les boucles
     public function getExamensWithAnalyses($validatedIds)
     {
         return $this->with(['analyses' => function ($query) use ($validatedIds) {
             $query->whereIn('id', $validatedIds)->with('children');
         }])->get()->each(function ($examen) {
             foreach ($examen->analyses as $analyse) {
                 if ($analyse->hasCyclicRelation()) {
                     throw new \Exception("Boucle détectée dans l'analyse ID: {$analyse->id}");
                 }
                 $analyse->loadChildrenWithDepth(3);
             }
         });
     }

}
