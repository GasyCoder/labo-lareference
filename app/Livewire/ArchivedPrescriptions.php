<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Prescription;
use Livewire\WithPagination;
use App\Services\ResultatPdfService;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class ArchivedPrescriptions extends Component
{
    use WithPagination, LivewireAlert;
    protected $paginationTheme = 'bootstrap';
    public $prescriptionId;
    public $search = '';
    public $count;
    protected $queryString = ['search'];

    protected $pdfService;

    public function boot(ResultatPdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    protected $listeners = [
        'unarchiveConfirmed' => 'unarchivePrescription',
        'refreshArchiveCount' => 'getCount'
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->getCount();
    }

    public function getCount()
    {
        $this->count = Prescription::countArchived();
    }

    public function render()
    {
        $search = '%' . $this->search . '%';

        $archivedPrescriptions = Prescription::with([
            'patient',
            'prescripteur',
            'analyses',
            'resultats'
        ])
        ->where('is_archive', true)
        ->where('status', Prescription::STATUS_ARCHIVE)
        ->where(function ($query) use ($search) {
            $query->whereHas('patient', function($q) use ($search) {
                $q->where('nom', 'like', $search)
                  ->orWhere('prenom', 'like', $search)
                  ->orWhere('telephone', 'like', $search);
            })
            ->orWhereHas('prescripteur', function($q) use ($search) {
                $q->where('name', 'like', $search);
            })
            ->orWhere('nouveau_prescripteur_nom', 'like', $search)
            ->orWhereHas('analyses', function($q) use ($search) {
                $q->where('abr', 'like', $search)
                  ->orWhere('designation', 'like', $search);
            });
        })
        ->orderBy('created_at', 'desc')
        ->paginate(15);

        return view('livewire.archived-prescriptions', [
            'archivedPrescriptions' => $archivedPrescriptions
        ]);
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
            'onConfirmed' => 'unarchiveConfirmed'
        ]);
    }

    public function unarchivePrescription()
    {
        try {
            $prescription = Prescription::findOrFail($this->prescriptionId);
            $prescription->unarchive();
            $this->alert('success', 'Prescription désarchivée avec succès.');
            return redirect()->route('archives');
        } catch (\Exception $e) {
            $this->alert('error', 'Erreur lors du désarchivage : ' . $e->getMessage());
        }
    }


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
