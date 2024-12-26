<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Examen;
use Illuminate\Support\Facades\Log;

class ExamenTest extends Component
{
    public $examens = []; // Pour stocker les examens avec analyses

    public function mount()
    {
        try {
            // Récupérer tous les examens avec leurs analyses
            $this->examens = Examen::with('analyses.children')->get();

            Log::info('Examens récupérés avec toutes les analyses', $this->examens->toArray());
        } catch (\Exception $e) {
            Log::error('Erreur : ' . $e->getMessage());
            session()->flash('error', 'Erreur lors de la récupération des examens.');
        }
    }

    public function render()
    {
        return view('livewire.index-examen', [
            'examens' => $this->examens,
        ]);
    }
}
