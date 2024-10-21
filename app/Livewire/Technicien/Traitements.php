<?php

namespace App\Livewire\Technicien;

use Livewire\Component;
use App\Models\Prescription;
use Livewire\WithPagination;

class Traitements extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';


    public $search = '';

    protected $queryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function render()
    {
        $search = '%' . $this->search . '%';
        // Prescriptions actives
        $analyseEntentes = Prescription::with(['patient', 'prescripteur', 'analyses'])
            ->whereHas('patient', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->where('status', '=', Prescription::STATUS_EN_ATTENTE)
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
            ->latest()
            ->paginate(10);


            // Prescriptions terminé
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
            ->latest()
            ->paginate(10, ['*'], 'termine_page');


        return view('livewire.technicien.traitements', [
            'analyseEntentes' => $analyseEntentes,
            'analyseTermines'=> $analyseTermines,
        ]);
    }

    public function openPrescription($prescriptionId)
    {
        $prescription = Prescription::findOrFail($prescriptionId);

        if ($prescription->status === Prescription::STATUS_EN_ATTENTE) {
            $prescription->status = Prescription::STATUS_EN_COURS;
            $prescription->save();

            $this->dispatch('prescriptionStatusUpdated');
        }

        return $this->redirect(route('technicien.traitement.show', ['prescription' => $prescription]));
    }

}
