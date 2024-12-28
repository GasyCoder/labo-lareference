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

    public $showPrelevements = false; // pour contrôler l'état de l'accordéon
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
        // Mettre en cache les prélèvements
        $this->prelevements = cache()->remember('prelevements', 60*60, function() {
            return Prelevement::actif()
                ->select('id', 'nom', 'description', 'prix')
                ->orderBy('nom')
                ->get()
                ->toArray();
        });
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
        // Ajout d'index sur la colonne name
        $this->suggestions = User::role('prescripteur')
            ->select(['id', 'name']) // Sélection uniquement des champs nécessaires
            ->where('name', 'like', $this->prescripteur_search . '%') // Recherche par début de chaîne plus rapide
            ->take(5)
            ->get()
            ->toArray();

        $this->showCreateOption = strlen($this->prescripteur_search) >= 2 && empty($this->suggestions);
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
        static $rules = null;

        if ($rules === null) {
            $rules = [
                1 => [
                    'nom' => 'required|string|max:255',
                    'prenom' => 'nullable|string|max:255',
                    'sexe' => 'required|string|max:255',
                    'age' => 'required|integer|min:0',
                    'unite_age' => 'required|in:Ans,Mois,Jours',
                ],
                2 => [
                    'patient_type' => 'required|in:HOSPITALISE,EXTERNE',
                    'poids' => 'nullable|numeric|min:0',
                    'renseignement_clinique' => 'nullable|string',
                    'prescripteur_search' => 'required|string|max:255',
                    'prescripteur_id' => 'required_without:nouveau_prescripteur_nom|nullable|integer',
                    'nouveau_prescripteur_nom' => 'required_without:prescripteur_id|nullable|string|max:255',
                ],
                3 => [
                    'selectedAnalyses' => 'required|array|min:1'
                ]
            ];
        }

        return $rules[$this->step] ?? [];
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

    public function updatedAnalyseSearch()
    {
        if (strlen($this->analyseSearch) < 2) {
            $this->analyseSuggestions = [];
            return;
        }

        $this->analyseSuggestions = collect($this->analyses)
            ->filter(function ($analyse) {
                $searchTerm = Str::lower($this->analyseSearch);
                return Str::contains(Str::lower($analyse['abr']), $searchTerm) ||
                       Str::contains(Str::lower($analyse['designation']), $searchTerm);
            })
            ->take(5)
            ->values()
            ->toArray();
    }

    // Augmenter la durée de mise en cache
    public function loadAnalyses()
    {
        $this->analyses = cache()->remember('analyses', 24*60*60, function() { // 24h au lieu de 1h
            return Analyse::select('id', 'code', 'level', 'parent_code', 'abr', 'designation', 'prix')
                ->where('status', 1)
                ->orderByRaw("CASE WHEN level = 'PARENT' THEN 0 ELSE 1 END")
                ->orderBy('ordre')
                ->get()
                ->toArray();
        });
    }

    public function addAnalyse($analyseId)
    {
        $selectedAnalyse = collect($this->analyses)->firstWhere('id', $analyseId);

        // Vérifie si c'est HEPATITE B ou HEMOSTASE
        if (in_array($selectedAnalyse['abr'], ['HB', 'HSTASE'])) {
            if (!in_array($analyseId, $this->selectedAnalyses)) {
                // Ajouter le parent
                $this->selectedAnalyses[] = $analyseId;

                // Ajouter tous les enfants automatiquement
                $childAnalyses = collect($this->analyses)
                    ->filter(function($analyse) use ($selectedAnalyse) {
                        return $analyse['parent_code'] === $selectedAnalyse['code'] &&
                               $analyse['level'] === 'NORMAL';
                    })
                    ->pluck('id')
                    ->toArray();

                $this->selectedAnalyses = array_merge($this->selectedAnalyses, $childAnalyses);
            }
        } else {
            // Pour les autres analyses, ajouter uniquement l'analyse sélectionnée
            if (!in_array($analyseId, $this->selectedAnalyses)) {
                $this->selectedAnalyses[] = $analyseId;
            }
        }

        $this->selectedAnalyses = array_unique($this->selectedAnalyses);
        $this->calculateTotal();
        $this->analyseSuggestions = [];
        $this->analyseSearch = '';
    }



    public function removeAnalyse($analyseId)
    {
        $analyse = collect($this->analyses)->firstWhere('id', $analyseId);

        // Si c'est HEPATITE B ou HEMOSTASE
        if (in_array($analyse['abr'], ['HB', 'HSTASE'])) {
            // Supprimer le parent et tous ses enfants
            $childIds = collect($this->analyses)
                ->where('parent_code', $analyse['code'])
                ->pluck('id')
                ->toArray();

            $this->selectedAnalyses = array_values(array_diff(
                $this->selectedAnalyses,
                array_merge([$analyseId], $childIds)
            ));
        } else {
            // Pour les autres analyses, supprimer uniquement l'analyse
            $this->selectedAnalyses = array_values(array_diff($this->selectedAnalyses, [$analyseId]));
        }

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
        // Mise en cache des collections
        static $prelevementsCollection = null;
        static $analysesCollection = null;

        if (!$prelevementsCollection) {
            $prelevementsCollection = collect($this->prelevements);
        }

        if (!$analysesCollection) {
            $analysesCollection = collect($this->analyses);
        }

        // Calcul optimisé
        $this->totalPrelevementsPrice = $prelevementsCollection
            ->whereIn('id', $this->selectedPrelevements)
            ->sum(function ($prelevement) {
                return $this->getPrelevementPrice($prelevement['id']);
            });

        $this->totalPrice = $analysesCollection
            ->whereIn('id', $this->selectedAnalyses)
            ->sum('prix') + $this->totalPrelevementsPrice;
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

    public function hasQuantity($prelevement)
    {
        return $prelevement['nom'] === 'Tube aiguille';
    }


}
