<?php
namespace App\Livewire\Biologiste;

use Carbon\Carbon;
use App\Models\Analyse;
use Livewire\Component;
use App\Models\Resultat;
use App\Models\Prescription;
use App\Models\BacteryFamily;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ResultatPdfShow;
use Illuminate\Support\Facades\DB;
use App\Models\AnalysePrescription;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BiologisteAnalysisForm extends Component
{
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

    public function mount(Prescription $prescription){

        $this->prescription = $prescription;
        // Charger l'état depuis la session pour l'analyse sélectionnée si elle existe
        if ($this->selectedAnalyse) {
            $savedState = session("analysis_state_{$prescription->id}_{$this->selectedAnalyse->id}");
            if ($savedState) {
                $this->hydrateSavedState($savedState);
            }
        }
        // Charger depuis la base de données
        $this->loadResults();
        // Charger les bactéries disponibles
        $this->showBactery = BacteryFamily::all();
        // Si une bactérie est déjà sélectionnée, recharger ses détails
        if ($this->currentBacteria) {
            $this->loadBacteriaDetails($this->currentBacteria, true);
        }
        $this->restoreAntibioticSelections();
        $this->showForm = true;
    }

    private function restoreAntibioticSelections(){
        $results = Resultat::where('prescription_id', $this->prescription->id)->get();

        foreach ($results as $result) {
            $decodedData = json_decode($result->resultats, true);

            if (isset($decodedData['bacteries'])) {
                foreach ($decodedData['bacteries'] as $bacteriaName => $bacteriaData) {
                    if (isset($bacteriaData['antibiotics'])) {
                        $this->selectedBacteriaResults[$bacteriaName]['antibiotics'] = $bacteriaData['antibiotics'];
                    }
                }
            }
        }
    }

    private function hydrateSavedState($savedState){
        foreach ($savedState as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    private function loadResults(){
        try {
            $results = Resultat::where('prescription_id', $this->prescription->id)->get();

            foreach ($results as $result) {
                $analyse = Analyse::find($result->analyse_id);
                if (!$analyse) continue;

                $this->results[$result->analyse_id] = [
                    'resultats' => $result->resultats,
                    'valeur' => $result->valeur,
                    'interpretation' => $result->interpretation
                ];

                // Pour les analyses de type GERME
                if ($analyse->analyseType->name === 'GERME') {
                    if ($result->resultats) {
                        $decodedData = is_string($result->resultats) ?
                            json_decode($result->resultats, true) :
                            $result->resultats;

                        // Gérer les options et les bactéries
                        if (isset($decodedData['option_speciale'])) {
                            $this->selectedOption = $decodedData['option_speciale'];
                        }

                        if (isset($decodedData['bacteries'])) {
                            foreach ($decodedData['bacteries'] as $bacteriaName => $bacteriaData) {
                                $this->selectedBacteriaResults[$bacteriaName] = [
                                    'name' => $bacteriaName,
                                    'antibiotics' => $bacteriaData['antibiotics'] ?? []
                                ];

                                if (empty($this->currentBacteria)) {
                                    $this->currentBacteria = $bacteriaName;
                                    $this->loadBacteriaDetails($bacteriaName, true);
                                }
                            }
                        }

                        // Sauvegarder l'état pour cette analyse spécifique
                        $state = [
                            'selectedOption' => $this->selectedOption,
                            'otherBacteriaValue' => $this->otherBacteriaValue ?? '',
                            'selectedBacteriaResults' => $this->selectedBacteriaResults,
                            'currentBacteria' => $this->currentBacteria,
                            'showAntibiotics' => $this->showAntibiotics,
                            'results' => $this->results
                        ];

                        session(["analysis_state_{$this->prescription->id}_{$result->analyse_id}" => $state]);
                    }
                }
            }

            $this->hasResults = !empty($this->results);

        } catch (\Exception $e) {
            Log::error('Erreur loadResults:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function refreshAntibiograms(){
        if ($this->currentBacteria && isset($this->selectedBacteriaResults[$this->currentBacteria])) {
            $this->dispatch('resultsLoaded', [
                'selectedBacteriaResults' => [
                    $this->currentBacteria => $this->selectedBacteriaResults[$this->currentBacteria]
                ]
            ]);
        }
    }

    private function loadBacteriaDetails($bacteriaName, $fromDb = false)
    {
        try {
            $this->currentBacteria = $bacteriaName;
            $this->showOtherInput = false;

            $bacteryFamily = BacteryFamily::all()->first(function ($family) use ($bacteriaName) {
                $bacteries = is_string($family->bacteries)
                    ? json_decode($family->bacteries)
                    : $family->bacteries;
                return in_array($bacteriaName, $bacteries);
            });

            if ($bacteryFamily) {
                $antibiotics = $bacteryFamily->antibiotics;
                $this->antibiotics_name = is_string($antibiotics)
                    ? json_decode($antibiotics, true)
                    : $antibiotics;

                $this->showAntibiotics = true;

                if (!isset($this->selectedBacteriaResults[$bacteriaName])) {
                    $this->selectedBacteriaResults[$bacteriaName] = [
                        'name' => $bacteriaName,
                        'antibiotics' => []
                    ];
                }

                if ($fromDb) {
                    $this->saveStateToSession();
                }
            }

            if ($this->showAntibiotics && isset($this->selectedBacteriaResults[$bacteriaName])) {
                $this->refreshAntibiograms();
            }

        } catch (\Exception $e) {
            Log::error('Erreur loadBacteriaDetails:', [
                'message' => $e->getMessage(),
                'bacteria' => $bacteriaName,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function bacteries($bactery_name){
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


    public function updateAntibiogramResult($antibiotic, $sensitivity){
        try {
            if ($this->currentBacteria) {
                if (!isset($this->selectedBacteriaResults[$this->currentBacteria]['antibiotics'])) {
                    $this->selectedBacteriaResults[$this->currentBacteria]['antibiotics'] = [];
                }

                // Nettoyage du nom d'antibiotique
                $cleanAntibiotic = $this->cleanAntibioticName($antibiotic);
                $this->selectedBacteriaResults[$this->currentBacteria]['antibiotics'][$cleanAntibiotic] = $sensitivity;

                // Sauvegarder l'état après modification
                $this->saveStateToSession();
            }
        } catch (\Exception $e) {
            Log::error('Erreur updateAntibiogramResult:', [
                'message' => $e->getMessage(),
                'antibiotic' => $antibiotic,
                'sensitivity' => $sensitivity
            ]);
        }
    }


    private function saveStateToSession(){
        if (!$this->selectedAnalyse) return;

        $state = [
            'results' => $this->results,
            'selectedOption' => $this->selectedOption,
            'otherBacteriaValue' => $this->otherBacteriaValue,
            'selectedBacteriaResults' => $this->selectedBacteriaResults,
            'currentBacteria' => $this->currentBacteria,
            'showAntibiotics' => $this->showAntibiotics,
            'antibiotics_name' => $this->antibiotics_name,
            'showOtherInput' => $this->showOtherInput,
            'conclusion' => $this->conclusion
        ];

        // Sauvegarder avec l'ID unique de l'analyse
        session(["analysis_state_{$this->prescription->id}_{$this->selectedAnalyse->id}" => $state]);
    }

    private function resetAnalysisForm(){
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
    public function saveResult($analyseId)
    {
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
            } elseif ($analyse->analyseType->name === 'GERME') {
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
                        // Construit le JSON directement sans nettoyer les noms d’antibiotiques
                        $germeData = [
                            'type' => 'bacterie',
                            'option_speciale' => $this->selectedOption,
                            'bacteries' => []
                        ];

                        if ($this->currentBacteria && isset($this->selectedBacteriaResults[$this->currentBacteria])) {
                            $antibiotics = [];
                            if (!empty($this->selectedBacteriaResults[$this->currentBacteria]['antibiotics'])) {
                                foreach ($this->selectedBacteriaResults[$this->currentBacteria]['antibiotics'] as $antibiotic => $sensitivity) {
                                    // On n'applique plus cleanAntibioticName
                                    $antibiotics[$antibiotic] = $sensitivity;
                                }
                            }

                            $germeData['bacteries'][$this->currentBacteria] = [
                                'name' => $this->currentBacteria,
                                'antibiotics' => $antibiotics
                            ];
                        }

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
                            $childResultData['resultats'] = json_encode($this->results[$childId]['resultats'], JSON_UNESCAPED_UNICODE);
                        }
                        break;

                    case 'NEGATIF_POSITIF_3':
                        $childResultData['resultats'] = $this->results[$childId]['resultats'] ?? null;
                        if (($this->results[$childId]['resultats'] ?? '') === 'Presence') {
                            $childResultData['valeur'] = $this->results[$childId]['valeur'] ?? null;
                        }
                        break;

                    case 'TEST':
                        $childResultData['resultats'] = $this->results[$childId]['resultats'] ?? null;
                        $childResultData['interpretation'] = ($this->results[$childId]['resultats'] === 'POSITIF')
                            ? 'PATHOLOGIQUE'
                            : 'NORMAL';
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
                ])->update(['status' => 'VALIDE']);
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
            ])->update(['status' => 'VALIDE']);

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
            session()->flash('success', 'Résultats enregistrés avec succès');

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
            session()->flash('error', "Erreur lors de l'enregistrement: " . $e->getMessage());
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
    private function processGermeValue(){
        if (empty($this->selectedOption)) {
            return null;
        }

        $lines = [];
        $lines[] = "Type : bactérie";
        $lines[] = "Option(s) spéciale(s) : " . implode(', ', $this->selectedOption);

        if ($this->currentBacteria && isset($this->selectedBacteriaResults[$this->currentBacteria]['antibiotics'])) {
            $lines[] = "Bactérie : " . $this->currentBacteria;

            $antibioticCorrections = [
                'Trimethoprime sulphaméthoxazole (Bactrim,' => 'Trimethoprime sulphaméthoxazole (Bactrim)',
                'C1G (Cefalotine,' => 'C1G (Cefalotine)',
                '\/' => '/',
                '\"' => '"'
            ];

            foreach ($this->selectedBacteriaResults[$this->currentBacteria]['antibiotics'] as $antibiotic => $sensitivity) {
                $cleanAntibiotic = $antibiotic;
                foreach ($antibioticCorrections as $search => $replace) {
                    $cleanAntibiotic = str_replace($search, $replace, $cleanAntibiotic);
                }

                $cleanAntibiotic = preg_replace('/\{"":"":{"[^"]*"\}}/', '', $cleanAntibiotic);
                $cleanAntibiotic = trim($cleanAntibiotic);

                if (!empty($cleanAntibiotic)) {
                    $lines[] = " - $cleanAntibiotic : $sensitivity";
                }
            }
        }

        return implode("\n", $lines);
    }

    // Sélection d'une analyse
    public function selectAnalyse($analyseId){
        try {
            // Sauvegarder l'état actuel de l'analyse précédente
            if ($this->selectedAnalyse) {
                $this->saveStateToSession();
            }

            $this->selectedAnalyse = Analyse::with(['allChildren.analyseType'])->findOrFail($analyseId);

            // Charger l'état sauvegardé pour la nouvelle analyse
            $savedState = session("analysis_state_{$this->prescription->id}_{$analyseId}");
            if ($savedState) {
                $this->hydrateSavedState($savedState);
            } else {
                $this->resetAnalysisForm();
            }

            // Charger les résultats de la base de données
            $result = Resultat::where([
                'prescription_id' => $this->prescription->id,
                'analyse_id' => $analyseId
            ])->first();

            if ($result) {
                $this->results[$analyseId] = [
                    'resultats' => $result->resultats,
                    'valeur' => $result->valeur,
                    'interpretation' => $result->interpretation
                ];
                $this->conclusion = $result->conclusion;

                if ($this->selectedAnalyse->analyseType->name === 'GERME' && $result->resultats) {
                    $decodedResults = json_decode($result->resultats, true);
                    $this->selectedOption = $decodedResults['option_speciale'] ?? [];
                    $this->otherBacteriaValue = $decodedResults['autre_valeur'] ?? '';

                    if (isset($decodedResults['bacteries'])) {
                        foreach ($decodedResults['bacteries'] as $bacteria => $data) {
                            $this->selectedBacteriaResults[$bacteria] = [
                                'name' => $bacteria,
                                'antibiotics' => $data['antibiotics'] ?? []
                            ];
                            if (empty($this->currentBacteria)) {
                                $this->currentBacteria = $bacteria;
                                $this->loadBacteriaDetails($bacteria, true);
                            }
                        }
                    }
                }
            }

            $this->showForm = true;

        } catch (\Exception $e) {
            Log::error('Erreur selectAnalyse:', [
                'message' => $e->getMessage(),
                'analyse_id' => $analyseId
            ]);
        }
    }

    // Vérification de la validation d'une analyse
    public function isAnalyseValidated($analyseId){
        return Resultat::where([
            'prescription_id' => $this->prescription->id,
            'analyse_id' => $analyseId,
        ])->whereNotNull('validated_at')->exists();
    }

    // Mise à jour des résultats
    public function updatedResults($value, $key)
    {
        try {
            // Extraire l'ID de l'analyse du chemin de la clé (ex: "results.123.resultats")
            preg_match('/results\.(\d+)\./', $key, $matches);
            if (!empty($matches[1])) {
                $analyseId = $matches[1];
                $analyse = Analyse::find($analyseId);

                if ($analyse && $analyse->analyseType->name === 'SELECT_MULTIPLE') {
                    // S'assurer que la valeur est un tableau
                    if (!is_array($value)) {
                        $value = [$value];
                    }

                    // Mettre à jour les résultats
                    $this->results[$analyseId]['resultats'] = array_values(array_filter($value));
                }
            }

            // Sauvegarder l'état après chaque modification
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

    // ********************
    public function updatedSelectedOption($value){
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
                $this->resetBacteriaSelection();
                $this->showOtherInput = false;
            }
            // Si "autre" est sélectionné
            elseif (in_array('autre', $this->selectedOption)) {
                $this->selectedOption = ['autre'];
                $this->showOtherInput = true;
                $this->resetBacteriaSelection();
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

    private function resetBacteriaSelection(){
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
                // Ajouter l'ID du parent
                $allAnalyseIds->push($parentAnalyse->id);

                // Récupérer récursivement tous les IDs des enfants
                $this->collectChildAnalyseIds($parentAnalyse, $allAnalyseIds);
            }

            // Mettre à jour tous les résultats pour les analyses collectées
            $updatedCount = Resultat::where('prescription_id', $this->prescription->id)
                ->whereIn('analyse_id', $allAnalyseIds)
                ->update([
                    'validated_by' => Auth::id(),
                    'validated_at' => now()
                ]);

            if ($updatedCount === 0) {
                throw new \Exception("Aucun résultat n'a été validé.");
            }

            // Mettre à jour le statut de la prescription
            $this->prescription->update([
                'status' => Prescription::STATUS_VALIDE
            ]);

            DB::commit();

            // Log de succès
            // Log::info('Validation réussie:', [
            //     'prescription_id' => $this->prescription->id,
            //     'validated_by' => Auth::id(),
            //     'results_count' => $updatedCount,
            //     'analyses_count' => $allAnalyseIds->count()
            // ]);

            $this->dispatch('$refresh');
            session()->flash('success', 'Toutes les analyses ont été validées avec succès');

            return redirect()->route('biologiste.analyse.index');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur validation analyses:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'prescription_id' => $this->prescription->id,
                'user_id' => Auth::id()
            ]);
            session()->flash('error', $e->getMessage());
        }
    }

    // Méthode auxiliaire pour collecter récursivement les IDs des analyses enfants
    private function collectChildAnalyseIds($analyse, &$ids)
    {
        foreach ($analyse->children as $child) {
            $ids->push($child->id);

            // Si l'enfant a lui-même des enfants, on les collecte aussi
            if ($child->children->isNotEmpty()) {
                $this->collectChildAnalyseIds($child, $ids);
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
