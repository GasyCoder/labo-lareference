<?php
// app/Livewire/Technicien/TechnicianAnalysisForm.php
namespace App\Livewire\Technicien;

use Carbon\Carbon;
use App\Models\Analyse;
use Livewire\Component;
use App\Models\Resultat;
use App\Models\Prescription;
use App\Models\BacteryFamily;
use Illuminate\Support\Facades\DB;

class TechnicianAnalysisForm extends Component
{
    public Prescription $prescription;
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

    public function mount(Prescription $prescription)
    {
        $this->prescription = $prescription;
        $this->loadResults();
    }

    private function loadResults()
    {
        $result = Resultat::where('prescription_id', $this->prescription->id)->first();
        if ($result) {
            $decodedResults = json_decode($result->valeur, true) ?: [];
            $this->results = $decodedResults;
            $this->showForm = true;

            // Restaurer les états
            if (isset($decodedResults['option_speciale'])) {
                $this->selectedOption = $decodedResults['option_speciale'];
                $this->showOtherInput = in_array('autre', (array)$this->selectedOption);
                $this->otherBacteriaValue = $decodedResults['autre_valeur'] ?? '';
            }

            // Restaurer les états pour Présence/Absence
            foreach ($this->results as $analyseId => $data) {
                if (isset($data['valeur']) && $data['valeur'] === 'Présence') {
                    $this->showPresenceInputs[$analyseId] = true;
                }
            }

            if (isset($decodedResults['bacteries'])) {
                $this->selectedBacteriaResults = $decodedResults['bacteries'];
            }
            if (isset($decodedResults['conclusion'])) {
                $this->conclusion = $decodedResults['conclusion'];
            }
        }
    }

    public function selectAnalyse($analyseId)
    {
        try {
            $this->selectedAnalyse = Analyse::with(['allChildren.analyseType'])
                ->findOrFail($analyseId);
            $this->showForm = true;
            $this->showBactery = BacteryFamily::all();

            $existingResult = Resultat::where([
                'prescription_id' => $this->prescription->id,
                'analyse_id' => $analyseId
            ])->first();

            if ($existingResult) {
                $this->loadResults();
            } else {
                $this->resetStates();
            }
        } catch (\Exception $e) {
            session()->flash('error', "Erreur lors de la sélection de l'analyse: " . $e->getMessage());
        }
    }

    private function resetStates()
    {
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
    }

    public function updatedResults($value, $key)
    {
        // Détecter si c'est un changement de valeur pour NEGATIF_POSITIF_3
        if (str_contains($key, '.valeur')) {
            $analyseId = explode('.', $key)[1];

            // Vérifier si l'analyse correspondante est de type NEGATIF_POSITIF_3
            $analyse = Analyse::find($analyseId);
            if ($analyse && $analyse->analyseType->name === 'NEGATIF_POSITIF_3') {
                if ($value === 'Presence') {
                    $this->showPresenceInputs[$analyseId] = true;
                } else {
                    $this->showPresenceInputs[$analyseId] = false;
                    // Réinitialiser l'interprétation
                    if (isset($this->results[$analyseId])) {
                        $this->results[$analyseId]['interpretation'] = null;
                    }
                }
            }
        }
    }

    public function updatedSelectedOption($value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        $this->selectedOption = array_filter($value);

        // Gérer l'affichage/masquage des éléments
        $specialOptions = ['non-rechercher', 'en-cours', 'culture-sterile', 'absence'];
        $hasSpecialOption = !empty(array_intersect($specialOptions, $this->selectedOption));

        if ($hasSpecialOption || in_array('autre', $this->selectedOption)) {
            $this->resetBacterySelection();
        }

        $this->showOtherInput = in_array('autre', $this->selectedOption);
    }

    private function resetBacterySelection()
    {
        $this->showAntibiotics = false;
        $this->antibiotics_name = null;
        $this->currentBacteria = null;
        $this->selectedBacteriaResults = [];
    }

    public function bacteries($bactery_name)
    {
        try {
            // Réinitialiser les options spéciales
            $this->selectedOption = [$bactery_name];
            $this->showOtherInput = false;
            $this->currentBacteria = $bactery_name;

            $bactery_familly = BacteryFamily::all();
            $bactery_familly_name = null;

            foreach ($bactery_familly as $bactery) {
                $bacteriaArray = is_string($bactery->bacteries) ?
                    json_decode($bactery->bacteries) :
                    $bactery->bacteries;

                if (in_array($bactery_name, $bacteriaArray)) {
                    $bactery_familly_name = $bactery->name;
                    break;
                }
            }

            if ($bactery_familly_name) {
                $antibiotics = BacteryFamily::where('name', $bactery_familly_name)
                    ->value('antibiotics');

                $this->antibiotics_name = is_string($antibiotics) ?
                    json_decode($antibiotics, true) :
                    $antibiotics;

                $this->showAntibiotics = true;

                if (!isset($this->selectedBacteriaResults[$bactery_name])) {
                    $this->selectedBacteriaResults[$bactery_name] = [
                        'name' => $bactery_name,
                        'antibiotics' => []
                    ];
                }
            }
        } catch (\Exception $e) {
            session()->flash('error', "Erreur lors de la sélection de la bactérie: " . $e->getMessage());
        }
    }

    public function updateAntibiogramResult($antibiotic, $sensitivity)
    {
        if ($this->currentBacteria) {
            $this->selectedBacteriaResults[$this->currentBacteria]['antibiotics'][$antibiotic] = $sensitivity;
        }
    }

    public function validateAnalyse()
    {
        try {
            Resultat::where('prescription_id', $this->prescription->id)
                ->update(['validated_at' => Carbon::now()]);

            $this->prescription->update(['status' => Prescription::STATUS_TERMINE]);
            session()->flash('success', 'Analyse validée avec succès');
        } catch (\Exception $e) {
            session()->flash('error', "Erreur lors de la validation: " . $e->getMessage());
        }
    }

    public function render()
    {
        $topLevelAnalyses = $this->prescription->analyses()
            ->orderBy('ordre')
            ->get()
            ->groupBy('parent_code');

        return view('livewire.technicien.details-prescription', [
            'topLevelAnalyses' => $topLevelAnalyses[0] ?? collect(),
            'childAnalyses' => $topLevelAnalyses->forget(0) ?? collect()
        ]);
    }



    public function saveResult($analyseId)
    {
        try {
            $analyse = Analyse::findOrFail($analyseId);
            $analyses_children = Analyse::where('parent_code', $analyse->code)->get();
            $child_ids = $analyses_children->pluck('id')->toArray();
            $id_child = [];

            function searchChild($child_ids, &$id_child) {
                foreach($child_ids as $childId) {
                    $analyseChild = Analyse::findOrFail($childId);
                    if($analyseChild->children->isNotEmpty()) {
                        $analyse = Analyse::findOrFail($childId);
                        $analyses_children = Analyse::where('parent_code', $analyse->code)->get();
                        $child_ids = $analyses_children->pluck('id')->toArray();
                        searchChild($child_ids, $id_child);
                    } else {
                        $id_child[$childId] = $childId;
                    }
                }
            }

            searchChild($child_ids, $id_child);

            DB::beginTransaction();

            try {
                // Sauvegarde du résultat parent

                $mainResultData = [
                    'option_speciale' => $this->selectedOption,
                    'autre_valeur' => $this->otherBacteriaValue,
                ];

                if (!empty($this->selectedBacteriaResults)) {
                    $mainResultData['bacteries'] = $this->selectedBacteriaResults;
                }

                // Pour chaque analyse enfant
                foreach ($id_child as $childId) {
                    if (isset($this->results[$childId])) {
                        $analyseChild = Analyse::find($childId);
                        if (!$analyseChild || !$analyseChild->analyseType) continue;

                        $resultData = [
                            'prescription_id' => $this->prescription->id,
                            'analyse_id' => $childId
                        ];

                        if ($analyseChild->analyseType->name === 'GERME') {
                            $germeValue = $this->processGermeValue();
                            $resultData['valeur'] = json_encode($germeValue, JSON_UNESCAPED_UNICODE);
                            $resultData['interpretation'] = null;
                        } else {

                            // Traitement des autres types d'analyses
                            $value = $this->results[$childId]['valeur'] ?? null;
                            $interpretation = $this->results[$childId]['interpretation'] ?? null;


                            switch ($analyseChild->analyseType->name) {
                                case 'DOSAGE':
                                case 'COMPTAGE':
                                case 'NEGATIF_POSITIF_1':
                                    // Si la valeur est NORMAL ou PATHOLOGIQUE, c'est l'interprétation
                                    if (in_array($value, ['NORMAL', 'PATHOLOGIQUE'])) {
                                        $interpretation = $value;
                                        $value = $this->results[$childId]['valeur'] ?? null;
                                    }
                                    break;

                                case 'TEST':
                                    $interpretation = $value === 'POSITIF' ? 'PATHOLOGIQUE' : 'NORMAL';
                                    break;
                            }

                            $resultData['valeur'] = $value;
                            $resultData['interpretation'] = $interpretation;
                        }

                        $resultData['conclusion'] = null;

                        Resultat::updateOrCreate(
                            [
                                'prescription_id' => $this->prescription->id,
                                'analyse_id' => $childId
                            ],
                            $resultData
                        );
                    }
                }

                // Sauvegarde de l'analyse parent
                Resultat::updateOrCreate(
                    [
                        'prescription_id' => $this->prescription->id,
                        'analyse_id' => $analyseId
                    ],
                    [
                        'valeur' => json_encode($mainResultData, JSON_UNESCAPED_UNICODE),
                        'interpretation' => null,
                        'conclusion' => $this->conclusion
                    ]
                );

                $this->prescription->update(['status' => Prescription::STATUS_TERMINE]);
                DB::commit();

                $this->validation = true;
                $this->showForm = false;
                $this->dispatch('resultSaved');
                session()->flash('success', 'Résultats enregistrés avec succès');

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            session()->flash('error', "Erreur lors de l'enregistrement: " . $e->getMessage());
        }
    }

    private function processGermeValue()
    {
        $germeValue = [];

        if (!empty($this->selectedOption)) {
            $firstOption = $this->selectedOption[0];
            if (in_array($firstOption, ['non-rechercher', 'en-cours', 'culture-sterile', 'absence'])) {
                $germeValue = [
                    'status' => $firstOption
                ];
            } elseif ($this->currentBacteria) {
                $germeValue = [
                    'status' => 'bacterie',
                    'bacterie' => [
                        'nom' => $this->currentBacteria,
                        'antibiogramme' => $this->selectedBacteriaResults[$this->currentBacteria]['antibiotics'] ?? []
                    ]
                ];
            }
        }

        if (in_array('autre', $this->selectedOption)) {
            $germeValue['status'] = 'autre';
            $germeValue['autre_valeur'] = $this->otherBacteriaValue;
        }

        return $germeValue;
    }

}
