<?php

namespace App\Livewire\Technicien;

use Livewire\Component;
use App\Models\Prescription;
use App\Models\Analyse;
use App\Models\Resultat;

class TechnicianAnalysisForm extends Component
{
    public Prescription $prescription;
    public $selectedParentAnalyse = null;
    public $results = [];

    public function mount(Prescription $prescription)
    {
        $this->prescription = $prescription;
        $this->loadResults();
    }

    private function loadResults()
    {
        $this->results = $this->prescription->resultats()
            ->with('analyse')
            ->get()
            ->keyBy('analyse_id')
            ->map(function ($resultat) {
                return [
                    'valeur' => $resultat->valeur,
                    'interpretation' => $resultat->interpretation
                ];
            })
            ->toArray();
    }

    public function selectParentAnalyse($analyseId)
    {
        $this->selectedParentAnalyse = Analyse::with(['allChildren.analyseType'])
            ->where('level', 'PARENT')
            ->where('parent_code', 0)
            ->findOrFail($analyseId);
    }

    public function saveResult($analyseId)
    {
        $this->validate([
            "results.{$analyseId}.valeur" => 'required',
            "results.{$analyseId}.interpretation" => 'nullable',
        ]);

        Resultat::updateOrCreate(
            [
                'prescription_id' => $this->prescription->id,
                'analyse_id' => $analyseId
            ],
            [
                'valeur' => $this->results[$analyseId]['valeur'],
                'interpretation' => $this->results[$analyseId]['interpretation'] ?? null
            ]
        );

        $this->dispatch('resultSaved');
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
