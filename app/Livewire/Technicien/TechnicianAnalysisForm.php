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
    public $selectedAnalyse = null;
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
        $result = Resultat::where('prescription_id', $this->prescription->id)->first();
        if ($result) {
            $this->results = json_decode($result->valeur, true) ?: [];
            $this->showForm = true;
        }
    }

    public function selectAnalyse($analyseId)
    {
        try {
            // Charger l'analyse sélectionnée sans filtres
            $this->selectedAnalyse = Analyse::with(['allChildren.analyseType'])
                ->findOrFail($analyseId);

            $this->showForm = true;
            $this->showBactery = BacteryFamily::all();

            // Charger les résultats existants pour cette analyse
            $existingResult = Resultat::where([
                'prescription_id' => $this->prescription->id,
                'analyse_id' => $analyseId
            ])->first();

            if ($existingResult) {
                $this->results = json_decode($existingResult->valeur, true) ?: [];
            }

        } catch (\Exception $e) {
            session()->flash('error', "Erreur lors de la sélection de l'analyse: " . $e->getMessage());
        }
    }

    public function bacteries($bactery_name)
    {
        try {
            $bactery_familly = BacteryFamily::all();
            $bactery_familly_name = null;

            foreach ($bactery_familly as $bactery) {
                $bacteriaArray = is_string($bactery->bacteries) ?
                    json_decode($bactery->bacteries) : $bactery->bacteries;

                if (in_array($bactery_name, $bacteriaArray)) {
                    $bactery_familly_name = $bactery->name;
                    break;
                }
            }

            if ($bactery_familly_name) {
                $this->antibiotics_name = BacteryFamily::where('name', $bactery_familly_name)
                    ->value('antibiotics');
            }
        } catch (\Exception $e) {
            session()->flash('error', "Erreur lors de la sélection de la bactérie: " . $e->getMessage());
        }
    }

    public function saveResult($analyseId)
    {
        try {
            // Récupérer l'analyse sans filtres
            $analyse = Analyse::findOrFail($analyseId);

            // Récupérer tous les enfants
            $analyses_children = Analyse::where('parent_code', $analyse->code)->get();
            $child_ids = $analyses_children->pluck('id')->toArray();

            // Validation
            $validationRules = [];
            foreach ($child_ids as $childId) {
                $validationRules["results.{$childId}.valeur"] = 'required';
                $validationRules["results.{$childId}.interpretation"] = 'nullable';
            }

            $this->validate($validationRules, [
                'results.*.valeur.required' => 'Ce champ est obligatoire'
            ]);

            // Préparer les données
            $valuesToStore = [];
            foreach ($child_ids as $childId) {
                if (isset($this->results[$childId])) {
                    $valuesToStore[$childId] = [
                        'valeur' => $this->results[$childId]['valeur'],
                        'interpretation' => $this->results[$childId]['interpretation'] ?? null
                    ];
                }
            }

            // Sauvegarder le résultat
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

            // Mettre à jour le statut de la prescription
            $this->prescription->update(['status' => Prescription::STATUS_TERMINE]);

            $this->validation = true;
            $this->showForm = false;
            $this->dispatch('resultSaved');
            session()->flash('success', 'Résultats enregistrés avec succès');

        } catch (\Exception $e) {
            session()->flash('error', "Erreur lors de l'enregistrement: " . $e->getMessage());
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
        // Récupérer toutes les analyses sans filtres
        $topLevelAnalyses = $this->prescription->analyses()
            ->orderBy('ordre')
            ->get()
            ->groupBy('parent_code');

        return view('livewire.technicien.details-prescription', [
            'topLevelAnalyses' => $topLevelAnalyses[0] ?? collect(), // analyses racines
            'childAnalyses' => $topLevelAnalyses->forget(0) ?? collect() // sous-analyses
        ]);
    }
}
