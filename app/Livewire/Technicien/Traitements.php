<?php

namespace App\Livewire\Technicien;

use Livewire\Component;
use App\Models\Prescription;
use Livewire\WithPagination;
use App\Models\AnalysePrescription;

class Traitements extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $tab = 'actifs';
    public function switchTab($tab)
    {
        $this->tab = $tab;
        $this->resetPage(); // Réinitialiser la pagination pour chaque onglet
    }

    public $search = '';

    protected $queryString = [
        'search',
        'tab' => ['except' => 'actifs'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->tab = request()->query('tab', 'actifs'); // Par défaut : 'actifs'
    }

    public function render()
    {
        $search = '%' . $this->search . '%';

        // Prescriptions actives (non terminées et non archivées)
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
        ->whereIn('status', [Prescription::STATUS_EN_ATTENTE, Prescription::STATUS_EN_COURS])  // Modification ici
        ->where(function ($query) use ($search) {
            $query->where('renseignement_clinique', 'like', $search)
                ->orWhere('status', 'like', $search)
                ->orWhereHas('patient', function ($query) use ($search) {
                    $query->where('nom', 'like', $search)
                        ->orWhere('prenom', 'like', $search)
                        ->orWhere('telephone', 'like', $search);
                })
                ->orWhereHas('prescripteur', function ($query) use ($search) {
                    $query->where('nom', 'like', $search)
                        ->where('is_active', true);
                });
        })
        ->orderBy('created_at', 'desc')
        ->paginate(15);

        // Prescriptions terminées
        $analyseTermines = Prescription::with(['patient', 'prescripteur', 'analyses'])
            ->whereHas('patient', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->where('is_archive', false)  // Ajout de cette condition
            ->where('status', Prescription::STATUS_TERMINE)  // Modification ici
            ->whereDoesntHave('resultats', function($query) {  // Ajout de cette condition
                $query->where('status', 'VALIDE');
            })
            ->where(function ($query) use ($search) {
                $query->where('renseignement_clinique', 'like', $search)
                    ->orWhere('status', 'like', $search)
                    ->orWhereHas('patient', function ($query) use ($search) {
                        $query->where('nom', 'like', $search)
                            ->orWhere('prenom', 'like', $search)
                            ->orWhere('telephone', 'like', $search);
                    })
                    ->orWhereHas('prescripteur', function ($query) use ($search) {
                        $query->where('nom', 'like', $search)
                            ->where('is_active', true);
                    });
            })
            ->orderBy('created_at', 'asc')
            ->paginate(15, ['*'], 'termine_page');

        return view('livewire.technicien.traitements', [
            'activePrescriptions' => $activePrescriptions,
            'analyseTermines' => $analyseTermines,
        ]);
    }


    public function openPrescription($prescriptionId)
    {
        $prescription = Prescription::findOrFail($prescriptionId);

        if ($prescription->status === Prescription::STATUS_EN_ATTENTE) {
            // Démarrer une transaction pour assurer la cohérence des données
            \DB::transaction(function () use ($prescription) {
                // Mettre à jour le statut de la prescription
                $prescription->status = Prescription::STATUS_EN_COURS;
                $prescription->save();

                // Mettre à jour le statut de toutes les analyses dans la table pivot
                $prescription->analyses()->wherePivot('status', AnalysePrescription::STATUS_EN_ATTENTE)
                    ->each(function ($analyse) {
                        $analyse->pivot->status = AnalysePrescription::STATUS_EN_COURS;
                        $analyse->pivot->save();
                    });
            });

            $this->dispatch('prescriptionStatusUpdated');
        }

        return $this->redirect(route('technicien.traitement.show', ['prescription' => $prescription]));
    }

}
