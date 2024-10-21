<?php

namespace App\Livewire\Secretaire;

use App\Models\Patient;
use App\Models\Prescription;
use App\Models\AnalysePrescription;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PatientPrescription extends Component
{
    use WithPagination, LivewireAlert, AuthorizesRequests;

    protected $paginationTheme = 'bootstrap';

    public $prescriptionId;

    protected $listeners = [
        'prescriptionAdded' => '$refresh',
        'deleteConfirmed' => 'deletePrescription',
        'restorePrescription',
        'permanentDeletePrescription'
    ];

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
        $activePrescriptions = Prescription::with(['patient', 'prescripteur', 'analyses'])
            ->whereHas('patient', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->where('status', '!=', Prescription::STATUS_ARCHIVE)
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

            // Prescriptions valide
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
            ->latest()
            ->paginate(10, ['*'], 'valide_page');

        // Prescriptions deleted_at
        $deletedPrescriptions = Prescription::with(['patient', 'prescripteur', 'analyses'])
            ->onlyTrashed() // Ceci sélectionne uniquement les enregistrements soft deleted
            ->where(function ($query) use ($search) {
                $query->where('renseignement_clinique', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%")
                    ->orWhere('nouveau_prescripteur_nom', 'like', "%$search%")
                    ->orWhereHas('patient', function ($patientQuery) use ($search) {
                        $patientQuery->where('nom', 'like', "%$search%")
                            ->orWhere('prenom', 'like', "%$search%")
                            ->orWhere('telephone', 'like', "%$search%");
                    })
                    ->orWhereHas('prescripteur', function ($prescripteurQuery) use ($search) {
                        $prescripteurQuery->where('name', 'like', "%$search%")
                            ->whereHas('roles', function ($roleQuery) {
                                $roleQuery->where('name', 'prescripteur');
                            });
                    });
            })
            ->latest()
            ->paginate(10, ['*'], 'deleted_page');

        return view('livewire.secretaire.patient-prescription', [
            'activePrescriptions' => $activePrescriptions,
            'analyseValides'=> $analyseValides,
            'deletedPrescriptions' => $deletedPrescriptions,
        ]);
    }

    public function edit($prescriptionId)
    {
        $this->dispatch('editPrescription', $prescriptionId);
    }

    public function confirmDelete($prescriptionId)
    {
        $this->prescriptionId = $prescriptionId;
        $this->confirm('Êtes-vous sûr de vouloir mettre cette prescription en corbeille ?', [
            'toast' => true,
            'position' => 'center',
            'showConfirmButton' => true,
            'confirmButtonText' => 'Oui, corbeille',
            'cancelButtonText' => 'Annuler',
            'onConfirmed' => 'deleteConfirmed',
        ]);
    }

    public function deletePrescription()
    {
        try {
            $prescription = Prescription::findOrFail($this->prescriptionId);

            // Effectuer uniquement le soft delete
            $prescription->delete();

            $this->alert('success', 'Prescription mise en corbeille avec succès.');
            $this->dispatch('prescriptionDeleted');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la mise en corbeille de la prescription: ' . $e->getMessage());
            $this->alert('error', 'Une erreur est survenue lors de la mise en corbeille: ' . $e->getMessage());
        }

        $this->prescriptionId = null;
    }

    public function confirmRestore($prescriptionId)
    {
        $this->prescriptionId = $prescriptionId;
        $this->confirm('Êtes-vous sûr de vouloir restaurer cette prescription ?', [
            'toast' => true,
            'position' => 'center',
            'confirmButtonText' => 'Oui, restaurer',
            'onConfirmed' => 'restorePrescription',
            'onCancelled' => 'cancelled'
        ]);
    }

    public function restorePrescription()
    {
        try {
            $prescription = Prescription::withTrashed()->findOrFail($this->prescriptionId);

            // Restaurer la prescription sans modifier son statut
            $prescription->restore();

            $this->alert('success', 'Prescription restaurée avec succès.');
            $this->dispatch('prescriptionRestored');
        } catch (\Exception $e) {
            $this->alert('error', 'Erreur lors de la restauration : ' . $e->getMessage());
        }
    }

    public function confirmPermanentDelete($prescriptionId)
    {
        $this->prescriptionId = $prescriptionId;
        $this->confirm('Êtes-vous sûr de vouloir supprimer définitivement cette prescription ?', [
            'toast' => true,
            'position' => 'center',
            'onConfirmed' => 'permanentDeletePrescription',
            'onCancelled' => 'cancelled'
        ]);
    }

    public function permanentDeletePrescription()
    {
        try {
            $prescription = Prescription::withTrashed()->findOrFail($this->prescriptionId);
            $prescription->forceDelete();

            $this->alert('success', 'Prescription supprimée définitivement.');
            $this->dispatch('prescriptionPermanentlyDeleted');
        } catch (\Exception $e) {
            $this->alert('error', 'Erreur lors de la suppression définitive : ' . $e->getMessage());
        }
    }
    public function cancelled()
    {
        $this->alert('info', 'Action annulée');
    }
}
