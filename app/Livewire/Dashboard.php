<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $totalValidatedAnalyses;      // Total des analyses validées
    public $totalRevenue;                // Total des revenus des analyses validées
    public $monthlyValidatedStats;       // Statistiques mensuelles des analyses validées
    public $topAnalyses;                 // Top analyses les plus demandées
    public $chartData;                   // Données pour le graphique

    public function mount()
    {
        $this->loadValidatedStats();
        $this->prepareChartData();
    }

    private function loadValidatedStats()
    {
        // Total des analyses validées
        $this->totalValidatedAnalyses = DB::table('resultats')
            ->whereNotNull('validated_by')
            ->count();

        // Total des revenus générés par les analyses validées
        $this->totalRevenue = DB::table('resultats')
            ->whereNotNull('validated_by')
            ->join('analyses', 'resultats.analyse_id', '=', 'analyses.id')
            ->sum('analyses.prix');

        // Statistiques mensuelles avec correction de la colonne created_at
// Statistiques mensuelles avec correction de la colonne created_at
$this->monthlyValidatedStats = DB::table('resultats')
    ->whereNotNull('resultats.validated_by')
    ->join('analyses', 'resultats.analyse_id', '=', 'analyses.id')
    ->select(
        DB::raw('YEAR(resultats.created_at) as year'),
        DB::raw('MONTHNAME(resultats.created_at) as month'),
        DB::raw('MONTH(resultats.created_at) as month_number'),
        DB::raw('SUM(analyses.prix) as total_revenue'),
        DB::raw('COUNT(resultats.analyse_id) as total_analyses')
    )
    ->groupBy('year', 'month', 'month_number') // Ajouter month_number pour l'ordre
    ->orderBy('year', 'asc')
    ->orderBy('month_number', 'asc') // Utiliser month_number pour le tri
    ->get();


        // Top analyses validées les plus fréquentes
        $this->topAnalyses = DB::table('resultats')
            ->whereNotNull('resultats.validated_by')
            ->join('analyses', 'resultats.analyse_id', '=', 'analyses.id')
            ->select('analyses.designation', DB::raw('COUNT(resultats.analyse_id) as count'))
            ->groupBy('analyses.designation')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
    }

    private function prepareChartData()
    {
        $this->chartData = [
            'categories' => $this->monthlyValidatedStats->pluck('month'),
            'revenue' => $this->monthlyValidatedStats->pluck('total_revenue'),
            'analyses' => $this->monthlyValidatedStats->pluck('total_analyses'),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
