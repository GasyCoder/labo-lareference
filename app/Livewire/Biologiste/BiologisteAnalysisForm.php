<?php
namespace App\Livewire\Biologiste;

use Carbon\Carbon;
use App\Models\Analyse;
use Livewire\Component;
use App\Models\Resultat;
use Illuminate\Support\Str;
use App\Models\Prescription;
use App\Models\BacteryFamily;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ResultatPdfShow;
use Illuminate\Support\Facades\DB;
use App\Models\AnalysePrescription;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class BiologisteAnalysisForm extends Component
{
    use LivewireAlert;
    public Prescription $prescription;
    protected $pdfService;

    public function boot(ResultatPdfShow $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public $selectedAnalyse = null;
    public $results = [];
    public $validation;
    public $showForm = false;
    public $showBactery = null;
    public $antibiotics_name = null;
    public $selectedBacteriaResults = [];
    public $currentBacteria = null;
    public $showAntibiotics = false;
    public $otherBacteriaValue = '';
    public $conclusion = '';
    public $selectedOption = [];
    public $showOtherInput = false;
    public $showPresenceInputs = [];
    public $hasResults = false;

    public $validated_analyses = [];
    public $analysisValidated = false;

    public function mount(Prescription $prescription)
    {
        $this->prescription = $prescription;

        // Si une analyse est déjà sélectionnée
        if ($this->selectedAnalyse) {
            $savedState = session("analysis_state_{$prescription->id}_{$this->selectedAnalyse->id}");
            if ($savedState) {
                Log::info('État restauré depuis la session:', [
                    'savedState' => $savedState
                ]);
                $this->hydrateSavedState($savedState);
            }
        }

        $this->loadResults();
        $this->showBactery = BacteryFamily::all();
        $this->showForm = true;
    }

    private function hydrateSavedState($savedState)
    {
        try {
            Log::info('Début hydratation état:', [
                'savedState' => $savedState
            ]);

            foreach ($savedState as $key => $value) {
                if (property_exists($this, $key)) {
                    if ($key === 'results') {
                        foreach ($value as $analyseId => $analyseResult) {
                            // Traitement spécial pour SELECT_MULTIPLE
                            $analyse = Analyse::find($analyseId);
                            if ($analyse && $analyse->analyseType->name === 'SELECT_MULTIPLE') {
                                if (isset($analyseResult['resultats'])) {
                                    // S'assurer que c'est un tableau
                                    $resultats = is_array($analyseResult['resultats'])
                                        ? $analyseResult['resultats']
                                        : json_decode($analyseResult['resultats'], true);

                                    $this->results[$analyseId] = [
                                        'resultats' => $resultats,
                                        'valeur' => $analyseResult['valeur'] ?? null,
                                        'interpretation' => $analyseResult['interpretation'] ?? null
                                    ];
                                }
                            } else {
                                $this->results[$analyseId] = $analyseResult;
                            }
                        }
                    } else {
                        $this->{$key} = $value;
                    }

                    Log::info('Propriété restaurée:', [
                        'key' => $key,
                        'value' => $this->{$key}
                    ]);
                }
            }

            Log::info('État final après hydratation:', [
                'results' => $this->results
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur hydratation:', [
                'message' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
        }
    }

    private function loadResults()
    {
        try {
            $results = Resultat::where('prescription_id', $this->prescription->id)->get();

            foreach ($results as $result) {
                $analyse = Analyse::find($result->analyse_id);
                if (!$analyse) continue;

                // Gestion selon le type d'analyse
                switch ($analyse->analyseType->name) {
                    case 'LEUCOCYTES':
                        // Charger les leucocytes avec leurs enfants
                        $leucocytesData = is_string($result->valeur) ?
                            json_decode($result->valeur, true) :
                            $result->valeur;

                        if (json_last_error() === JSON_ERROR_NONE) {
                            $this->results[$result->analyse_id] = [
                                'valeur' => $leucocytesData['valeur'] ?? null,
                                'polynucleaires' => $leucocytesData['polynucleaires'] ?? null,
                                'lymphocytes' => $leucocytesData['lymphocytes'] ?? null,
                                'resultats' => $result->resultats,
                                'interpretation' => $result->interpretation
                            ];
                        } else {
                            Log::error('Erreur JSON pour les leucocytes', [
                                'valeur' => $result->valeur,
                                'erreur' => json_last_error_msg()
                            ]);
                        }
                        break;

                    case 'GERME':
                        // Charger les germes et leurs antibiogrammes
                        $decodedResults = is_string($result->resultats) ?
                            json_decode($result->resultats, true) :
                            $result->resultats;

                        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedResults)) {
                            // Nettoyer les antibiotiques avant de les stocker
                            if (isset($decodedResults['bacteries'])) {
                                foreach ($decodedResults['bacteries'] as $bacteriaName => $bacteriaData) {
                                    if (isset($bacteriaData['antibiotics'])) {
                                        // Nettoyer les antibiotiques
                                        $cleanedAntibiotics = [];
                                        foreach ($bacteriaData['antibiotics'] as $antibiotic => $sensitivity) {
                                            // Ignorer les entrées vides
                                            if (!empty($sensitivity)) {
                                                // Normaliser le nom de l'antibiotique
                                                $normalizedAntibiotic = $antibiotic;
                                                if (strpos($antibiotic, 'C1G (Cefalotine') !== false) {
                                                    $normalizedAntibiotic = 'C1G (Cefalotine)';
                                                }
                                                $cleanedAntibiotics[$normalizedAntibiotic] = $sensitivity;
                                            }
                                        }
                                        $decodedResults['bacteries'][$bacteriaName]['antibiotics'] = $cleanedAntibiotics;
                                    }
                                }
                            }

                            // Stocker les résultats nettoyés
                            $this->results[$result->analyse_id] = [
                                'bacteries' => $decodedResults['bacteries'] ?? [],
                                'option_speciale' => $decodedResults['option_speciale'] ?? [],
                                'autre_valeur' => $decodedResults['autre_valeur'] ?? null,
                                'resultats' => $result->resultats
                            ];

                            // Restaurer les options sélectionnées
                            if (isset($decodedResults['option_speciale'])) {
                                $this->selectedOption = $decodedResults['option_speciale'];
                            }

                            // Restaurer les résultats des antibiogrammes nettoyés
                            if (isset($decodedResults['bacteries']) && is_array($decodedResults['bacteries'])) {
                                foreach ($decodedResults['bacteries'] as $bacteriaName => $bacteriaData) {
                                    $this->selectedBacteriaResults[$bacteriaName] = [
                                        'name' => $bacteriaName,
                                        'antibiotics' => $bacteriaData['antibiotics'] ?? []
                                    ];

                                    if (empty($this->currentBacteria)) {
                                        $this->currentBacteria = $bacteriaName;
                                        $this->showAntibiotics = true;
                                    }
                                }
                            }
                        }
                    break;

                    case 'DOSAGE':
                        // Charger un dosage simple
                        $this->results[$result->analyse_id] = [
                            'valeur' => $result->valeur,
                            'interpretation' => $result->interpretation,
                            'resultats' => $result->resultats
                        ];
                        break;

                    case 'SELECT_MULTIPLE':
                        // Charger des sélections multiples
                        $this->results[$result->analyse_id] = [
                            'resultats' => is_array($result->resultats)
                                ? $result->resultats
                                : json_decode($result->resultats, true),
                            'valeur' => $result->valeur,
                            'interpretation' => $result->interpretation
                        ];
                        break;

                    case 'INPUT':
                        // Charger une valeur simple
                        $this->results[$result->analyse_id] = [
                            'valeur' => $result->valeur,
                            'interpretation' => $result->interpretation
                        ];
                        break;

                    case 'NEGATIF_POSITIF_3':
                            if ($result->resultats === 'Positif') {
                                $this->results[$result->analyse_id] = [
                                    'resultats' => $result->resultats,
                                    'valeur' => $result->valeur ? explode(', ', $result->valeur) : []
                                ];
                            } else {
                                $this->results[$result->analyse_id] = [
                                    'resultats' => $result->resultats,
                                    'valeur' => null
                                ];
                            }
                    break;

                    default:
                        // Cas générique
                        $this->results[$result->analyse_id] = [
                            'resultats' => $result->resultats,
                            'valeur' => $result->valeur,
                            'interpretation' => $result->interpretation
                        ];
                }
            }

            // Initialiser les analyses sans résultats
            $allAnalyses = Analyse::whereHas('prescriptions', function ($query) {
                $query->where('prescription_id', $this->prescription->id);
            })->with('analyseType')->get();

            foreach ($allAnalyses as $analyse) {
                if (!isset($this->results[$analyse->id])) {
                    $this->results[$analyse->id] = [
                        'valeur' => null,
                        'resultats' => null,
                        'interpretation' => null
                    ];
                }
            }

            // Sauvegarder l'état dans la session
            $this->saveStateToSession();

        } catch (\Exception $e) {
            Log::error('Erreur loadResults:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function loadBacteriaDetails($bacteriaName, $fromDb = false)
    {
        try {
            $this->currentBacteria = $bacteriaName;
            $this->showOtherInput = false;

            // Trouver la famille de bactéries
            $bacteryFamily = BacteryFamily::all()->first(function ($family) use ($bacteriaName) {
                $bacteries = is_string($family->bacteries) ?
                    json_decode($family->bacteries) :
                    $family->bacteries;
                return in_array($bacteriaName, $bacteries);
            });

            if ($bacteryFamily) {
                // Charger les antibiotiques
                $antibiotics = $bacteryFamily->antibiotics;
                $rawAntibiotics = is_string($antibiotics) ?
                    json_decode($antibiotics, true) :
                    $antibiotics;

                // Nettoyer les noms d'antibiotiques
                $this->antibiotics_name = array_map(function($antibiotic) {
                    // Remplacer les cas problématiques
                    if (strpos($antibiotic, 'C1G (Cefalotine') !== false) {
                        return 'C1G (Cefalotine)';
                    }
                    if (strpos($antibiotic, 'Trimethoprime sulphaméthoxazole') !== false) {
                        return 'Trimethoprime sulphamétoxazole (Bactrim)';
                    }
                    return rtrim($antibiotic, '.,)');
                }, $rawAntibiotics);

                $this->showAntibiotics = true;

                // Initialiser ou restaurer les résultats
                if (!isset($this->selectedBacteriaResults[$bacteriaName])) {
                    $this->selectedBacteriaResults[$bacteriaName] = [
                        'name' => $bacteriaName,
                        'antibiotics' => []
                    ];
                }

                if (!$fromDb) {
                    $this->dispatch('bacteriaSelected', [
                        'bacteria' => $bacteriaName,
                        'results' => $this->selectedBacteriaResults[$bacteriaName]
                    ]);
                }

                $this->saveStateToSession();
            }
        } catch (\Exception $e) {
            Log::error('Erreur loadBacteriaDetails:', [
                'message' => $e->getMessage(),
                'bacteria' => $bacteriaName
            ]);
        }
    }

    public function bacteries($bactery_name)
    {
        try {
            $this->selectedOption = [$bactery_name];
            $this->loadBacteriaDetails($bactery_name);
            $this->saveStateToSession();
        } catch (\Exception $e) {
            Log::error('Erreur bacteries:', [
                'message' => $e->getMessage(),
                'bactery_name' => $bactery_name
            ]);
        }
    }

    public function updateAntibiogramResult($antibiotic, $sensitivity)
    {
        try {
            if ($this->currentBacteria) {
                if (!isset($this->selectedBacteriaResults[$this->currentBacteria])) {
                    $this->selectedBacteriaResults[$this->currentBacteria] = [
                        'name' => $this->currentBacteria,
                        'antibiotics' => []
                    ];
                }

                // Nettoyer toutes les variantes de C1G Cefalotine avant d'ajouter la nouvelle
                if (strpos($antibiotic, 'C1G (Cefalotine') !== false) {
                    // Supprimer toutes les variantes existantes
                    $keysToRemove = [];
                    foreach ($this->selectedBacteriaResults[$this->currentBacteria]['antibiotics'] as $key => $_) {
                        if (strpos($key, 'C1G (Cefalotine') !== false) {
                            $keysToRemove[] = $key;
                        }
                    }
                    foreach ($keysToRemove as $key) {
                        unset($this->selectedBacteriaResults[$this->currentBacteria]['antibiotics'][$key]);
                    }

                    // Utiliser le format normalisé
                    $antibiotic = 'C1G (Cefalotine)';
                }

                // N'ajouter que si la sensibilité n'est pas vide
                if (!empty($sensitivity)) {
                    $this->selectedBacteriaResults[$this->currentBacteria]['antibiotics'][$antibiotic] = $sensitivity;
                }

                Log::info('Antibiogramme mis à jour:', [
                    'bacteria' => $this->currentBacteria,
                    'antibiotic' => $antibiotic,
                    'sensitivity' => $sensitivity,
                    'current_state' => $this->selectedBacteriaResults[$this->currentBacteria]
                ]);

                $this->saveStateToSession();
            }
        } catch (\Exception $e) {
            Log::error('Erreur updateAntibiogramResult:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function cleanAntibioticKey($key)
    {
        // Remplacer les parenthèses et leur contenu par des chaînes simplifiées
        $key = preg_replace('/\([^)]+\)/', '', $key);
        // Supprimer les caractères spéciaux tout en gardant les espaces et tirets
        $key = preg_replace('/[^A-Za-z0-9\s-]/', '', $key);
        // Normaliser les espaces
        $key = trim($key);
        // Convertir en slug pour une clé propre
        return Str::slug($key);
    }

    private function saveStateToSession()
    {
        if (!$this->selectedAnalyse) return;

        try {
            $analyseType = $this->selectedAnalyse->analyseType->name;

            // État de base commun à tous les types
            $baseState = [
                'analyseId' => $this->selectedAnalyse->id,
                'results' => $this->results,
                'conclusion' => $this->conclusion
            ];

            // États spécifiques selon le type
            switch ($analyseType) {
                case 'GERME':
                    $state = array_merge($baseState, [
                        'selectedOption' => $this->selectedOption,
                        'otherBacteriaValue' => $this->otherBacteriaValue,
                        'selectedBacteriaResults' => $this->cleanBacteriaResults(),
                        'currentBacteria' => $this->currentBacteria,
                        'showAntibiotics' => $this->showAntibiotics,
                        'antibiotics_name' => $this->antibiotics_name,
                        'showOtherInput' => $this->showOtherInput,
                    ]);
                    break;

                case 'SELECT_MULTIPLE':
                    $state = array_merge($baseState, [
                        'selectedValues' => $this->results[$this->selectedAnalyse->id]['resultats'] ?? []
                    ]);
                    break;

                case 'LEUCOCYTES':
                    $state = array_merge($baseState, [
                        'leucocytesValues' => [
                            'valeur' => $this->results[$this->selectedAnalyse->id]['valeur'] ?? null,
                            'polynucleaires' => $this->results[$this->selectedAnalyse->id]['polynucleaires'] ?? null,
                            'lymphocytes' => $this->results[$this->selectedAnalyse->id]['lymphocytes'] ?? null
                        ]
                    ]);
                    break;

                default:
                    $state = $baseState;
            }

            $sessionKey = "analysis_state_{$this->prescription->id}_{$this->selectedAnalyse->id}";
            session([$sessionKey => $state]);

            Log::info('État sauvegardé dans la session', [
                'sessionKey' => $sessionKey,
                'analyseType' => $analyseType,
                'state' => $state
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur saveStateToSession:', [
                'message' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
        }
    }

    private function cleanBacteriaResults()
    {
        $cleanedBacteriaResults = [];
        foreach ($this->selectedBacteriaResults as $bacteria => $data) {
            $cleanedBacteriaResults[$bacteria] = [
                'name' => $data['name'],
                'antibiotics' => array_filter((array)($data['antibiotics'] ?? []))
            ];
        }
        return $cleanedBacteriaResults;
    }

    private function resetAnalysisForm()
    {
        // Ne pas réinitialiser si nous avons un état sauvegardé
        if ($this->selectedAnalyse) {
            $savedState = session("analysis_state_{$this->prescription->id}_{$this->selectedAnalyse->id}");
            if ($savedState) {
                return;
            }
        }

        $this->results = [];
        $this->selectedOption = [];
        $this->showOtherInput = false;
        $this->otherBacteriaValue = '';
        $this->selectedBacteriaResults = [];
        $this->currentBacteria = null;
        $this->showAntibiotics = false;
        $this->antibiotics_name = null;
        $this->conclusion = '';
        $this->showPresenceInputs = [];
        $this->hasResults = false;
    }

    // Sauvegarde des résultats
    public function saveResult($analyseId){
        try {
            DB::beginTransaction();

            $analyse = Analyse::with(['analyseType', 'children.analyseType'])->findOrFail($analyseId);
            $analyses_children = Analyse::where('parent_code', $analyse->code)->get();
            $child_ids = $analyses_children->pluck('id')->toArray();
            $id_child = [];

            $this->findChildAnalyses($child_ids, $id_child);

            if ($analyse->analyseType->name === 'CULTURE') {
                $mainResultData = [
                    'prescription_id' => $this->prescription->id,
                    'analyse_id' => $analyseId,
                    'resultats' => $this->results[$analyseId]['resultats'] ?? null, // Sélection principale
                    'valeur' => $this->results[$analyseId]['valeur'] ?? null,       // Précision additionnelle si nécessaire
                    'interpretation' => null,
                    'conclusion' => $this->conclusion
                ];
            }
            elseif ($analyse->analyseType->name === 'TEST') {
                $value = $this->results[$analyseId]['resultats'] ?? null;
                $mainResultData = [
                    'prescription_id' => $this->prescription->id,
                    'analyse_id' => $analyseId,
                    'resultats' => $value,
                    'valeur' => $value,
                    'interpretation' => $this->results[$analyseId]['interpretation'] ?? null,
                    'conclusion' => $this->conclusion
                ];
            }
            elseif ($analyse->analyseType->name === 'NEGATIF_POSITIF_2') {
                $mainResultData = [
                    'prescription_id' => $this->prescription->id,
                    'analyse_id' => $analyseId,
                    'resultats' => $this->results[$analyseId]['resultats'] ?? null,
                    'valeur' => $this->results[$analyseId]['resultats'] === 'Positif' ?
                        ($this->results[$analyseId]['valeur'] ?? null) : null,
                    'interpretation' => null,
                    'conclusion' => $this->conclusion
                ];
            }
            elseif ($analyse->analyseType->name === 'NEGATIF_POSITIF_1') {
                $mainResultData = [
                    'prescription_id' => $this->prescription->id,
                    'analyse_id' => $analyseId,
                    'valeur' => $this->results[$analyseId]['valeur'] ?? null,       // Précision additionnelle si nécessaire
                    'interpretation' => $this->results[$analyseId]['interpretation'] ?? null,
                    'conclusion' => $this->conclusion
                ];
            }
            elseif ($analyse->analyseType->name === 'GERME') {
                $mainResultData = [
                    'prescription_id' => $this->prescription->id,
                    'analyse_id' => $analyseId,
                    'resultats' => $this->processGermeValue(),
                    'valeur' => null,
                    'interpretation' => null,
                    'conclusion' => $this->conclusion
                ];
            } elseif ($analyse->analyseType->name === 'DOSAGE') {
                $mainResultData = [
                    'prescription_id' => $this->prescription->id,
                    'analyse_id' => $analyseId,
                    'valeur' => $this->results[$analyseId]['valeur'] ?? null,
                    'interpretation' => $this->results[$analyseId]['interpretation'] ?? null,
                    'conclusion' => $this->conclusion
                ];
            } else {
                $mainResultData = [
                    'prescription_id' => $this->prescription->id,
                    'analyse_id' => $analyseId,
                    'conclusion' => $this->conclusion,
                    'valeur' => null,
                    'interpretation' => null,
                    'resultats' => null
                ];
            }


            foreach ($id_child as $childId) {
                $analyseChild = Analyse::with(['analyseType'])->find($childId);
                if (!$analyseChild || !$analyseChild->analyseType) continue;

                $childResultData = [
                    'prescription_id' => $this->prescription->id,
                    'analyse_id' => $childId,
                    'conclusion' => null,
                    'valeur' => null,
                    'resultats' => null,
                    'interpretation' => null
                ];

                switch ($analyseChild->analyseType->name) {

                    case 'LEUCOCYTES':
                        if (isset($this->results[$childId])) {
                            $leucocytesValue = [
                                'valeur' => $this->results[$childId]['valeur'] ?? null,
                                'polynucleaires' => $this->results[$childId]['polynucleaires'] ?? null,
                                'lymphocytes' => $this->results[$childId]['lymphocytes'] ?? null
                            ];

                            // Vérification que toutes les valeurs requises sont présentes
                            if ($leucocytesValue['valeur'] !== null ||
                                $leucocytesValue['polynucleaires'] !== null ||
                                $leucocytesValue['lymphocytes'] !== null) {
                                $childResultData['valeur'] = json_encode($leucocytesValue, JSON_UNESCAPED_UNICODE);
                            } else {
                                throw new \Exception("Certaines valeurs des leucocytes ou de ses enfants sont manquantes pour l'analyse ID " . $childId);
                            }
                        }
                        break;

                        case 'GERME':
                            // Construit le JSON avec les données déjà nettoyées
                            $germeData = [
                                'type' => 'bacterie',
                                'option_speciale' => $this->selectedOption,
                                'bacteries' => []
                            ];

                            if ($this->currentBacteria && isset($this->selectedBacteriaResults[$this->currentBacteria])) {
                                // Utiliser les données déjà nettoyées de selectedBacteriaResults
                                $antibiotics = [];
                                if (!empty($this->selectedBacteriaResults[$this->currentBacteria]['antibiotics'])) {
                                    foreach ($this->selectedBacteriaResults[$this->currentBacteria]['antibiotics'] as $antibiotic => $sensitivity) {
                                        // Normaliser le nom de C1G Cefalotine
                                        if (strpos($antibiotic, 'C1G (Cefalotine') !== false) {
                                            $antibiotic = 'C1G (Cefalotine)';
                                        }
                                        // Ne garder que les antibiotiques avec une sensibilité
                                        if (!empty($sensitivity)) {
                                            $antibiotics[$antibiotic] = $sensitivity;
                                        }
                                    }
                                }

                                // N'ajouter que si nous avons des antibiotiques
                                if (!empty($antibiotics)) {
                                    $germeData['bacteries'][$this->currentBacteria] = [
                                        'name' => $this->currentBacteria,
                                        'antibiotics' => $antibiotics
                                    ];
                                }
                            }

                            // Si "autre" est sélectionné
                            if (in_array('autre', $this->selectedOption)) {
                                $childResultData['valeur'] = $this->otherBacteriaValue;
                            }

                            // Encoder le JSON final
                            $childResultData['resultats'] = json_encode($germeData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        break;

                        case 'INPUT':
                        if (isset($this->results[$childId]['valeur'])) {
                            $childResultData['valeur'] = $this->results[$childId]['valeur'];
                            // Ajouter l'interprétation pour les analyses avec input numérique
                            if (isset($this->results[$childId]['interpretation'])) {
                                $childResultData['interpretation'] = $this->results[$childId]['interpretation'];
                            }

                            // Pour les analyses numériques, nous voulons aussi stocker la valeur dans resultats
                            $childResultData['resultats'] = $this->results[$childId]['valeur'];
                        }
                        break;

                    case 'SELECT_MULTIPLE':
                        if (isset($this->results[$childId]['resultats']) && is_array($this->results[$childId]['resultats'])) {
                            //$childResultData['resultats'] = implode(', ', $this->results[$childId]['resultats']); // Convert JSON to text
                            $childResultData['resultats'] = json_encode($this->results[$childId]['resultats'], JSON_UNESCAPED_UNICODE);
                        }
                        break;

                    case 'NEGATIF_POSITIF_3':
                            $resultats = $this->results[$childId]['resultats'] ?? null;
                            $childResultData['resultats'] = $resultats;

                            if ($resultats === 'Positif') {
                                // Récupérer les valeurs sélectionnées
                                $selectedValues = $this->results[$childId]['valeur'] ?? [];

                                // Si c'est un tableau, le convertir en chaîne
                                if (is_array($selectedValues)) {
                                    $valeurString = implode(', ', $selectedValues);
                                } else {
                                    $valeurString = $selectedValues;
                                }

                                $childResultData['valeur'] = $valeurString;
                            } else {
                                $childResultData['valeur'] = null;
                            }
                    break;

                    default:
                        $childResultData['resultats'] = $this->results[$childId]['resultats'] ?? null;
                        $childResultData['valeur'] = $this->results[$childId]['valeur'] ?? null;
                        $childResultData['interpretation'] = $this->results[$childId]['interpretation'] ?? null;
                }

                $childResult = Resultat::updateOrCreate(
                    [
                        'prescription_id' => $this->prescription->id,
                        'analyse_id' => $childId
                    ],
                    $childResultData
                );

                if (!$childResult) {
                    throw new \Exception("Erreur lors de la sauvegarde de l'analyse enfant: {$childId}");
                }

                AnalysePrescription::where([
                    'prescription_id' => $this->prescription->id,
                    'analyse_id' => $childId
                ])->update(['status' => 'TERMINE']);
            }

            $mainResult = Resultat::updateOrCreate(
                [
                    'prescription_id' => $this->prescription->id,
                    'analyse_id' => $analyseId
                ],
                $mainResultData
            );

            if (!$mainResult) {
                throw new \Exception("Erreur lors de la sauvegarde de l'analyse principale");
            }

            AnalysePrescription::where([
                'prescription_id' => $this->prescription->id,
                'analyse_id' => $analyseId
            ])->update(['status' => 'TERMINE']);

            $totalAnalyses = $this->prescription->analyses()->count();
            $completedAnalyses = AnalysePrescription::where([
                'prescription_id' => $this->prescription->id,
                'status' => 'VALIDE'
            ])->count();

            if ($totalAnalyses === $completedAnalyses) {
                $this->prescription->update(['status' => Prescription::STATUS_VALIDE]);
            } else {
                // Si certaines analyses ne sont pas encore validées
                $this->prescription->update(['status' => Prescription::STATUS_TERMINE]);
            }

            DB::commit();

            $this->validation = true;
            $this->showForm = false;
            $this->dispatch('resultSaved');
            $this->alert('success', 'Résultats enregistrés avec succès');

            return true;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur saveResult:', [
                'message' => $e->getMessage(),
                'analyse_id' => $analyseId,
                'trace' => $e->getTraceAsString(),
                'data' => [
                    'selectedOption' => $this->selectedOption,
                    'otherBacteriaValue' => $this->otherBacteriaValue,
                    'currentBacteria' => $this->currentBacteria,
                    'results' => $this->results
                ]
            ]);
            $this->alert('success', 'Erreur lors de l\'enregistrement: ' . $e->getMessage());
            return false;
        }
    }

    // Recherche récursive des analyses enfants
    private function findChildAnalyses($child_ids, &$id_child)
    {
        foreach ($child_ids as $childId) {
            $analyseChild = Analyse::findOrFail($childId);
            if ($analyseChild->children->isNotEmpty()) {
                $analyses_children = Analyse::where('parent_code', $analyseChild->code)->get();
                $next_child_ids = $analyses_children->pluck('id')->toArray();
                $this->findChildAnalyses($next_child_ids, $id_child);
            } else {
                $id_child[$childId] = $childId;
            }
        }
    }

    // Traitement des valeurs de germes
    private function processGermeValue()
    {
        if (empty($this->selectedOption)) {
            return null;
        }

        try {
            $germeData = [
                'type' => 'bacterie',
                'option_speciale' => $this->selectedOption,
                'bacteries' => []
            ];

            if ($this->currentBacteria && isset($this->selectedBacteriaResults[$this->currentBacteria])) {
                $antibiotics = [];

                // Nettoyer et normaliser les antibiotiques
                if (!empty($this->selectedBacteriaResults[$this->currentBacteria]['antibiotics'])) {
                    foreach ($this->selectedBacteriaResults[$this->currentBacteria]['antibiotics'] as $antibiotic => $sensitivity) {
                        // Ignorer les entrées vides
                        if (empty($sensitivity)) {
                            continue;
                        }

                        // Normaliser C1G Cefalotine
                        $normalizedAntibiotic = $antibiotic;
                        if (strpos($antibiotic, 'C1G (Cefalotine') !== false) {
                            $normalizedAntibiotic = 'C1G (Cefalotine)';
                        }

                        // Ne pas ajouter de doublons
                        if (!isset($antibiotics[$normalizedAntibiotic])) {
                            $antibiotics[$normalizedAntibiotic] = $sensitivity;
                        }
                    }
                }

                // Seulement ajouter si nous avons des antibiotiques
                if (!empty($antibiotics)) {
                    $germeData['bacteries'][$this->currentBacteria] = [
                        'name' => $this->currentBacteria,
                        'antibiotics' => $antibiotics
                    ];
                }
            }

            return json_encode($germeData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            Log::error('Erreur processGermeValue:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    // Sélection d'une analyse
    public function selectAnalyse($analyseId)
    {
        try {
            // Sauvegarder l'état de l'analyse précédente si elle existe
            if ($this->selectedAnalyse) {
                $this->saveStateToSession();
            }

            $this->selectedAnalyse = Analyse::with(['allChildren.analyseType'])
                ->findOrFail($analyseId);

            // Réinitialiser les propriétés spécifiques au type
            $this->resetTypeSpecificProperties();

            // Charger l'état sauvegardé
            $savedState = session("analysis_state_{$this->prescription->id}_{$analyseId}");

            if ($savedState) {
                $this->hydrateSavedState($savedState);
            } else {
                $this->resetAnalysisForm();
            }

            $this->showForm = true;
            $this->loadResults();

        } catch (\Exception $e) {
            Log::error('Erreur selectAnalyse:', [
                'message' => $e->getMessage(),
                'analyse_id' => $analyseId,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function resetTypeSpecificProperties()
    {
        // Réinitialiser selon le type d'analyse
        switch ($this->selectedAnalyse->analyseType->name) {
            case 'GERME':
                $this->selectedOption = [];
                $this->showOtherInput = false;
                $this->otherBacteriaValue = '';
                $this->selectedBacteriaResults = [];
                $this->currentBacteria = null;
                $this->showAntibiotics = false;
                $this->antibiotics_name = null;
                break;

            case 'SELECT_MULTIPLE':
                if (isset($this->results[$this->selectedAnalyse->id])) {
                    $this->results[$this->selectedAnalyse->id]['resultats'] = [];
                }
                break;

            // Ajouter d'autres cas selon vos types de formulaires
        }
    }

    public function initializeSelectMultiple($analyseId)
    {
        if (!isset($this->results[$analyseId])) {
            $this->results[$analyseId] = [
                'resultats' => [],
                'valeur' => null,
                'interpretation' => null
            ];
        }
    }

    public function updatedResultsSelectMultiple($value, $analyseId)
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        $this->results[$analyseId]['resultats'] = array_values(array_filter($value));
        $this->saveStateToSession();
    }

    // Vérification de la validation d'une analyse
    public function isAnalyseValidated($analyseId)
    {
        return Resultat::where([
            'prescription_id' => $this->prescription->id,
            'analyse_id' => $analyseId,
        ])->whereNotNull('validated_by')->exists();
    }

    // Mise à jour des résultats
    public function updatedResults($value, $key)
    {
        try {
            if (is_string($value) && str_contains($value, ',')) {
                $value = str_replace(',', '.', $value);
            }

            preg_match('/results\.(\d+)\./', $key, $matches);
            if (!empty($matches[1])) {
                $analyseId = $matches[1];
                $analyse = Analyse::find($analyseId);

                if ($analyse) {
                    // Pour SELECT_MULTIPLE
                    if ($analyse->analyseType->name === 'SELECT_MULTIPLE') {
                        if (!is_array($value)) {
                            $value = [$value];
                        }
                        $this->results[$analyseId]['resultats'] = array_values(array_filter($value));
                    }

                    // Pour NEGATIF_POSITIF_3
                    if ($analyse->analyseType->name === 'NEGATIF_POSITIF_3') {
                        if (str_contains($key, 'resultats')) {
                            if ($value === 'Positif') {
                                // Conserver les valeurs existantes si elles existent
                                if (!isset($this->results[$analyseId]['valeur'])) {
                                    $this->results[$analyseId]['valeur'] = [];
                                }
                            } else if ($value === 'Négatif') {
                                // Sauvegarder temporairement les anciennes valeurs
                                $this->results[$analyseId]['previous_valeur'] = $this->results[$analyseId]['valeur'] ?? [];
                                $this->results[$analyseId]['valeur'] = null;
                            }
                        } else if (str_contains($key, 'valeur')) {
                            // Pour les changements de valeurs sélectionnées
                            if (is_array($value)) {
                                $this->results[$analyseId]['valeur'] = array_values(array_filter($value));
                            } else if ($value) {
                                $this->results[$analyseId]['valeur'] = [$value];
                            }
                        }
                    }
                }
            }

            $this->saveStateToSession();

        } catch (\Exception $e) {
            Log::error('Erreur updatedResults:', [
                'message' => $e->getMessage(),
                'value' => $value,
                'key' => $key,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function updatedResultsResultats($value, $analyseId)
    {
        if ($value === 'Positif' && isset($this->results[$analyseId]['previous_valeur'])) {
            // Restaurer les valeurs précédentes
            $this->results[$analyseId]['valeur'] = $this->results[$analyseId]['previous_valeur'];
            unset($this->results[$analyseId]['previous_valeur']);
        }
    }

    // ********************
    public function updatedSelectedOption($value)
    {
        try {
            if (!is_array($value)) {
                $value = [$value];
            }

            $this->selectedOption = array_filter($value);

            $standardOptions = [
                'non-rechercher',
                'en-cours',
                'culture-sterile',
                'absence de germe pathogène'
            ];

            // Si une option standard est sélectionnée
            $hasStandardOption = !empty(array_intersect($standardOptions, $this->selectedOption));

            if ($hasStandardOption) {
                // Ne garder que l'option standard
                $standardOption = array_values(array_intersect($standardOptions, $this->selectedOption))[0];
                $this->selectedOption = [$standardOption];

                // Réinitialiser tout
                $this->resetBacteriaSelection();
                $this->showOtherInput = false;

                // Important : Masquer les antibiogrammes pour les options standards
                $this->showAntibiotics = false;
                $this->currentBacteria = null;
                $this->selectedBacteriaResults = [];
            }
            // Si "autre" est sélectionné
            elseif (in_array('autre', $this->selectedOption)) {
                $this->selectedOption = ['autre'];
                $this->showOtherInput = true;
                $this->resetBacteriaSelection();
                $this->showAntibiotics = false;
            }
            // Si c'est une bactérie
            else {
                $this->showOtherInput = false;
                if (count($this->selectedOption) === 1) {
                    $this->bacteries($this->selectedOption[0]);
                }
            }

            // Sauvegarder l'état
            $this->saveStateToSession();

        } catch (\Exception $e) {
            Log::error('Erreur updatedSelectedOption:', [
                'message' => $e->getMessage(),
                'value' => $value
            ]);
        }
    }

    private function resetBacteriaSelection()
    {
        $this->showAntibiotics = false;
        $this->antibiotics_name = null;
        $this->currentBacteria = null;
        $this->selectedBacteriaResults = [];
        $this->saveStateToSession();
    }


    public function hasRequiredFields($analyseId)
    {
        $result = Resultat::where([
            'prescription_id' => $this->prescription->id,
            'analyse_id' => $analyseId
        ])->first();

        if (!$result) return false;

        $analyse = Analyse::findOrFail($analyseId);

        switch ($analyse->analyseType->name) {
            case 'INPUT':
            case 'INPUT_SUFFIXE':
            case 'DOSAGE':
            case 'COMPTAGE':
                return !empty($result->valeur);

            case 'SELECT':
            case 'TEST':
                return !empty($result->resultats);

            case 'SELECT_MULTIPLE':
                return !empty($result->resultats) && is_array($result->resultats);

            case 'GERME':
                $resultats = is_string($result->resultats) ?
                            json_decode($result->resultats, true) :
                            $result->resultats;

                if (empty($resultats)) return false;

                // Vérifier les options standards
                $standardOptions = ['non-rechercher', 'en-cours', 'culture-sterile', 'absence de germe pathogène'];
                if (isset($resultats['option_speciale']) &&
                    array_intersect($standardOptions, $resultats['option_speciale'])) {
                    return true;
                }

                // Vérifier l'option "autre"
                if (isset($resultats['option_speciale']) &&
                    in_array('autre', $resultats['option_speciale'])) {
                    return !empty($resultats['autre_valeur']);
                }

                // Vérifier les bactéries
                return isset($resultats['bacteries']) && !empty($resultats['bacteries']);

            case 'LEUCOCYTES':
                $valeur = is_string($result->valeur) ?
                        json_decode($result->valeur, true) :
                        $result->valeur;
                return isset($valeur['polynucleaires']) && isset($valeur['lymphocytes']);

            case 'NEGATIF_POSITIF_1':
            case 'NEGATIF_POSITIF_2':
            case 'NEGATIF_POSITIF_3':
                if (empty($result->resultats)) return false;
                if ($result->resultats === 'Presence' && empty($result->valeur)) {
                    return false;
                }
                return true;

            default:
                return true;
        }
    }

    // Validation d'une analyse par le biologiste
    public function validateAnalyse()
    {
        try {
            DB::beginTransaction();

            // Récupérer toutes les analyses (parents et enfants)
            $parentAnalyses = $this->prescription->analyses()
                ->with(['children'])
                ->get();

            $allAnalyseIds = collect();

            // Collecter tous les IDs des analyses (parents et enfants)
            foreach ($parentAnalyses as $parentAnalyse) {
                $allAnalyseIds->push($parentAnalyse->id);
                $this->collectChildAnalyseIds($parentAnalyse, $allAnalyseIds);
            }

            // Mettre à jour les résultats
            Resultat::where('prescription_id', $this->prescription->id)
                ->whereIn('analyse_id', $allAnalyseIds)
                ->update([
                    'validated_by' => Auth::id(),
                    'validated_at' => now(),
                    'status' => Resultat::STATUS_VALIDE
                ]);

            // Mettre à jour les statuts des analyses pivot
            // Les analyses déjà TERMINE restent TERMINE, seules les analyses parentes sont VALIDE
            foreach ($parentAnalyses as $parentAnalyse) {
                // Mettre à jour l'analyse parent en VALIDE
                AnalysePrescription::where([
                    'prescription_id' => $this->prescription->id,
                    'analyse_id' => $parentAnalyse->id
                ])->update([
                    'status' => AnalysePrescription::STATUS_VALIDE,
                    'updated_at' => now()
                ]);

                // Les analyses enfants restent en TERMINE si elles étaient déjà TERMINE
                if ($parentAnalyse->children->isNotEmpty()) {
                    AnalysePrescription::where('prescription_id', $this->prescription->id)
                        ->whereIn('analyse_id', $parentAnalyse->children->pluck('id'))
                        ->where('status', AnalysePrescription::STATUS_TERMINE)
                        ->update([
                            'status' => AnalysePrescription::STATUS_TERMINE,
                            'updated_at' => now()
                        ]);
                }
            }

            // Vérifier si toutes les analyses principales sont validées
            $totalParentAnalyses = $parentAnalyses->count();
            $validatedParentAnalyses = AnalysePrescription::where([
                'prescription_id' => $this->prescription->id,
                'status' => AnalysePrescription::STATUS_VALIDE
            ])->whereIn('analyse_id', $parentAnalyses->pluck('id'))->count();

            // Mise à jour du statut de la prescription
            if ($totalParentAnalyses === $validatedParentAnalyses) {
                $this->prescription->update([
                    'status' => Prescription::STATUS_VALIDE
                ]);
            } else {
                $this->prescription->update([
                    'status' => Prescription::STATUS_TERMINE
                ]);
            }

            DB::commit();

            $this->dispatch('$refresh');
            $this->alert('success', 'Les analyses ont été validées avec succès');

            return redirect()->route('biologiste.analyse.index');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur validation analyses:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'prescription_id' => $this->prescription->id,
                'user_id' => Auth::id()
            ]);

            $this->alert('error', "Une erreur s'est produite lors de la validation");
            return false;
        }
    }

    private function collectChildAnalyseIds($analyse, &$allAnalyseIds)
    {
        if ($analyse->children) {
            foreach ($analyse->children as $child) {
                $allAnalyseIds->push($child->id);
                $this->collectChildAnalyseIds($child, $allAnalyseIds);
            }
        }
    }

    // Rendu de la vue
    public function render()
    {
        try {
            $topLevelAnalyses = $this->prescription->analyses()
                ->with([
                    'children',
                    'analyseType',
                    'analysePrescription' => function($query) {
                        $query->where('prescription_id', $this->prescription->id);
                    }
                ])
                ->orderBy('ordre')
                ->get()
                ->groupBy('parent_code');

            $analyses = $topLevelAnalyses[0] ?? collect();

            $analyses->each(function ($analyse) {
                $analyse->is_validated = $this->isAnalyseValidated($analyse->id);
                $analyse->status = $analyse->analysePrescription
                    ->where('prescription_id', $this->prescription->id)
                    ->first()?->status ?? null;
            });

            return view('livewire.biologiste.details-prescription', [
                'topLevelAnalyses' => $analyses,
                'childAnalyses' => $topLevelAnalyses->forget(0) ?? collect(),
                'showValidateButton' => $this->selectedAnalyse &&
                                    !$this->isAnalyseValidated($this->selectedAnalyse->id) &&
                                    $this->selectedAnalyse->status === 'TERMINE'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur render:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', 'Une erreur est survenue lors du chargement de la page');
            return view('livewire.biologiste.details-prescription', [
                'topLevelAnalyses' => collect(),
                'childAnalyses' => collect(),
                'showValidateButton' => false
            ]);
        }
    }

    public function generateResultatsPDF()
    {
        try {
            $url = $this->pdfService->generatePDF($this->prescription);

            // Émettre un événement avec l'URL du PDF
            $this->dispatch('openPdfInNewWindow', [
                'url' => $url
            ]);

            return $url;
        } catch (\Exception $e) {
            Log::error('Erreur génération PDF:', [
                'message' => $e->getMessage(),
                'prescription_id' => $this->prescription->id,
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('error', "Erreur lors de la génération du PDF : {$e->getMessage()}");
            return null;
        }
    }


    public function resetAntibiotic($antibiotic)
    {
        if (isset($this->selectedBacteriaResults[$this->currentBacteria]['antibiotics'][$antibiotic])) {
            unset($this->selectedBacteriaResults[$this->currentBacteria]['antibiotics'][$antibiotic]);
            $this->saveStateToSession();
        }
    }

    public function resetAllAntibiotics()
    {
        if (isset($this->selectedBacteriaResults[$this->currentBacteria])) {
            $this->selectedBacteriaResults[$this->currentBacteria]['antibiotics'] = [];
            $this->saveStateToSession();
        }
    }

}
