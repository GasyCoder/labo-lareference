<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Prescription;
use App\Models\AnalysePrescription;
use Illuminate\Support\Facades\DB;

class PrescriptionService
{
    public function createPrescription(array $patientData, array $prescriptionData, array $analyses, array $prelevements): Prescription
    {
        return DB::transaction(function () use ($patientData, $prescriptionData, $analyses, $prelevements) {
            // Créer ou mettre à jour le patient
            $patient = Patient::create($patientData);

            // Créer la prescription
            $prescription = $patient->prescriptions()->create($prescriptionData);

            // Ajouter les analyses
            $analysesToSync = collect($analyses)->mapWithKeys(function ($prix, $id) {
                return [$id => [
                    'prix' => $prix,
                    'status' => AnalysePrescription::STATUS_EN_ATTENTE
                ]];
            })->toArray();

            $prescription->analyses()->sync($analysesToSync);

            // Ajouter les prélèvements
            if (!empty($prelevements)) {
                $prescription->prelevements()->sync($prelevements);
            }

            // Mettre à jour le statut
            $prescription->updateStatus();

            return $prescription;
        });
    }

    public function calculateTotal(array $analyses, array $prelevements): float
    {
        $analysesTotal = collect($analyses)->sum('prix');
        $prelevementsTotal = collect($prelevements)->sum(function ($prelevement) {
            return $prelevement['prix_unitaire'] * $prelevement['quantite'];
        });

        return $analysesTotal + $prelevementsTotal;
    }

    public function validatePrelevementQuantities(array $prelevements, array $quantities): bool
    {
        foreach ($prelevements as $prelevement) {
            $requestedQuantity = $quantities[$prelevement['id']] ?? 1;
            if ($requestedQuantity > $prelevement['quantite']) {
                throw new \Exception("Quantité insuffisante pour le prélèvement: {$prelevement['nom']}");
            }
        }

        return true;
    }
}
