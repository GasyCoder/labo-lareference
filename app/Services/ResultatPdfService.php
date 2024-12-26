<?php

namespace App\Services;

use App\Models\Prescription;
use App\Models\Analyse;
use App\Models\Examen;
use App\Models\Resultat;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ResultatPdfService
{
    
    // private function getValidatedExamens(Prescription $prescription)
    // {
    //     // 1. Récupérer les résultats validés avec leurs analyses
    //     // $validatedResultats = $prescription->resultats()
    //     //     ->whereNotNull('validated_by')
    //     //     ->select('analyse_id')
    //     //     ->get();
    //     $validatedResultats = Resultat::where('prescription_id', $prescription->id)
    //         ->with(['analyse' => function($query) {
    //             $query->orderBy('id', 'asc'); // Changé ordre -> id
    //         }])
    //     ->get();

    //     // 2. Récupérer tous les IDs d'analyses
    //     $analysesIds = $validatedResultats->pluck('analyse_id')->unique();

    //     // 3. Récupérer toutes les analyses avec leur hiérarchie complète
    //     $analyses = Analyse::where(function($query) use ($analysesIds) {
    //         $query->whereIn('id', $analysesIds)
    //             ->orWhereHas('children', function($q) use ($analysesIds) {
    //                 $q->whereIn('id', $analysesIds);
    //             });
    //     })
    //     ->with(['children' => function($query) use ($analysesIds) {
    //         $query->whereIn('id', $analysesIds)
    //               ->orderBy('id', 'asc')  // Changé ordre -> id
    //               ->with(['children' => function($q) use ($analysesIds) {
    //                   $q->whereIn('id', $analysesIds)
    //                     ->orderBy('id', 'asc');  // Changé ordre -> id
    //               }]);
    //     }])
    //     ->orderBy('id', 'asc')  // Changé ordre -> id
    //     ->get();

    //     // 4. Associer les résultats aux analyses en préservant la hiérarchie
    //     $analyses = $analyses->map(function($analyse) use ($validatedResultats) {
    //         $analyse->resultats = $validatedResultats->where('analyse_id', $analyse->id);

    //         if ($analyse->children) {
    //             $analyse->children = $analyse->children->map(function($child) use ($validatedResultats) {
    //                 $child->resultats = $validatedResultats->where('analyse_id', $child->id);

    //                 if ($child->children) {
    //                     $child->children = $child->children->map(function($subChild) use ($validatedResultats) {
    //                         $subChild->resultats = $validatedResultats->where('analyse_id', $subChild->id);
    //                         return $subChild;
    //                     });
    //                 }
    //                 return $child;
    //             });
    //         }
    //         return $analyse;
    //     });

    //     // 5. Regrouper par examen sans le tri par ordre
    //     return Examen::whereHas('analyses', function($query) use ($analyses) {
    //         $query->whereIn('id', $analyses->pluck('id'));
    //     })
    //     ->with(['analyses' => function($query) use ($analyses) {
    //         $query->orderBy('id', 'asc');  // Changé ordre -> id
    //     }])
    //     ->get()  // Retiré le orderBy sur examens
    //     ->map(function($examen) use ($analyses) {
    //         $analysesUniques = collect();
    //         $examen->analyses->each(function($analyse) use ($analyses, &$analysesUniques) {
    //             $matchingAnalyse = $analyses->firstWhere('id', $analyse->id);
    //             if ($matchingAnalyse && !$analysesUniques->contains('id', $matchingAnalyse->id)) {
    //                 $analyse->resultats = $matchingAnalyse->resultats;
    //                 $analyse->children = $matchingAnalyse->children;
    //                 $analysesUniques->push($analyse);
    //             }
    //         });
    //         $examen->analyses = $analysesUniques->sortBy('id');  // Changé ordre -> id
    //         return $examen;
    //     });
    // }



    private function getValidatedExamens(Prescription $prescription)
    {
        // 1. Récupérer les résultats validés 
        $validatedResultats = Resultat::where('prescription_id', $prescription->id)
            ->with(['analyse' => function($query) {
                $query->orderBy('ordre', 'asc');
            }])
            ->get();
    
        // 2. Récupérer les IDs d'analyses
        $analysesIds = $validatedResultats->pluck('analyse_id')->unique();
    
        // 3. Récupérer les analyses avec hiérarchie
        $analyses = Analyse::where(function($query) use ($analysesIds) {
            $query->whereIn('id', $analysesIds)
                ->orWhereHas('children', function($q) use ($analysesIds) {
                    $q->whereIn('id', $analysesIds);
                });
        })
        ->with(['children' => function($query) use ($analysesIds) {
            $query->whereIn('id', $analysesIds)
                  ->orderBy('ordre', 'asc')
                  ->with(['children' => function($q) use ($analysesIds) {
                      $q->whereIn('id', $analysesIds)
                        ->orderBy('ordre', 'asc');
                  }]);
        }])
        ->orderBy('ordre', 'asc')
        ->get();
    
        // 4. Associer les résultats aux analyses
        $analyses = $analyses->map(function($analyse) use ($validatedResultats) {
            $analyse->resultats = $validatedResultats->where('analyse_id', $analyse->id);
            
            if ($analyse->children) {
                $analyse->children = $analyse->children->map(function($child) use ($validatedResultats) {
                    $child->resultats = $validatedResultats->where('analyse_id', $child->id);
                    
                    if ($child->children) {
                        $child->children = $child->children->map(function($subChild) use ($validatedResultats) {
                            $subChild->resultats = $validatedResultats->where('analyse_id', $subChild->id);
                            return $subChild;
                        });
                    }
                    return $child;
                });
            }
            return $analyse;
        });
    
        // 5. Regrouper et ordonner les examens
        return Examen::whereHas('analyses', function($query) use ($analyses) {
            $query->whereIn('id', $analyses->pluck('id'));
        })
        ->with(['analyses' => function($query) {
            $query->orderBy('ordre', 'asc')
                  ->with(['children' => function($q) {
                      $q->orderBy('ordre', 'asc')
                        ->with(['children' => function($sq) {
                            $sq->orderBy('ordre', 'asc');
                        }]);
                  }]);
        }])
        ->get()
        ->map(function($examen) use ($analyses) {
            $analysesUniques = collect();
            
            $examen->analyses->each(function($analyse) use ($analyses, &$analysesUniques) {
                $matchingAnalyse = $analyses->firstWhere('id', $analyse->id);
                if ($matchingAnalyse && !$analysesUniques->contains('id', $matchingAnalyse->id)) {
                    $analyse->resultats = $matchingAnalyse->resultats;
                    $analyse->children = $matchingAnalyse->children;
                    $analysesUniques->push($analyse);
                }
            });
            
            $examen->analyses = $analysesUniques;
            return $examen;
        });
    }


    public function generatePDF(Prescription $prescription)
    {
        try {
            $examens = $this->getValidatedExamens($prescription);

                    // Générer le QR code
        $qrCode = new QrCode;
        // Dans ResultatPdfShow.php
        $designationsAnalyses = $prescription->analyses()
            ->where(function ($query) {
                $query->where('level', 'PARENT')
                    ->orWhere('level', 'NORMAL');
            })
            ->orderBy('ordre')  // Pour garder l'ordre correct
            ->get()
            ->map(function ($analyse) {
                return $analyse->designation;
            })
            ->join("\n• ");

            $qrCodeData = mb_convert_encoding(sprintf(
            "LABORATOIRE LA REFERENCE MAHAJANGA\n\n" .
            "PATIENT:\n" .
            "Nom: %s\n" .
            "Sexe: %s\n" .
            "Age: %s\n" .
            "Réf: %s\n" .
            "Date: %s\n\n" .
            "PRESCRIPTEUR:\n" .
            "Dr. %s\n\n" .
            "ANALYSES:\n" .
            "• %s",

            $prescription->patient->nom . ' ' . $prescription->patient->prenom,
            $prescription->patient->sexe,
            $prescription->age . ' ' . $prescription->unite_age,
            $prescription->patient->formatted_ref ?? 'N/A',
            $prescription->created_at->format('d/m/Y'),
            $prescription->nouveau_prescripteur_nom ?? $prescription->prescripteur->name,
            $designationsAnalyses
        ), 'UTF-8', 'UTF-8');

        $qrcodeImage = $qrCode::size(150)
            ->encoding('UTF-8')  // Spécifier l'encodage UTF-8
            ->errorCorrection('M')
            ->generate($qrCodeData);


            Log::info('Structure des examens:',[
                'examens' => $examens->map(function($examen) {
                    return [
                        'id' => $examen->id,
                        'nom' => $examen->name,
                        'analyses' => $examen->analyses->map(function($analyse) {
                            return [
                                'id' => $analyse->id,
                                'designation' => $analyse->designation,
                                'level' => $analyse->level,
                                'result_disponible' => $analyse->result_disponible,
                                'children' => $analyse->children->map(function($child) {
                                    return [
                                        'id' => $child->id,
                                        'designation' => $child->designation,
                                        'result_disponible' => $child->result_disponible,
                                        'resultats' => $child->resultats->map(function($resultat) {
                                            return [
                                                'valeur' => $resultat->valeur,
                                                'resultats' => $resultat->resultats,
                                                'validated_by' => $resultat->validated_by
                                            ];
                                        })->toArray()
                                    ];
                                })->toArray(),
                                'resultats' => $analyse->resultats->map(function($resultat) {
                                    return [
                                        'valeur' => $resultat->valeur,
                                        'resultats' => $resultat->resultats,
                                        'validated_by' => $resultat->validated_by
                                    ];
                                })->toArray()
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ]);


            $data = [
                'prescription' => $prescription->load('patient', 'prescripteur'),
                'qrcodeImage' => $qrcodeImage,
                'examens' => $examens,
                'headers' => [
                    'nif' => 'NIF-400319074/1 du 30/10/18 - STAT-86903412017D.0010',
                    'rcs' => 'RCS 2018A00156',
                    'tel_bureau' => '261 34 53 211 41',
                    'tel_urgence' => '261 34 76 637 92',
                    'adresse' => 'Mangarivotra',
                    'ville' => 'MAHAJANGA 401'
                ]
            ];

            return PDF::loadView('pdf.analyses.resultats-analyses', $data);

            // Vérifier si le PDF est généré correctement
            if (!$pdf->output()) {
                throw new \Exception("Erreur lors de la génération du PDF");
            }


        } catch (\Exception $e) {
            Log::error('Erreur génération PDF:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }


}
