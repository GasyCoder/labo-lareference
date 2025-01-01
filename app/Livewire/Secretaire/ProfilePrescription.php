<?php

namespace App\Livewire\Secretaire;

use Livewire\Component;
use App\Models\Paiement;
use App\Models\Resultat;
use App\Models\Prelevement;
use App\Models\Prescription;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ResultatPdfShow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\ResultatPdfService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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
    public $totalGeneral = 0;
    public $basePrelevementPrice = 2000;
    public $elevatedPrelevementPrice = 3500;
    public $modePaiement = 'ESPECES';
    public $showRemise = false;
    public $remisePercent = null;
    public $montantAvantRemise = 0;
    public $montantRemise = 0;
    public $montantApresRemise = 0;

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

    public function updatedShowRemise($value)
    {
        if (!$value) {
            $this->remisePercent = null;
            $this->resetRemiseCalculations();
        }
        $this->calculerMontants();
    }

    public function updatedRemisePercent($value)
    {
        $this->calculerMontants();
    }

    private function calculerMontants()
    {
        $this->montantAvantRemise = $this->totalGeneral;

        if ($this->showRemise && $this->remisePercent) {
            $this->montantRemise = $this->montantAvantRemise * ($this->remisePercent / 100);
            $this->montantApresRemise = $this->montantAvantRemise - $this->montantRemise;
        } else {
            $this->resetRemiseCalculations();
        }
    }


    // Méthode pour réinitialiser les calculs de remise
    private function resetRemiseCalculations()
    {
        $this->montantRemise = 0;
        $this->montantApresRemise = $this->montantAvantRemise;
    }


    /**
     * Initialisation du composant
     */
    public function mount($id)
    {
        $this->prescription = Prescription::with([
            'patient',
            'prescripteur',
            'analyses' => function($query) {
                $query->withPivot(['prix', 'is_payer', 'status']);
            },
            'analyses.resultats',
            'analyses.examen',
            'resultats',
            'prelevements' => function($query) {
                $query->withPivot(['prix_unitaire', 'quantite', 'is_payer']);
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
        // Calcul du total des analyses
        $this->totalAnalyses = $this->prescription->analyses->sum('pivot.prix');

        // Calcul du total des prélèvements
        $this->totalPrelevements = $this->prescription->prelevements->sum(function ($prelevement) {
            $quantite = $prelevement->pivot->quantite;
            $isTubeAiguille = $prelevement->nom === self::TUBE_AIGUILLE_NOM;

            return $isTubeAiguille && $quantite > 1
                ? $this->elevatedPrelevementPrice
                : ($isTubeAiguille
                    ? $this->basePrelevementPrice
                    : $prelevement->pivot->prix_unitaire * $quantite);
        });

        // Total avant frais d'urgence
        $this->totalGeneral = $this->totalAnalyses + $this->totalPrelevements;

        // Ajout des frais d'urgence selon le type de patient
        if ($this->prescription->patient_type === 'URGENCE-NUIT') {
            $this->totalGeneral += 20000; // Frais supplémentaires pour urgence de nuit
        } elseif ($this->prescription->patient_type === 'URGENCE-JOUR') {
            $this->totalGeneral += 15000; // Frais supplémentaires pour urgence de jour
        }

        // Si une remise est appliquée
        if ($this->prescription->remise > 0) {
            $this->totalGeneral = $this->totalGeneral * (1 - ($this->prescription->remise / 100));
        }
    }


    // Pour les montants à payer, ajoutez une nouvelle méthode
    private function calculateUnpaidTotals()
    {
        // Pour les analyses non payées
        $unpaidAnalyses = $this->prescription->analyses
            ->where('pivot.is_payer', 'NON_PAYE')
            ->sum('pivot.prix');

        // Pour les prélèvements non payés
        $unpaidPrelevements = $this->prescription->prelevements
            ->where('pivot.is_payer', 'NON_PAYE')
            ->sum(function ($prelevement) {
                $quantite = $prelevement->pivot->quantite;
                $isTubeAiguille = $prelevement->nom === self::TUBE_AIGUILLE_NOM;

                return $isTubeAiguille && $quantite > 1
                    ? $this->elevatedPrelevementPrice
                    : ($isTubeAiguille
                        ? $this->basePrelevementPrice
                        : $prelevement->pivot->prix_unitaire * $quantite);
            });

        $subtotal = $unpaidAnalyses + $unpaidPrelevements;

        // Ajout des frais d'urgence si non payés
        if ($this->prescription->patient_type === 'URGENCE-NUIT') {
            $subtotal += 20000;
        } elseif ($this->prescription->patient_type === 'URGENCE-JOUR') {
            $subtotal += 15000;
        }

        // Appliquer la remise si elle existe
        if ($this->prescription->remise > 0) {
            $subtotal = $subtotal * (1 - ($this->prescription->remise / 100));
        }

        return [
            'unpaidAnalyses' => $unpaidAnalyses,
            'unpaidPrelevements' => $unpaidPrelevements,
            'unpaidTotal' => $subtotal
        ];
    }
    /**
     * Traitement du paiement
     */
    public function processPaiement()
    {
        try {
            DB::beginTransaction();

            // Mettre à jour la remise dans la prescription si nécessaire
            if ($this->showRemise && $this->remisePercent) {
                $this->prescription->update([
                    'remise' => $this->remisePercent
                ]);
            }

            // Créer le paiement
            Paiement::create([
                'prescription_id' => $this->prescription->id,
                'montant' => $this->showRemise ? $this->montantApresRemise : $this->totalGeneral,
                'mode_paiement' => $this->modePaiement,
                'recu_par' => Auth::id()
            ]);

            // Mettre à jour le statut des analyses (via Eloquent)
            $this->prescription->analyses()
                ->wherePivot('is_payer', 'NON_PAYE')
                ->updateExistingPivot(null, ['is_payer' => 'PAYE']);

            // Mettre à jour directement la table analyse_prescriptions
            DB::table('analyse_prescriptions')
                ->where('prescription_id', $this->prescription->id)
                ->where('is_payer', 'NON_PAYE')
                ->update([
                    'is_payer' => 'PAYE',
                    'updated_at' => now()
                ]);

            // Mettre à jour le statut des prélèvements
            foreach ($this->prescription->prelevements as $prelevement) {
                $this->prescription->prelevements()
                    ->updateExistingPivot($prelevement->id, [
                        'is_payer' => 'PAYE'
                    ]);
            }

            DB::commit();

            // Rafraîchir les données
            $this->prescription->refresh();
            $this->calculateTotals();

            // Fermer la modal et afficher le succès
            $this->dispatch('payment-processed');
            $this->alert('success', 'Paiement effectué avec succès');

            // Générer la facture
            $this->generateFacturePDF();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du paiement:', [
                'message' => $e->getMessage(),
                'prescription_id' => $this->prescription->id
            ]);

            $this->alert('error', "Une erreur est survenue lors du paiement");
        }
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

            $this->alert('error', "Erreur lors de la génération du PDF");
            return null;
        }
    }

    /**
     * Génère le PDF de la facture
     */
    public function generateFacturePDF()
    {
        try {
            $allPaid = $this->prescription->analyses
                ->every(function($analyse) {
                    return $analyse->pivot->is_payer === 'PAYE';
                }) &&
                $this->prescription->prelevements
                ->every(function($prelevement) {
                    return $prelevement->pivot->is_payer === 'PAYE';
                });

            $totalPrelivementsEtAnalyses = $this->totalAnalyses + $this->totalPrelevements;

            $data = [
                'prescription' => $this->prescription,
                'totalAnalyses' => $this->totalAnalyses,
                'totalPrelevements' => $this->totalPrelevements,
                'totalPrelivementsEtAnalyses' => $totalPrelivementsEtAnalyses,
                'totalGeneral' => $this->totalGeneral,
                'TUBE_AIGUILLE_NOM' => self::TUBE_AIGUILLE_NOM,
                'basePrelevementPrice' => $this->basePrelevementPrice,
                'elevatedPrelevementPrice' => $this->elevatedPrelevementPrice,
                'modePaiement' => $this->modePaiement,
                'allPaid' => $allPaid
            ];

            $pdf = PDF::loadView('pdf.invoice.facture', $data);
            $pdf->setPaper('B5');

            $filename = 'factures/facture-' . $this->prescription->id . '-' . time() . '.pdf';

            Storage::disk('public')->put($filename, $pdf->output());

            return Storage::disk('public')->url($filename);

        } catch (\Exception $e) {
            Log::error('Erreur génération facture:', [
                'message' => $e->getMessage(),
                'prescription_id' => $this->prescription->id
            ]);

            $this->alert('error', "Erreur lors de la génération de la facture");
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
                'totalAnalyses' => $this->totalAnalyses,
                'totalPrelevements' => $this->totalPrelevements,
                'totalGeneral' => $this->totalGeneral
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

            $this->alert('error', "Erreur lors de l'envoi de la facture");
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
            'totalAnalyses' => $this->totalAnalyses,
            'totalPrelevements' => $this->totalPrelevements,
            'totalGeneral' => $this->totalGeneral
        ]);
    }
}
