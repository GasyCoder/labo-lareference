<?php

namespace App\Livewire\Secretaire;

use App\Models\Patient;
use Livewire\Component;
use App\Models\Prescription;
use Livewire\WithPagination;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ResultatPdfShow;
use Illuminate\Support\Facades\DB;
use App\Models\AnalysePrescription;
use App\Services\ResultatPdfService;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PatientPrescription extends Component
{
    use WithPagination, LivewireAlert, AuthorizesRequests;
    public ?Prescription $prescription = null;
    protected $paginationTheme = 'bootstrap';

    public $prescriptionId;

    protected $pdfService;

    public function boot(ResultatPdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }


    protected $listeners = [
        'prescriptionAdded' => '$refresh',
        'deleteConfirmed' => 'deletePrescription',
        'restorePrescription',
        'permanentDeletePrescription',
        'archivePrescription',
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

        // Prescriptions actives (non validées et non archivées)
        $activePrescriptions = Prescription::with([
            'patient' => function($query) {
                $query->select('id', 'ref', 'nom', 'prenom', 'telephone');
            },
            'prescripteur',
            'analyses',
            'resultats'
        ])
        ->whereHas('patient', function ($query) {
            $query->whereNull('deleted_at');
        })
        ->where('is_archive', false)
        ->where('status', '!=', Prescription::STATUS_VALIDE)
        ->where(function ($query) use ($search) {
            $query->where('renseignement_clinique', 'like', $search)
                ->orWhere('status', 'like', $search)
                ->orWhere('nouveau_prescripteur_nom', 'like', $search)
                ->orWhereHas('patient', function ($query) use ($search) {
                    $query->where('nom', 'like', $search)
                        ->orWhere('prenom', 'like', $search)
                        ->orWhere('telephone', 'like', $search);
                })
                ->orWhereHas('prescripteur', function ($query) use ($search) {
                    $query->where('name', 'like', $search)
                        ->whereHas('roles', function ($query) {
                            $query->where('name', 'prescripteur');
                        });
                });
        })
        ->orderBy('created_at', 'desc')
        ->paginate(15);

        // Prescriptions validées (non archivées)
        $analyseValides = Prescription::with([
            'patient' => function($query) {
                $query->select('id', 'ref', 'nom', 'prenom', 'telephone');
            },
            'prescripteur',
            'analyses',
            'resultats'
        ])
        ->whereHas('patient', function ($query) {
            $query->whereNull('deleted_at');
        })
        ->where('is_archive', false)
        ->where('status', Prescription::STATUS_VALIDE)
        ->whereDoesntHave('analyses', function ($query) {
            $query->whereDoesntHave('resultats', function ($query) {
                $query->whereNotNull('validated_by');
            });
        })
        ->where(function ($query) use ($search) {
            $query->where('renseignement_clinique', 'like', $search)
                ->orWhere('status', 'like', $search)
                ->orWhere('nouveau_prescripteur_nom', 'like', $search)
                ->orWhereHas('patient', function ($query) use ($search) {
                    $query->where('nom', 'like', $search)
                        ->orWhere('prenom', 'like', $search)
                        ->orWhere('telephone', 'like', $search);
                });
        })
        ->orderBy('created_at', 'desc')
        ->paginate(15, ['*'], 'valide_page');

        // Prescriptions archivées
        $archivedPrescriptions = Prescription::with([
            'patient' => function($query) {
                $query->select('id', 'ref', 'nom', 'prenom', 'telephone');
            },
            'prescripteur',
            'analyses',
            'resultats'
        ])
        ->whereHas('patient', function ($query) {
            $query->whereNull('deleted_at');
        })
        ->where('is_archive', true)
        ->where('status', Prescription::STATUS_VALIDE)
        ->where(function ($query) use ($search) {
            $query->where('renseignement_clinique', 'like', $search)
                ->orWhere('status', 'like', $search)
                ->orWhere('nouveau_prescripteur_nom', 'like', $search)
                ->orWhereHas('patient', function ($query) use ($search) {
                    $query->where('nom', 'like', $search)
                        ->orWhere('prenom', 'like', $search)
                        ->orWhere('telephone', 'like', $search);
                });
        })
        ->orderBy('created_at', 'desc')
        ->paginate(15, ['*'], 'archive_page');

        // Prescriptions dans la corbeille
        $deletedPrescriptions = Prescription::with([
            'patient' => function($query) {
                $query->select('id', 'ref', 'nom', 'prenom', 'telephone');
            },
            'prescripteur',
            'analyses'
        ])
        ->onlyTrashed()
        ->where(function ($query) use ($search) {
            $query->where('renseignement_clinique', 'like', $search)
                ->orWhere('status', 'like', $search)
                ->orWhere('nouveau_prescripteur_nom', 'like', $search)
                ->orWhereHas('patient', function ($query) use ($search) {
                    $query->where('nom', 'like', $search)
                        ->orWhere('prenom', 'like', $search)
                        ->orWhere('telephone', 'like', $search);
                });
        })
        ->orderBy('created_at', 'desc')
        ->paginate(15, ['*'], 'deleted_page');

        return view('livewire.secretaire.patient-prescription', [
            'activePrescriptions' => $activePrescriptions,
            'analyseValides' => $analyseValides,
            'archivedPrescriptions' => $archivedPrescriptions,
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
            return redirect()->route('secretaire.patients.index');

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
            return redirect()->route('secretaire.patients.index');
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
            return redirect()->route('secretaire.patients.index');

        } catch (\Exception $e) {
            $this->alert('error', 'Erreur lors de la suppression définitive : ' . $e->getMessage());
        }
    }
    public function cancelled()
    {
        $this->alert('info', 'Action annulée');
    }


    public function confirmArchive($prescriptionId)
    {
        $this->prescriptionId = $prescriptionId;
        $this->confirm('Voulez-vous archiver cette prescription ?', [
            'toast' => true,
            'position' => 'center',
            'showConfirmButton' => true,
            'confirmButtonText' => 'Oui, archiver',
            'cancelButtonText' => 'Annuler',
            'onConfirmed' => 'archivePrescription'
        ]);
    }

    public function archivePrescription()
    {
        try {
            $prescription = Prescription::findOrFail($this->prescriptionId);

            if ($prescription->status === 'VALIDE' && $prescription->hasValidatedResultsByBiologiste()) {
                $prescription->archive();
                $this->alert('success', 'Prescription archivée avec succès.');
                return redirect()->route('secretaire.patients.index');
            }

        } catch (\Exception $e) {
            $this->alert('error', 'Erreur lors de l\'archivage : ' . $e->getMessage());
        }
    }

    public function confirmUnarchive($prescriptionId)
    {
        $this->prescriptionId = $prescriptionId;
        $this->confirm('Voulez-vous désarchiver cette prescription ?', [
            'toast' => true,
            'position' => 'center',
            'showConfirmButton' => true,
            'confirmButtonText' => 'Oui, désarchiver',
            'cancelButtonText' => 'Annuler',
            'onConfirmed' => 'unarchivePrescription'
        ]);
    }

    public function unarchivePrescription()
    {
        try {
            $prescription = Prescription::findOrFail($this->prescriptionId);
            $prescription->unarchive();
            $this->alert('success', 'Prescription désarchivée avec succès.');
        } catch (\Exception $e) {
            $this->alert('error', 'Erreur lors du désarchivage : ' . $e->getMessage());
        }
    }


    // Ajouter une méthode pour générer le PDF pour une prescription spécifique
    public function generateResultatsPDF($prescriptionId)
    {
        try {
            $prescription = Prescription::findOrFail($prescriptionId);

            // Utiliser le service pour générer le PDF
            $pdf = $this->pdfService->generatePDF($prescription);

            // Créer un nom de fichier unique
            $filename = 'resultats/prescription-' . $prescriptionId . '-' . time() . '.pdf';

            // Sauvegarder temporairement le PDF
            Storage::disk('public')->put($filename, $pdf->output());

            // Retourner l'URL temporaire
            return Storage::disk('public')->url($filename);

        } catch (\Exception $e) {
            \Log::error('Erreur génération PDF:', [
                'message' => $e->getMessage(),
                'prescription_id' => $prescriptionId,
                'trace' => $e->getTraceAsString()
            ]);

            $this->alert('error', "Erreur lors de la génération du PDF : {$e->getMessage()}");
            return null;
        }
    }
}
