<?php

namespace App\Livewire\Biologiste;

use App\Models\Analyse;
use App\Models\Resultat;
use App\Models\Prescription;
use Livewire\Component;

class AnalyseShow extends Component
{
    public Prescription $prescription;
    public $mainAnalyses;
    public $results;

    public function mount(Prescription $prescription)
    {
        $this->prescription = $prescription;

        // Charger les analyses principales avec leurs enfants
        $this->mainAnalyses = $prescription->analyses()
            ->with(['analyseType', 'allChildren.analyseType'])
            ->orderBy('ordre')
            ->get()
            ->groupBy('parent_code');

        // Charger tous les résultats
        $this->results = Resultat::where('prescription_id', $prescription->id)
            ->get()
            ->keyBy('analyse_id');
    }

    public function render()
    {
        return view('livewire.biologiste.analyse-show')
            ->layout('layouts.app');
    }
}
