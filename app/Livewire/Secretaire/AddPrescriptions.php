<?php

namespace App\Livewire\Secretaire;

use App\Models\User;
use App\Models\Analyse;
use App\Models\Patient;
use Livewire\Component;
use App\Models\Prelevement;
use Illuminate\Support\Str;
use App\Models\Prescription;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\AnalysePrescription;
use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class AddPrescriptions extends Component
{
    use LivewireAlert;

    public $step = 1;
    public $totalSteps = 3;
    public $isEditing = false;
    public $patientId;
    public $prescriptionId;

    // Patient fields
    public $nom = '';
    public $prenom = '';
    public $sexe = '';
    public $telephone = '';
    public $email = '';

    // Prescription fields
    public $patient_type = 'EXTERNE';
    public $age = 0;
    public $unite_age = 'Ans';
    public $poids = null;
    public $renseignement_clinique = '';
    public $remise = 0;

    public $analyses = [];

    public $prescripteur_search = '';
    public $prescripteur_id = null;
    public $nouveau_prescripteur_nom = null;
    public $suggestions = [];
    public $showCreateOption = false;

    public $selectedAnalyses = [];
    public $analyseSearch = '';
    public $analyseSuggestions = [];
    public $totalPrice = 0;
    public $analysesPrices;

    // Propriétés pour les prélèvements
    public $prelevements = [];
    public $selectedPrelevements = [];
    public $totalPrelevementsPrice = 0;

    public $prelevementQuantities = [];
    public $basePrelevementPrice = 2000;
    public $elevatedPrelevementPrice = 3500;

    public function mount()
    {
        $this->loadAnalyses();
        $this->loadPrelevements();
    }

    public function loadPrelevements()
    {
        $this->prelevements = Prelevement::actif()
            ->select('id', 'nom', 'description', 'prix')
            ->orderBy('nom')
            ->get()
            ->toArray();
    }


    public function getProgressPercentageProperty()
    {
        return min(100, ($this->step / $this->totalSteps) * 100);
    }

    public function render()
    {
        return view('livewire.secretaire.add-prescription', [
            'analyses' => $this->analyses,
            'analysesPrices' => $this->analysesPrices,
            'prelevements' => $this->prelevements,  // Ajout des prélèvements
            'totalPrelevementsPrice' => $this->totalPrelevementsPrice,
        ]);
    }


    public function nextStep()
    {
        // Vérifie si l'action provient bien du bouton Suivant/Enregistrer
        if ($this->isValidAction()) {
            $this->validate($this->getValidationRules());

            if ($this->step < 3) {
                $this->step++;
                $this->dispatch('stepUpdated', step: $this->step);
            } elseif ($this->step == 3) {
                $this->savePatientAndPrescription();
            }
        }
    }

    private function isValidAction()
    {
        return true;
    }

    public function previousStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function savePatientAndPrescription()
    {
        $this->validate($this->getValidationRules());

        try {
            DB::transaction(function () {
                $patient = $this->isEditing ? $this->updatePatient() : $this->createPatient();
                $prescription = $this->savePrescription($patient);
                $this->saveAnalyses($prescription);
                $this->savePrelevements($prescription);
            });

            $this->alert('success', 'Patient, prescription et analyses enregistrés avec succès.');
            $this->reset();
            return redirect()->route('secretaire.patients.index');
        } catch (\Exception $e) {
            $this->alert('error', 'Erreur lors de l\'enregistrement: ' . $e->getMessage());
        }
    }

    public function updatedPrescripteurSearch()
    {
        $this->suggestions = User::role('prescripteur')
            ->where('name', 'like', '%' . $this->prescripteur_search . '%')
            ->take(5)
            ->get(['id', 'name'])
            ->toArray();

        $this->showCreateOption = strlen($this->prescripteur_search) >= 2 && empty($this->suggestions);

        if ($this->showCreateOption) {
            $this->nouveau_prescripteur_nom = $this->prescripteur_search;
            $this->prescripteur_id = null;
        } else {
            $this->nouveau_prescripteur_nom = null;
        }
    }

    public function selectPrescripteur($id, $name)
    {
        $this->prescripteur_id = $id;
        $this->prescripteur_search = $name;
        $this->nouveau_prescripteur_nom = null;
        $this->suggestions = [];
        $this->showCreateOption = false;
    }

    public function setNewPrescripteur()
    {
        $this->prescripteur_id = null;
        $this->nouveau_prescripteur_nom = $this->prescripteur_search;
        $this->suggestions = [];
        $this->showCreateOption = false;
    }

    private function createPatient()
    {
        return Patient::create($this->getPatientData());
    }

    private function updatePatient()
    {
        $patient = Patient::findOrFail($this->patientId);
        $patient->update($this->getPatientData());
        return $patient;
    }

    private function savePrescription($patient)
    {
        $prescriptionData = $this->getPrescriptionData();
        $prescriptionData['patient_id'] = $patient->id;
        $prescriptionData['status'] = Prescription::STATUS_EN_ATTENTE;
        $prescriptionData['secretaire_id'] = Auth::id();

        $prescription = $this->isEditing
            ? Prescription::findOrFail($this->prescriptionId)
            : new Prescription();

        $prescription->fill($prescriptionData);
        $prescription->save();

        $this->prescriptionId = $prescription->id;
        return $prescription;
    }



    private function getPatientData()
    {
        return [
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'sexe' => $this->sexe,
            'telephone' => $this->telephone,
            'email' => $this->email,
        ];
    }

    private function getPrescriptionData()
    {
        return [
            'patient_type' => $this->patient_type,
            'age' => $this->age,
            'unite_age' => $this->unite_age,
            'poids' => $this->poids,
            'renseignement_clinique' => $this->renseignement_clinique,
            'remise' => $this->remise,
            'prescripteur_id' => $this->prescripteur_id,
            'nouveau_prescripteur_nom' => $this->prescripteur_id ? null : $this->nouveau_prescripteur_nom,
            'secretaire_id' => Auth::id(),
        ];
    }

    private function getValidationRules()
    {
        $rules = [
            'nom' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'sexe' => 'required|string|max:255',
            'age' => 'required|integer|min:0',
            'unite_age' => 'required|in:Ans,Mois,Jours',
        ];

        if ($this->step >= 2) {
            $rules = array_merge($rules, [
                'patient_type' => 'required|in:HOSPITALISE,EXTERNE',
                'poids' => 'nullable|numeric|min:0',
                'renseignement_clinique' => 'nullable|string',
                'prescripteur_search' => 'required|string|max:255',
                'prescripteur_id' => 'required_without:nouveau_prescripteur_nom|nullable|integer',
                'nouveau_prescripteur_nom' => 'required_without:prescripteur_id|nullable|string|max:255',
            ]);
        }

        if ($this->step == 3) {
            $rules['selectedAnalyses'] = 'required|array|min:1';
        }

        return $rules;
    }


    public function updatedNom($value)
    {
        if (strlen($value) > 2) {
            $this->suggestions = Patient::where('nom', 'like', $value . '%')
                ->select('nom', 'prenom')
                ->limit(5)
                ->get()
                ->toArray();
        } else {
            $this->suggestions = [];
        }

        // Vérifier si le nom saisi correspond exactement à l'une des suggestions
        $exactMatch = collect($this->suggestions)->first(function ($suggestion) use ($value) {
            return strtolower($suggestion['nom']) === strtolower($value);
        });

        if (!$exactMatch) {
            $this->suggestions = [];
        }
    }

    public function selectSuggestion($nom, $prenom)
    {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->suggestions = [];
    }

    //Analyse :

    public function loadAnalyses()
    {
        $this->analyses = Analyse::select('id', 'abr', 'designation', 'prix')->get()->toArray();
    }

    public function updatedAnalyseSearch()
    {
        $this->analyseSuggestions = collect($this->analyses)
            ->filter(function ($analyse) {
                return Str::contains(Str::lower($analyse['abr']), Str::lower($this->analyseSearch)) ||
                       Str::contains(Str::lower($analyse['designation']), Str::lower($this->analyseSearch));
            })
            ->take(5)
            ->toArray();
    }

    public function addAnalyse($analyseId)
    {
        $analyse = collect($this->analyses)->firstWhere('id', $analyseId);
        if ($analyse && !in_array($analyseId, $this->selectedAnalyses)) {
            $this->selectedAnalyses[] = $analyseId;
            $this->calculateTotal();
        }
        $this->analyseSearch = '';
        $this->analyseSuggestions = [];
    }

    public function removeAnalyse($analyseId)
    {
        $this->selectedAnalyses = array_values(array_diff($this->selectedAnalyses, [$analyseId]));
        $this->calculateTotal();
        $this->dispatch('analyseRemoved', analyseId: $analyseId);
    }

    public function togglePrelevement($prelevementId)
    {
        $index = array_search($prelevementId, $this->selectedPrelevements);

        if ($index !== false) {
            unset($this->selectedPrelevements[$index]);
            unset($this->prelevementQuantities[$prelevementId]);
        } else {
            $this->selectedPrelevements[] = $prelevementId;
            $this->prelevementQuantities[$prelevementId] = 1; // Quantité par défaut
        }

        $this->calculateTotal();
        $this->dispatch('prelevementUpdated');
    }


    public function updatedSelectedPrelevements($value)
    {
        $this->calculateTotal();
    }

    // Dans la classe AddPrescriptions
    public function calculateTotal()
    {
        // Calcul du total des prélèvements
        $this->totalPrelevementsPrice = collect($this->prelevements)
            ->whereIn('id', $this->selectedPrelevements)
            ->sum(function ($prelevement) {
                // Si c'est un tube aiguille
                if ($prelevement['nom'] === 'Tube aiguille') {
                    $quantity = $this->prelevementQuantities[$prelevement['id']] ?? 1;
                    // Si la quantité est > 1, on utilise le prix élevé
                    $price = $quantity > 1 ? 3500 : 2000;
                    return $price;
                }
                // Pour les autres prélèvements, prix normal
                return $prelevement['prix'];
            });

        // Calcul du total des analyses
        $analysesTotal = collect($this->analyses)
            ->whereIn('id', $this->selectedAnalyses)
            ->sum('prix');

        // Total final
        $this->totalPrice = $analysesTotal + $this->totalPrelevementsPrice;
    }

    // Ajouter cette méthode pour réagir aux changements de quantité
    public function updatedPrelevementQuantities($value, $key)
    {
        $this->calculateTotal();
        $this->dispatch('prelevementUpdated');
    }


    private function savePrelevements($prescription)
    {
        if (empty($this->selectedPrelevements)) {
            return;
        }

        $prelevementsToSync = collect($this->prelevements)
            ->whereIn('id', $this->selectedPrelevements)
            ->mapWithKeys(function ($prelevement) {
                $prix = $this->getPrelevementPrice($prelevement['id']);
                $quantity = $this->prelevementQuantities[$prelevement['id']] ?? 1;

                return [$prelevement['id'] => [
                    'prix_unitaire' => $prix,
                    'quantite' => $quantity,
                    'created_at' => now(),
                    'updated_at' => now()
                ]];
            })
            ->toArray();

        $prescription->prelevements()->sync($prelevementsToSync);
    }


    public function getSelectedAnalysesCountProperty()
    {
        return count($this->selectedAnalyses);
    }

    private function saveAnalyses($prescription)
    {
        $analysesToSync = collect($this->analyses)
            ->whereIn('id', $this->selectedAnalyses)
            ->pluck('prix', 'id')
            ->map(function ($prix) {
                return [
                    'prix' => $prix,
                    'status' => AnalysePrescription::STATUS_EN_ATTENTE
                ];
            })
            ->toArray();

        $prescription->analyses()->sync($analysesToSync);

        // Mettre à jour le statut de la prescription
        $prescription->updateStatus();
    }


    // Méthode pour récupérer le prix d'un prélèvement spécifique
    public function getPrelevementPrice($prelevementId)
    {
        $prelevement = collect($this->prelevements)->firstWhere('id', $prelevementId);
        if (!$prelevement) return 0;

        // Si c'est un prélèvement tube/aiguille
        if ($prelevement['nom'] === 'Tube/Aiguille') {
            $quantity = $this->prelevementQuantities[$prelevementId] ?? 1;
            return $quantity > 1 ? $this->elevatedPrelevementPrice : $this->basePrelevementPrice;
        }

        return $prelevement['prix'];
    }

    // Méthode pour vérifier si un prélèvement est sélectionné
    public function isPrelevementSelected($prelevementId)
    {
        return in_array($prelevementId, $this->selectedPrelevements);
    }

    // Propriété calculée pour le nombre de prélèvements sélectionnés
    public function getSelectedPrelevementsCountProperty()
    {
        return count($this->selectedPrelevements);
    }


}
