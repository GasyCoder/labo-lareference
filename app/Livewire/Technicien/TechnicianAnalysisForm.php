<?php
// app/Livewire/Technicien/TechnicianAnalysisForm.php
namespace App\Livewire\Technicien;

use Livewire\Component;
use App\Models\Prescription;
use App\Models\Analyse;
use App\Models\Resultat;
use App\Models\BacteryFamily;
use Carbon\Carbon;

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

    public function saveResult($analyseId)
    {
        try {
            // Validation
            
            $analyse = Analyse::findOrFail($analyseId);
            $analyses_children = Analyse::where('parent_code', $analyse->code)->get();
            $child_ids = $analyses_children->pluck('id')->toArray();

            $validationRules = [];
            $id_child = [];
           
            //Fonction pour rechercher toutes les enfants de l'analyse parent
            function searchChild($child_ids, &$id_child, &$test){
                foreach($child_ids as $childId){
                    $Analysechild = Analyse::findOrFail($childId);
                    if($Analysechild->children->isNotEmpty()){
                        $analyse = Analyse::findOrFail($childId);
                        $analyses_children = Analyse::where('parent_code', $analyse->code)->get();
                        $child_ids = $analyses_children->pluck('id')->toArray();
                        searchChild($child_ids,  $id_child, $test);
                    } else{
                        $id_child [$childId]= $childId;
                    }
                }
            }
            
            //Appel de la fonction searchChild
            searchChild($child_ids, $id_child, $test);

            //Boucle pour insérer la valeur de la select germe dans l'id du variable 'results' de l'enfant qui  a le germe   
            foreach($id_child as $id_germe){
                $analyse = Analyse::findOrFail($id_germe);
                if($analyse->analyse_type_id == 15){
                    $this->results[$id_germe]['valeur'] = $this->selectedBacteriaResults[$this->currentBacteria];
                } 
            }
            
            //Vérification que les champs soit bien  remplis
            foreach ($id_child as $childId) {
                $validationRules["results.{$childId}.valeur"] = 'required';
                $validationRules["results.{$childId}.interpretation"] = 'nullable';
                
            }
            
            
            $this->validate($validationRules, [
                'results.*.valeur.required' => 'Ce champ est obligatoire'
            ]);

            // Préparer les données
            $valuesToStore = [
                'option_speciale' => $this->selectedOption,
                'autre_valeur' => $this->otherBacteriaValue,
                'conclusion' => $this->conclusion
            ];
            
            foreach ($id_child as $childId) {
                if (isset($this->results[$childId])) {
                    $valuesToStore[$childId] = [
                        'valeur' => $this->results[$childId]['valeur'],
                        'interpretation' => $this->results[$childId]['interpretation'] ?? null
                    ];
                }
            }

            if (!empty($this->selectedBacteriaResults)) {
                $valuesToStore['bacteries'] = $this->selectedBacteriaResults;
            }

            // Sauvegarder
            Resultat::updateOrCreate(
                [
                    'prescription_id' => $this->prescription->id,
                    'analyse_id' => $analyseId
                ],
                [
                    'valeur' => json_encode($valuesToStore),
                    'interpretation' => $this->results[$analyseId]['interpretation'] ?? null
                ]
            );

            $this->prescription->update(['status' => Prescription::STATUS_TERMINE]);
            $this->validation = true;
            $this->showForm = false;
            $this->dispatch('resultSaved');
            session()->flash('success', 'Résultats enregistrés avec succès');

        } catch (\Exception $e) {
            //session()->flash('error', "Erreur lors de l'enregistrement: " . $e->getMessage());
            dd($e->getMessage());
        }
    }

    public function validateAnalyse()
    {
        try {
            Resultat::where('prescription_id', $this->prescription->id)
                ->update(['validated_at' => Carbon::now()]);

            $this->prescription->update(['status' => Prescription::STATUS_VALIDE]);
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
}
