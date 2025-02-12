<?php

namespace App\Livewire\Secretaire;

use App\Models\User;
use App\Models\Analyse;
use App\Models\Patient;
use Livewire\Component;
use App\Models\Prelevement;
use Illuminate\Support\Str;
use App\Models\Prescripteur;
use App\Models\Prescription;
use Livewire\Attributes\Rule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\AnalysePrescription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class AddPrescriptions extends Component
{
    use LivewireAlert;

    // Propriétés de base
    protected $queryString = ['step'];
    protected $listeners = [
        'refreshAnalyses' => 'loadAnalyses',
        'refreshPrelevements' => 'loadPrelevements',
        'calculateTotal' => 'calculateTotal'
    ];

  // États et étapes
  public $showPrelevements = false;
  public $step = 1;
  public $totalSteps = 3;
  public $isEditing = false;

  // IDs
  public $patientId;
  public $prescriptionId;

  // Champs Patient
  public $nom = '';
  public $prenom = '';
  public $sexe = '';
  public $telephone = '';
  public $email = '';

  // Champs Prescription
  public $patient_type = 'EXTERNE';
  public $age = 0;
  public $unite_age = 'Ans';
  public $poids = null;
  public $renseignement_clinique = '';
  public $remise = 0;

  // Collections et recherches
  public $analyses = [];
  public $prescripteur_search = '';
  public $prescripteur_id = null;
  public $suggestions = [];
  public $showCreateOption = false;
  public $selectedAnalyses = [];
  public $analyseSearch = '';
  public $analyseSuggestions = [];

  // Prix et calculs
  public $totalPrice = 0;
  protected $analysesPrices;
  public $prelevements = [];
  public $selectedPrelevements = [];
  public $totalPrelevementsPrice = 0;
  public $prelevementQuantities = [];
  protected $basePrelevementPrice = 2000;
  protected $elevatedPrelevementPrice = 3500;

    protected Collection $analysesCollection;
    protected Collection $prelevementsCollection;

    public function mount()
    {
        $this->analysesCollection = new Collection();
        $this->prelevementsCollection = new Collection();
        $this->loadInitialData();
    }

    protected function loadInitialData()
    {
        $this->loadAnalyses();
        $this->loadPrelevements();

        if ($this->isEditing && $this->patientId) {
            $this->loadExistingData();
        }
    }

    protected function loadExistingData()
    {
    $patient = Patient::findOrFail($this->patientId);
    $prescription = Prescription::where('patient_id', $this->patientId)
        ->with(['analyses', 'prelevements', 'prescripteur'])
        ->latest()
        ->firstOrFail();

    // Patient data
    $this->nom = $patient->nom;
    $this->prenom = $patient->prenom;
    $this->sexe = $patient->sexe;
    $this->telephone = $patient->telephone;
    $this->email = $patient->email;

    // Prescription data
    $this->patient_type = $prescription->patient_type;
    $this->age = $prescription->age;
    $this->unite_age = $prescription->unite_age;
    $this->poids = $prescription->poids;
    $this->renseignement_clinique = $prescription->renseignement_clinique;
    $this->remise = $prescription->remise;
    $this->prescripteur_id = $prescription->prescripteur_id;
    if ($prescription->prescripteur) {
        $this->prescripteur_search = $prescription->prescripteur->nom;
    }

    // Analyses and prelevements
    $this->selectedAnalyses = $prescription->analyses->pluck('id')->toArray();
    $this->selectedPrelevements = $prescription->prelevements->pluck('id')->toArray();
    $this->prelevementQuantities = $prescription->prelevements
        ->pluck('pivot.quantite', 'id')
        ->toArray();

    $this->calculateTotal();
    }

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

    public function loadPrelevements()
    {
        $cacheKey = 'prelevements_list_v2';

        $this->prelevements = Cache::remember($cacheKey, now()->addDays(7), function() {
            return Prelevement::actif()
                ->select(['id', 'nom', 'description', 'prix'])
                ->orderBy('nom')
                ->get()
                ->toArray();
        });

        $this->prelevementsCollection = collect($this->prelevements);
    }


    public function updatedPrescripteurSearch()
    {
        if (strlen($this->prescripteur_search) < 2) {
            $this->suggestions = [];
            $this->showCreateOption = false;
            return;
        }

        $this->suggestions = Cache::remember(
            'prescripteur_search_' . $this->prescripteur_search,
            now()->addMinutes(10),
            function () {
                return Prescripteur::where('nom', 'like', '%' . $this->prescripteur_search . '%')
                    ->where('is_active', true)
                    ->take(5)
                    ->get()
                    ->toArray();
            }
        );

        $this->showCreateOption = empty($this->suggestions);
    }


    public function updatedNom($value)
    {
        if (strlen($value) < 4) {
            $this->suggestions = [];
            return;
        }

        $this->suggestions = Cache::remember(
            'patient_search_' . $value,
            now()->addMinutes(30),
            function () use ($value) {
                return Patient::where('nom', 'like', $value . '%')
                    ->select('nom', 'prenom')
                    ->limit(5)
                    ->get()
                    ->toArray();
            }
        );
    }


    public function updatedAnalyseSearch()
    {
        if (strlen($this->analyseSearch) < 2) {
            $this->analyseSuggestions = [];
            return;
        }

        $searchTerm = Str::lower($this->analyseSearch);

        $this->analyseSuggestions = collect($this->analyses)
            ->filter(function ($analyse) use ($searchTerm) {
                // Vérification si l'analyse correspond aux termes de recherche
                $matches = Str::contains(Str::lower($analyse['abr']), $searchTerm) ||
                          Str::contains(Str::lower($analyse['designation']), $searchTerm);

                if (!$matches) {
                    return false;
                }

                // Pour HB et HEMOSTASE, afficher uniquement les parents
                if (in_array($analyse['abr'], ['HB', 'HSTASE'])) {
                    return $analyse['level'] === 'PARENT';
                }

                // Pour les analyses de niveau NORMAL
                if ($analyse['level'] === 'NORMAL') {
                    // Si c'est un enfant de HB ou HEMOSTASE, ne pas l'afficher
                    $isHbOrHemostaseChild = collect($this->analyses)
                        ->where('code', $analyse['parent_code'])
                        ->where('level', 'PARENT')
                        ->contains(function ($parent) {
                            return in_array($parent['abr'], ['HB', 'HSTASE']);
                        });

                    if ($isHbOrHemostaseChild) {
                        return false;
                    }

                    // Si c'est une analyse NORMAL sans parent, l'afficher
                    return empty($analyse['parent_code']);
                }

                // Afficher les PARENT
                return $analyse['level'] === 'PARENT';
            })
            ->take(5)
            ->values()
            ->toArray();
    }


    public function selectPrescripteur($id, $nom)
    {
        $this->prescripteur_id = $id;
        $this->prescripteur_search = $nom;
        $this->suggestions = [];
        $this->showCreateOption = false;
    }


    public function setNewPrescripteur()
    {
    if (!empty($this->prescripteur_search)) {
        $prescripteur = Prescripteur::create([
            'nom' => $this->prescripteur_search,
            'is_active' => true
        ]);

        $this->prescripteur_id = $prescripteur->id;
        $this->prescripteur_search = $prescripteur->nom;
    }


    $this->suggestions = [];
    $this->showCreateOption = false;
    }

    public function selectSuggestion($nom, $prenom)
    {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->suggestions = [];
    }

    public function addAnalyse($analyseId)
    {
        $selectedAnalyse = collect($this->analyses)->firstWhere('id', $analyseId);

        if (!$selectedAnalyse) return;

        // Cas spécial pour HB et HSTASE
        if (in_array($selectedAnalyse['abr'], ['HB', 'HSTASE'])) {
            if (!in_array($analyseId, $this->selectedAnalyses)) {
                // Ajouter le parent
                $this->selectedAnalyses[] = $analyseId;

                // Ajouter automatiquement tous les enfants
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
            // Pour toutes les autres analyses, ajouter uniquement l'analyse sélectionnée
            if (!in_array($analyseId, $this->selectedAnalyses)) {
                $this->selectedAnalyses[] = $analyseId;
            }
        }

        $this->selectedAnalyses = array_unique($this->selectedAnalyses);
        $this->calculateTotal();
        $this->analyseSuggestions = [];
        $this->analyseSearch = '';
    }

    protected function resetSearch()
    {
        $this->analyseSuggestions = [];
        $this->analyseSearch = '';
        $this->dispatch('search-reset');
    }


    public function removeAnalyse($analyseId)
    {
        $analyse = collect($this->analyses)->firstWhere('id', $analyseId);
        if (!$analyse) return;

        if (in_array($analyse['abr'], ['HB', 'HSTASE'])) {
            // Pour HEPATITE B et HEMOSTASE, supprimer le parent et tous les enfants
            $childIds = collect($this->analyses)
                ->filter(function($a) use ($analyse) {
                    return $a['parent_code'] === $analyse['code'];
                })
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
        $this->dispatch('analyse-removed', analyseId: $analyseId);
    }

    public function togglePrelevement($prelevementId)
    {
        $index = array_search($prelevementId, $this->selectedPrelevements);

        if ($index !== false) {
            // Décocher le prélèvement
            unset($this->selectedPrelevements[$index]);
            unset($this->prelevementQuantities[$prelevementId]);
        } else {
            // Cocher le prélèvement
            $this->selectedPrelevements[] = $prelevementId;

            // Définir une quantité par défaut si nécessaire
            $prelevement = collect($this->prelevements)->firstWhere('id', $prelevementId);
            if ($this->hasQuantity($prelevement)) {
                $this->prelevementQuantities[$prelevementId] = 1;
            }
        }

        $this->selectedPrelevements = array_values($this->selectedPrelevements);

        // Recalculer le total
        $this->calculateTotal();
        $this->dispatch('prelevement-updated');
    }


    // Méthode pour obtenir le prix d'un prélèvement
    public function getPrelevementPrice($prelevementId)
    {
        $prelevement = collect($this->prelevements)->firstWhere('id', $prelevementId);
        if (!$prelevement) return 0;

        // Vérifier si c'est un tube aiguille
        $isTubeAiguille = in_array(strtolower($prelevement['nom']), [
            'tube aiguille', 'tube/aiguille', 'tube sanguin', 'tube','aiguille'
        ]);

        if ($isTubeAiguille) {
            // Récupérer la quantité (par défaut 1)
            $quantity = isset($this->prelevementQuantities[$prelevementId]) ?
                intval($this->prelevementQuantities[$prelevementId]) : 1;

            // Appliquer le prix selon la quantité
            return $quantity >= 2 ? 3500 : 2000;
        }

        return $prelevement['prix'] ?? 0;
    }


    public function updatedPrelevementQuantities($quantity, $prelevementId)
    {
        // Forcer la conversion en entier
        $quantity = intval($quantity);

        // Assurer une quantité minimale de 1
        if ($quantity < 1) {
            $this->prelevementQuantities[$prelevementId] = 1;
        } else {
            $this->prelevementQuantities[$prelevementId] = $quantity;
        }

        // Forcer le recalcul immédiat du total
        $this->calculateTotal();
    }


    public function updatedSelectedPrelevements($value)
    {
        $this->calculateTotal();
    }



    public function calculateTotal()
    {
        // 1. Calcul du total des prélèvements
        $this->totalPrelevementsPrice = collect($this->selectedPrelevements)
            ->reduce(function ($total, $prelevementId) {
                return $total + $this->getPrelevementPrice($prelevementId);
            }, 0);

        // 2. Calcul du total des analyses
        $analysesTotal = collect($this->selectedAnalyses)
            ->reduce(function ($total, $analyseId) {
                $analyse = collect($this->analyses)->firstWhere('id', $analyseId);
                return $total + ($analyse['prix'] ?? 0);
            }, 0);

        // 3. Total des analyses et prélèvements
        $this->totalPrice = $analysesTotal + $this->totalPrelevementsPrice;

        // 4. Ajout des frais d'urgence selon le type de patient
        if ($this->patient_type === 'URGENCE-NUIT') {
            $this->totalPrice += 20000; // Frais supplémentaires pour urgence de nuit
        } elseif ($this->patient_type === 'URGENCE-JOUR') {
            $this->totalPrice += 15000; // Frais supplémentaires pour urgence de jour
        }

        // 5. Appliquer la remise en dernier (après les frais d'urgence)
        if ($this->remise > 0) {
            $this->totalPrice = $this->totalPrice * (1 - ($this->remise / 100));
        }
    }


    public function nextStep()
    {
        if ($this->isValidAction()) {
            $currentRules = $this->getValidationRules()[$this->step] ?? [];
            $this->validate($currentRules);

            if ($this->step === 1) {
                if (!empty($this->prescripteur_search) && empty($this->prescripteur_id)) {
                    $prescripteur = Prescripteur::create([
                        'nom' => $this->prescripteur_search,
                        'is_active' => true
                    ]);
                    $this->prescripteur_id = $prescripteur->id;
                }
                $this->loadAnalyses();
            } elseif ($this->step === 2) {
                $this->loadPrelevements();
            }

            if ($this->step < $this->totalSteps) {
                $this->step++;
                $this->dispatch('stepUpdated', step: $this->step);
            } elseif ($this->step == $this->totalSteps) {
                $this->savePatientAndPrescription();
            }
        }
    }



    public function previousStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function savePatientAndPrescription()
    {
        // Valider chaque étape séparément au lieu d'aplatir toutes les règles
        foreach ($this->getValidationRules() as $step => $rules) {
            $this->validate($rules);
        }

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


    protected function createPatient()
    {
        $patientData = $this->getPatientData();
        $patient = Patient::create($patientData);
        Cache::forget('patients_list');
        return $patient;
    }


    protected function updatePatient()
    {
        $patient = Patient::findOrFail($this->patientId);
        $patientData = $this->getPatientData();
        $patient->update($patientData);
        Cache::forget('patients_list');
        return $patient;
    }


    protected function getPatientData()
    {
        return [
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'sexe' => $this->sexe,
            'telephone' => $this->telephone,
            'email' => $this->email,
        ];
    }

    protected function savePrescription($patient)
    {
        $prescriptionData = array_merge($this->getPrescriptionData(), [
            'patient_id' => $patient->id,
            'status' => Prescription::STATUS_EN_ATTENTE,
            'secretaire_id' => Auth::id(),
            'montant_total' => $this->totalPrice
        ]);

        $prescription = $this->isEditing
            ? Prescription::findOrFail($this->prescriptionId)
            : new Prescription();

        $prescription->fill($prescriptionData);
        $prescription->save();

        Cache::forget('prescriptions_list');
        return $prescription;
    }

    protected function getPrescriptionData()
    {
        return [
            'patient_type' => $this->patient_type,
            'age' => $this->age,
            'unite_age' => $this->unite_age,
            'poids' => $this->poids,
            'renseignement_clinique' => $this->renseignement_clinique,
            'remise' => $this->remise,
            'prescripteur_id' => $this->prescripteur_id,
        ];
    }

    protected function saveAnalyses($prescription)
    {
        if (empty($this->selectedAnalyses)) {
            return;
        }

        $priceMap = collect($this->analyses)->pluck('prix', 'id')->toArray();

        $analysesToSync = collect($this->selectedAnalyses)->mapWithKeys(function ($analyseId) use ($priceMap) {
            return [$analyseId => [
                'prix' => $priceMap[$analyseId] ?? 0,
                'status' => AnalysePrescription::STATUS_EN_ATTENTE,
                'created_at' => now(),
                'updated_at' => now()
            ]];
        })->toArray();

        $prescription->analyses()->sync($analysesToSync);
        $prescription->updateStatus();

        Cache::forget('analyses_list_v2');
    }

    protected function savePrelevements($prescription)
    {
        if (empty($this->selectedPrelevements)) {
            return;
        }

        $prelevementsToSync = collect($this->selectedPrelevements)->mapWithKeys(function ($prelevementId) {
            return [$prelevementId => [
                'prix_unitaire' => $this->getPrelevementPrice($prelevementId),
                'quantite' => $this->prelevementQuantities[$prelevementId] ?? 1,
                'created_at' => now(),
                'updated_at' => now()
            ]];
        })->toArray();

        $prescription->prelevements()->sync($prelevementsToSync);
        Cache::forget('prelevements_list_v2');
    }

    protected function getValidationRules()
    {
        return [
            1 => [
                'nom' => 'required|string|max:255',
                'prenom' => 'nullable|string|max:255',
                'sexe' => 'required|string|max:255',
                'age' => 'required|integer|min:0',
                'unite_age' => 'required|in:Ans,Mois,Jours',
            ],
            2 => [
                'patient_type' => 'required|in:HOSPITALISE,EXTERNE,URGENCE-NUIT,URGENCE-JOUR',
                'poids' => 'nullable|numeric|min:0',
                'renseignement_clinique' => 'nullable|string',
                'prescripteur_search' => 'nullable|string|max:255|regex:/^[\pL\s\-]+$/u',
                'prescripteur_id' => 'required_without:nouveau_prescripteur_nom|nullable|integer',
            ],
            3 => [
                'selectedAnalyses' => 'required|array|min:1',
                'selectedAnalyses.*' => 'required|integer|exists:analyses,id',
                'prelevementQuantities.*' => 'nullable|integer|min:1'
            ]
        ];
    }


    protected function isValidAction()
    {
        return request()->hasHeader('X-Livewire') && !request()->hasHeader('X-Inertia');
    }

    public function render()
    {
        return view('livewire.secretaire.add-prescription', [
            'analyses' => $this->analyses,
            'analysesPrices' => $this->analysesPrices,
            'prelevements' => $this->prelevements,
            'totalPrelevementsPrice' => $this->totalPrelevementsPrice,
            'progressPercentage' => $this->getProgressPercentageProperty()
        ]);
    }

    public function getProgressPercentageProperty()
    {
        return min(100, ($this->step / $this->totalSteps) * 100);
    }

    public function getSelectedAnalysesCountProperty()
    {
        return count($this->selectedAnalyses);
    }

    public function getSelectedPrelevementsCountProperty()
    {
        return count($this->selectedPrelevements);
    }

    public function hasQuantity($prelevement)
    {
        // Vérifions tous les noms possibles
        return in_array(strtolower($prelevement['nom']), [
            'tube/aiguille',
            'tube aiguille',
            'tube sanguin',
            'tube',
            'aiguille'
        ]);
    }

    public function isPrelevementSelected($prelevementId)
    {
        return in_array($prelevementId, $this->selectedPrelevements);
    }

}
