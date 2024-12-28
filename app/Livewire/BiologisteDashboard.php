<?php

namespace App\Livewire;

use App\Models\Resultat;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class BiologisteDashboard extends Component
{
    use WithPagination;

    public $selectedDate;
    public $search = '';
    public $perPage = 5;

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedDate',
        'perPage'
    ];

    public function mount()
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updatingSelectedDate()
    {
        $this->resetPage();
    }

    public function render()
    {
        $date = Carbon::parse($this->selectedDate)->startOfDay();
        $userId = auth()->id();

        // Récupérer les prescriptions paginées
        $prescriptionIdsPaginator = Resultat::where('validated_by', $userId)
            ->whereDate('validated_at', $date)
            ->where(function($query) {
                if ($this->search) {
                    $query->whereHas('prescription.patient', function($q) {
                        $q->where('nom', 'like', '%' . $this->search . '%')
                          ->orWhere('prenom', 'like', '%' . $this->search . '%')
                          ->orWhere('ref', 'like', '%' . $this->search . '%');
                    });
                }
            })
            ->select('prescription_id')
            ->distinct()
            ->paginate($this->perPage);

        // Extraire les IDs des prescriptions
        $prescriptionIds = $prescriptionIdsPaginator->pluck('prescription_id');

        // Récupérer les résultats basés sur les prescription_ids
        $resultats = Resultat::with(['analyse.parent', 'prescription.patient'])
            ->whereIn('prescription_id', $prescriptionIds)
            ->where('validated_by', $userId)
            ->whereDate('validated_at', $date)
            ->where(function($query) {
                $query->whereNotNull('resultats')
                      ->orWhereNotNull('valeur');
            })
            ->whereHas('analyse', function($query) {
                $query->where('level', '!=', \App\Enums\AnalyseLevel::PARENT)
                      ->whereNotNull('parent_code');
            })
            ->orderBy('prescription_id')
            ->orderBy('validated_at')
            ->get();

        // Groupement par patient et date/heure
        $groupedValidations = $resultats->groupBy(function($item) {
            return $item->prescription->patient->id . '|' . $item->validated_at->format('H:i d/m/Y');
        });

        // Compteurs
        $counters = [
            'validations' => Resultat::where('validated_by', $userId)
                ->whereDate('validated_at', $date)
                ->count(),
            'aValider' => Resultat::where('status', 'TERMINE')
                ->whereNull('validated_by')
                ->count()
        ];

        return view('livewire.biologiste-dashboard', [
            'groupedValidations' => $groupedValidations,
            'paginationInfo' => $prescriptionIdsPaginator,
            'counters' => $counters
        ]);
    }
}
