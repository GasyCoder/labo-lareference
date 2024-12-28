<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Resultat;
use App\Enums\AnalyseLevel;
use Livewire\WithPagination;

class TechnicienDashboard extends Component
{
    use WithPagination;

    public $selectedDate;
    public $search = '';
    public $perPage = 5;

    protected $queryString = ['search' => ['except' => '']];

    public function mount()
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedSelectedDate()
    {
        $this->resetPage();
    }

    public function render()
    {
        $date = Carbon::parse($this->selectedDate);

        // Récupérer les prescriptions paginées
        $prescriptionIds = Resultat::whereDate('created_at', $date)
            ->whereHas('prescription.patient', function($query) {
                if ($this->search) {
                    $query->where(function($q) {
                        $q->where('nom', 'like', '%'.$this->search.'%')
                          ->orWhere('prenom', 'like', '%'.$this->search.'%')
                          ->orWhere('ref', 'like', '%'.$this->search.'%');
                    });
                }
            })
            ->select('prescription_id')
            ->distinct()
            ->paginate($this->perPage)
            ->pluck('prescription_id');

        // Récupérer les résultats
        $resultats = Resultat::with([
            'prescription.patient',
            'analyse.parent'
        ])
        ->whereIn('prescription_id', $prescriptionIds)
        ->whereDate('created_at', $date)
        ->where(function($query) {
            $query->whereNotNull('resultats')
                  ->orWhereNotNull('valeur');
        })
        ->whereHas('analyse', function($query) {
            $query->where('level', '!=', AnalyseLevel::PARENT)
                  ->whereNotNull('parent_code');
        })
        ->orderBy('prescription_id')
        ->orderBy('created_at')
        ->get();

        // Group by patient and datetime
        $groupedResultats = $resultats->groupBy(function($item) {
            return $item->prescription->patient->id . '|' . $item->created_at->format('H:i d/m/Y');
        });

        // Pagination info
        $paginationInfo = Resultat::whereDate('created_at', $date)
            ->whereHas('prescription.patient', function($query) {
                if ($this->search) {
                    $query->where(function($q) {
                        $q->where('nom', 'like', '%'.$this->search.'%')
                          ->orWhere('prenom', 'like', '%'.$this->search.'%')
                          ->orWhere('ref', 'like', '%'.$this->search.'%');
                    });
                }
            })
            ->select('prescription_id')
            ->distinct()
            ->paginate($this->perPage);

        // Compteurs
        $counters = [
            'total' => $resultats->count(),
            'enCours' => Resultat::whereDate('created_at', $date)
                ->where('status', 'EN_COURS')->count(),
            'enAttente' => Resultat::whereDate('created_at', $date)
                ->where('status', 'EN_ATTENTE')->count()
        ];

        return view('livewire.technicien-dashboard', [
            'groupedResultats' => $groupedResultats,
            'paginationInfo' => $paginationInfo,
            'counters' => $counters
        ]);
    }
}
