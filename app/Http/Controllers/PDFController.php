<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\Analyse;
use App\Models\Resultat;
use Barryvdh\DomPDF\Facade\Pdf;

class PDFController extends Controller
{
    public function generateAnalysePDF(Prescription $prescription, Analyse $analyse)
{
    // Récupérer les résultats groupés par examen
    $groupedResults = Resultat::where('prescription_id', $prescription->id)
        ->with([
            'analyse.examen',
            'analyse.analyseType',
            'analyse.children'
        ])
        ->get()
        ->groupBy(function ($result) {
            return $result->analyse->examen->name ?? 'AUTRES';
        });

    $validatedAt = Resultat::where('prescription_id', $prescription->id)
        ->whereNotNull('validated_at')
        ->latest('validated_at')
        ->value('validated_at');

    $validatedBy = Resultat::where('prescription_id', $prescription->id)
        ->whereNotNull('validated_by')
        ->with('validatedBy')
        ->latest('validated_at')
        ->first()?->validatedBy;

    $conclusion = Resultat::where([
        'prescription_id' => $prescription->id,
        'analyse_id' => $analyse->id
    ])->value('conclusion');

    $pdf = PDF::loadView('pdf.resultats-analyse', [
        'prescription' => $prescription,
        'groupedResults' => $groupedResults,
        'conclusion' => $conclusion,
        'validatedAt' => $validatedAt,
        'validatedBy' => $validatedBy
    ]);

    return $pdf->download("resultats_analyse_{$prescription->id}.pdf");
}

}
