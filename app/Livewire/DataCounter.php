<?php
namespace App\Livewire;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Patient;
use Livewire\Component;
use App\Models\Paiement;
use App\Models\AnalysePrescription;
use App\Models\Prescription;

class DataCounter extends Component
{
    public function getAnalysesStats()
    {
        $analysesPrescriptions = AnalysePrescription::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status')
            ->toArray();

        return [
            'en_attente' => $analysesPrescriptions[AnalysePrescription::STATUS_EN_ATTENTE] ?? 0,
            'termine' => $analysesPrescriptions[AnalysePrescription::STATUS_TERMINE] ?? 0,
            'validees' => $analysesPrescriptions[AnalysePrescription::STATUS_VALIDE] ?? 0
        ];
    }

    public function getPatientStats()
    {
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonth();

        // Tous les patients
        $totalPatients = Patient::count();

        // Nouveaux patients (derniers 30 jours avec leur première prescription)
        $newPatients = Patient::whereHas('prescriptions', function($query) use ($lastMonth) {
            $query->where('created_at', '>=', $lastMonth);
        })
        ->whereDoesntHave('prescriptions', function($query) use ($lastMonth) {
            $query->where('created_at', '<', $lastMonth);
        })
        ->count();

        return [
            'total' => $totalPatients,
            'nouveaux' => $newPatients
        ];
    }

    public function getRevenusStats()
    {
        $now = Carbon::now();
        $startWeek = $now->copy()->startOfWeek();
        $endWeek = $now->copy()->endOfWeek();
        $lastWeekStart = $now->copy()->subWeek()->startOfWeek();
        $lastWeekEnd = $now->copy()->subWeek()->endOfWeek();

        // Revenus de cette semaine
        $revenusHebdo = Paiement::whereBetween('created_at', [$startWeek, $endWeek])
            ->sum('montant');

        // Revenus de la semaine dernière
        $revenusPrecedents = Paiement::whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
            ->sum('montant');

        // Calcul de la croissance
        $croissance = 0;
        if ($revenusPrecedents > 0) {
            $croissance = round((($revenusHebdo - $revenusPrecedents) / $revenusPrecedents) * 100, 1);
        }

        return [
            'hebdo' => $revenusHebdo,
            'croissance' => $croissance,
            'tendance' => $croissance >= 0 ? 'up' : 'down'
        ];
    }

    public function getBiologistesCount()
    {
        return User::role('biologiste')->count();
    }

    public function render()
    {
        $analysesStats = $this->getAnalysesStats();
        $patientStats = $this->getPatientStats();
        $revenusStats = $this->getRevenusStats();

        return view('livewire.data-counter', [
            'analysesStats' => $analysesStats,
            'totalPatients' => $patientStats['total'],
            'newPatients' => $patientStats['nouveaux'],
            'prescripteurs' => $this->getBiologistesCount(),
            'revenusHebdo' => $revenusStats['hebdo'],
            'croissanceRevenu' => $revenusStats['croissance'],
            'tendanceRevenu' => $revenusStats['tendance']
        ]);
    }
}
