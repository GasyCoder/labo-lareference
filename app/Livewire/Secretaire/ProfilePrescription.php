<?php

namespace App\Livewire\Secretaire;

use App\Services\ResultatPdfShow;
use Livewire\Component;
use App\Models\Resultat;
use App\Models\Prescription;
use App\Models\Prelevement;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ResultatPdfService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class ProfilePrescription extends Component
{
    use LivewireAlert;

    // Propriétés publiques
    public $prescription;
    public $totalAnalyses = 0;
    public $totalPrelevements = 0;
    public $prelevements = [];
    public $selectedPrelevements = [];
    public $totalPrice = 0;
    public $basePrelevementPrice = 2000;
    public $elevatedPrelevementPrice = 3500;

    // Services injectés
    protected $pdfService;

    // Constantes
    const TUBE_AIGUILLE_NOM = 'Tube aiguille';

    /**
     * Boot avec injection de dépendance
     */
    public function boot(ResultatPdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * Initialisation du composant
     */
    public function mount($id)
    {
        $this->prescription = Prescription::with([
            'patient',
            'prescripteur',
            'analyses.resultats',
            'analyses.examen',
            'resultats',
            'prelevements' => function($query) {
                $query->withPivot(['prix_unitaire', 'quantite']);
            }
        ])->findOrFail($id);

        $this->prelevements = Prelevement::actif()->get()->toArray();
        $this->selectedPrelevements = $this->prescription->prelevements->pluck('id')->toArray();
        $this->calculateTotals();
    }

    /**
     * Calcule le prix d'un prélèvement en fonction de son type et de sa quantité
     */

    private function calculatePrelevementPrice($prelevement)
    {
        $quantite = $prelevement->pivot->quantite ?? 1;

        if ($prelevement->nom === self::TUBE_AIGUILLE_NOM) {
            // Si c'est un tube aiguille et la quantité est > 1, le prix est fixe à 3500
            // Sinon le prix est 2000 (peu importe la quantité)
            return $quantite > 1 ? $this->elevatedPrelevementPrice : $this->basePrelevementPrice;
        } else {
            // Pour les autres prélèvements, calcul normal prix unitaire × quantité
            return $prelevement->pivot->prix_unitaire * $quantite;
        }
    }

    /**
     * Calcule tous les totaux
     */
    private function calculateTotals()
    {
        $this->totalAnalyses = $this->prescription->analyses->sum('pivot.prix');

        $this->totalPrelevements = $this->prescription->prelevements->sum(function ($prelevement) {
            return $this->calculatePrelevementPrice($prelevement);
        });

        $this->totalPrice = $this->totalAnalyses + $this->totalPrelevements;
    }

    /**
     * Getter pour le total des analyses
     */
    public function getTotalAnalysesProperty()
    {
        return $this->prescription->analyses->sum('pivot.prix');
    }

    /**
     * Getter pour le total des prélèvements
     */
    public function getTotalPrelevementsProperty()
    {
        return $this->prescription->prelevements->sum(function ($prelevement) {
            return $this->calculatePrelevementPrice($prelevement);
        });
    }

    /**
     * Getter pour le total général
     */
    public function getTotalPriceProperty()
    {
        return $this->getTotalAnalysesProperty() + $this->getTotalPrelevementsProperty();
    }

    /**
     * Génère le PDF des résultats
     */
    public function generateResultatsPDF()
    {
        try {
            if (!$this->hasValidatedResults()) {
                $this->alert('error', 'Aucun résultat validé disponible.');
                return null;
            }

            $url = $this->pdfService->generatePDF($this->prescription);

            $this->dispatch('openPdfInNewWindow', ['url' => $url]);
            $this->alert('success', 'PDF généré avec succès');

            return $url;

        } catch (\Exception $e) {
            Log::error('Erreur génération PDF:', [
                'message' => $e->getMessage(),
                'prescription_id' => $this->prescription->id,
                'trace' => $e->getTraceAsString()
            ]);

            $this->alert('error', "Erreur lors de la génération du PDF : {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Génère le PDF de la facture
     */
    public function generateFacturePDF()
    {
        try {
            $data = [
                'prescription' => $this->prescription,
                'totalAnalyses' => $this->getTotalAnalysesProperty(),
                'totalPrelevements' => $this->getTotalPrelevementsProperty(),
                'totalGeneral' => $this->getTotalPriceProperty(),
                'totalPrice' => $this->getTotalPriceProperty(), // Ajout de cette ligne
                // Ajout des constantes pour la vue
                'TUBE_AIGUILLE_NOM' => self::TUBE_AIGUILLE_NOM,
                'basePrelevementPrice' => $this->basePrelevementPrice,
                'elevatedPrelevementPrice' => $this->elevatedPrelevementPrice
            ];

            $pdf = PDF::loadView('pdf.invoice.facture', $data);

            return response()->streamDownload(
                function () use ($pdf) {
                    echo $pdf->output();
                },
                "facture_{$this->prescription->id}.pdf"
            );

        } catch (\Exception $e) {
            Log::error('Erreur génération facture:', [
                'message' => $e->getMessage(),
                'prescription_id' => $this->prescription->id
            ]);

            $this->alert('error', "Erreur lors de la génération de la facture : {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Envoie la facture par email
     */
    public function sendFactureEmail()
    {
        try {
            if (!$this->prescription->patient->email) {
                $this->alert('error', "Le patient n'a pas d'adresse email enregistrée.");
                return;
            }

            $data = [
                'prescription' => $this->prescription,
                'totalAnalyses' => $this->getTotalAnalysesProperty(),
                'totalPrelevements' => $this->getTotalPrelevementsProperty(),
                'totalGeneral' => $this->getTotalPriceProperty()
            ];

            $pdf = PDF::loadView('pdfs.facture', $data);
            $filename = "facture_" . str_pad($this->prescription->id, 5, '0', STR_PAD_LEFT) . ".pdf";

            Mail::send('emails.facture', ['prescription' => $this->prescription], function ($message) use ($pdf, $filename) {
                $message->to($this->prescription->patient->email)
                        ->subject('Votre facture')
                        ->attachData($pdf->output(), $filename);
            });

            $this->alert('success', 'La facture a été envoyée par email avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur envoi facture par email:', [
                'message' => $e->getMessage(),
                'prescription_id' => $this->prescription->id
            ]);

            $this->alert('error', "Erreur lors de l'envoi de la facture : {$e->getMessage()}");
        }
    }

    /**
     * Vérifie si la prescription a des résultats validés
     */
    private function hasValidatedResults(): bool
    {
        return $this->prescription->resultats()
            ->where('validated_by', '!=', null)
            ->exists();
    }

    /**
     * Formate un montant en ariary
     */
    public function formatMontant($montant)
    {
        return number_format($montant, 0, ',', ' ') . ' Ar';
    }

    /**
     * Rendu du composant
     */
    public function render()
    {
        return view('livewire.secretaire.profile-prescription', [
            'totalAnalyses' => $this->getTotalAnalysesProperty(),
            'totalPrelevements' => $this->getTotalPrelevementsProperty(),
            'totalGeneral' => $this->getTotalPriceProperty()
        ]);
    }
}
