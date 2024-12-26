<?php

namespace App\Livewire\Biologiste;

use Livewire\Component;
use App\Models\Prescription;
use Livewire\WithPagination;

class AnalyseValide extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $tab = 'termine';
    public $search = '';

    protected $queryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedTab()
    {
        $this->resetPage(); // Réinitialise la pagination lors du changement d'onglet
    }

    public function openAnalyse($prescriptionId)
    {
        $prescription = Prescription::findOrFail($prescriptionId);

        return $this->redirect(route('biologiste.valide.show',
        ['prescription' => $prescription]));
    }

    public function render()
    {
        $search = '%' . $this->search . '%';

        // Prescriptions valides
        $analyseValides = Prescription::with(['patient', 'prescripteur', 'analyses'])
            ->whereHas('patient', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->where('status', '=', Prescription::STATUS_VALIDE)
            ->where(function ($query) use ($search) {
                $query->where('renseignement_clinique', 'like', $search)
                    ->orWhere('status', 'like', $search)
                    ->orWhere('nouveau_prescripteur_nom', 'like', $search)
                    ->orWhereHas('patient', function ($patientQuery) use ($search) {
                        $patientQuery->where('nom', 'like', $search)
                            ->orWhere('prenom', 'like', $search)
                            ->orWhere('telephone', 'like', $search);
                    })
                    ->orWhereHas('prescripteur', function ($prescripteurQuery) use ($search) {
                        $prescripteurQuery->where('name', 'like', $search)
                            ->whereHas('roles', function ($roleQuery) {
                                $roleQuery->where('name', 'prescripteur');
                            });
                    });
            })
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        // Prescriptions terminées
        $analyseTermines = Prescription::with(['patient', 'prescripteur', 'analyses'])
            ->whereHas('patient', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->where('status', '=', Prescription::STATUS_TERMINE)
            ->where(function ($query) use ($search) {
                $query->where('renseignement_clinique', 'like', $search)
                    ->orWhere('status', 'like', $search)
                    ->orWhere('nouveau_prescripteur_nom', 'like', $search)
                    ->orWhereHas('patient', function ($patientQuery) use ($search) {
                        $patientQuery->where('nom', 'like', $search)
                            ->orWhere('prenom', 'like', $search)
                            ->orWhere('telephone', 'like', $search);
                    })
                    ->orWhereHas('prescripteur', function ($prescripteurQuery) use ($search) {
                        $prescripteurQuery->where('name', 'like', $search)
                            ->whereHas('roles', function ($roleQuery) {
                                $roleQuery->where('name', 'prescripteur');
                            });
                    });
            })
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        return view('livewire.biologiste.analyse-valide', [
            'analyseValides' => $analyseValides,
            'analyseTermines' => $analyseTermines,
        ]);
    }
}
