<?php

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
    public $selectedParentAnalyse = null;
    public $results = [];
    public $validation;
    public $showForm = false;
    public $showOtherInput = [];
    public $showBactery = null;
    public $antibiotics_name = null;

    public function mount(Prescription $prescription)
    {
        $this->prescription = $prescription;
        $this->loadResults();
        $this->showForm = false;
        
    }

    private function loadResults()
    {
        /*$this->results = $this->prescription->resultats()
            ->with('analyse')
            ->get()
            ->keyBy('analyse_id')
            ->map(function ($resultat) {
                return [
                    'valeur' => $resultat->valeur,
                    'interpretation' => $resultat->interpretation
                ];
            })
            ->toArray();*/
            
        $result = Resultat::find($this->prescription->id);
        if($result){
            $this->results = json_decode($result->valeur, true);
            $this->showForm = true;
        }
            
    }

    public function selectParentAnalyse($analyseId)
    {
        $this->selectedParentAnalyse = Analyse::with(['allChildren.analyseType'])
            ->where('level', 'PARENT')
            ->where('parent_code', 0)
            ->findOrFail($analyseId);
        $this->showForm = true;
        $this->showBactery = BacteryFamily::all();
    }

    public function bacteries($bactery_name){
        $bactery_familly = BacteryFamily::all();
        foreach($bactery_familly as $bactery){
            $bacteriaArray = is_string($bactery->bacteries) ? json_decode($bactery->bacteries): $bactery->bacteries;
            foreach($bacteriaArray as $bacteri){
                if($bacteri == $bactery_name){
                    $bactery_familly_name = $bactery->name;
                    break;
                }
            }
        break;
        }

        $this->antibiotics_name = BacteryFamily::first('antibiotics',$bactery_familly_name);
        
    }

    public function saveResult($analyseId)
    {
        try{
           
            $analyses_parent = Analyse::where('id', $analyseId)->first();

            if($analyses_parent){
                $analyses_children = Analyse::where('parent_code', $analyses_parent->code)->get();
                foreach ($analyses_children as $child) {
                    $child_id[] = $child->id;
                }
            }

            
            
            try{
                foreach($child_id as $childId){
                     
                    $this->validate([
                        "results.{$childId}.valeur" => 'required',
                        "results.{$childId}.interpretation" => 'nullable',
                    ]);
                }
            } catch( \Exception $e){
                dd('veuillez remplir tout les champs ou choisir parmi la liste proposé');
            }
            
            // Initialiser un tableau pour stocker les valeurs associées
            $valuesToStore = [];

            // Récupérer les valeurs à partir de $this->results pour chaque enfant
            foreach ($child_id as $childId) {
                if (isset($this->results[$childId])) {
                    $valuesToStore[$childId] = $this->results[$childId]['valeur'] ?? null; // Récupérer la valeur correspondante
                }
            }
            // Convertir le tableau associatif en JSON
            $jsonResults = json_encode($valuesToStore);

            // Enregistrer les résultats dans la table Resultat
            Resultat::updateOrCreate(
                [
                    'prescription_id' => $this->prescription->id,
                    'analyse_id' => $analyseId // Utiliser l'ID de l'analyse parent
                ],
                [
                    'valeur' => $jsonResults, // Stocker les valeurs JSON dans la colonne "valeur"
                    'interpretation' => $this->results[$analyseId]['interpretation'] ?? null // Interprétation si elle existe
                ]
            );
            
            $search_prescription = Prescription::find($this->prescription->id);
            if($search_prescription){

                $search_prescription->status = Prescription::STATUS_TERMINE;
                $search_prescription->save();
            }
            
            $this->validation = true;
            $this->showForm = false;
            $this->dispatch('resultSaved');

        } catch (\Exception $e){
            dd($e->getMessage());
        }
        
    }

    public function validateAnalyse($analyseId){
        try{
            $prescription_validate = Resultat::where('prescription_id', $this->prescription->id)->get();
            foreach ($prescription_validate as $prescription){
                $prescription->validated_at = Carbon::now();
                $prescription->save();
            }
            
        } catch(\Exception $e){
            dd($e->getMessage());
        }
        
    }

    public function render()
    {
        $topLevelAnalyses = $this->prescription->analyses()
            ->where('level', 'PARENT')
            ->where('parent_code', 0)
            ->orderBy('ordre')
            ->get();

        return view('livewire.technicien.details-prescription', [
            'topLevelAnalyses' => $topLevelAnalyses
        ]);
    }
}
