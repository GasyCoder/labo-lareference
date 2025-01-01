<?php

namespace App\Livewire\Secretaire;

use App\Models\User;
use App\Models\Analyse;
use App\Models\Patient;
use Livewire\Component;
use App\Models\Prelevement;
use Illuminate\Support\Str;
use App\Models\Prescription;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\AnalysePrescription;
use Illuminate\Support\Facades\Cache;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class EditPrescription extends Component
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

    // IDs
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
    public $nouveau_prescripteur_nom = null;
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

    // Collections
    protected Collection $analysesCollection;
    protected Collection $prelevementsCollection;

    // Méthode de montage initiale
    public function mount($id)
    {
        $this->analysesCollection = new Collection();
        $this->prelevementsCollection = new Collection();

        $prescription = Prescription::with(['patient', 'prescripteur', 'analyses', 'prelevements'])->find($id);
        if (!$prescription) {
            session()->flash('error', 'Prescription introuvable.');
            return redirect()->route('secretaire.patients.index');
        }

        $this->prescriptionId = $prescription->id;
        $this->loadPrescriptionData($prescription);
        $this->loadAnalyses();
        $this->loadPrelevements($prescription);

        foreach ($prescription->prelevements as $prelevement) {
            $this->prelevementQuantities[$prelevement->id] = $prelevement->pivot->quantite ?? 1;
        }
    }

    // Chargement des données
    public function loadPrescriptionData($prescription)
    {
        if ($prescription->patient) {
            $this->nom = $prescription->patient->nom;
            $this->prenom = $prescription->patient->prenom;
            $this->sexe = $prescription->patient->sexe;
            $this->telephone = $prescription->patient->telephone;
            $this->email = $prescription->patient->email;
        }

        $this->patient_type = $prescription->patient_type;
        $this->age = $prescription->age;
        $this->unite_age = $prescription->unite_age;
        $this->poids = $prescription->poids;
        $this->renseignement_clinique = $prescription->renseignement_clinique;
        $this->remise = $prescription->remise;

        $this->prescripteur_id = $prescription->prescripteur_id;
        $this->prescripteur_search = $prescription->prescripteur ? $prescription->prescripteur->name : $prescription->nouveau_prescripteur_nom;
        $this->nouveau_prescripteur_nom = $prescription->nouveau_prescripteur_nom;

        $this->selectedAnalyses = $prescription->analyses->pluck('id')->toArray();
        $this->calculateTotal();
    }

    public function loadAnalyses()
    {
        $this->analyses = cache()->remember('analyses', 24*60*60, function() {
            return Analyse::select('id', 'code', 'level', 'parent_code', 'abr', 'designation', 'prix')
                ->where('status', 1)
                ->orderByRaw("CASE WHEN level = 'PARENT' THEN 0 ELSE 1 END")
                ->orderBy('ordre')
                ->get()
                ->toArray();
        });
    }

    public function loadPrelevements($prescription)
    {
        $this->prelevements = Cache::remember('prelevements_list_v2', now()->addDays(7), function() {
            return Prelevement::actif()
                ->select(['id', 'nom', 'description', 'prix'])
                ->orderBy('nom')
                ->get()
                ->toArray();
        });

        $this->selectedPrelevements = $prescription->prelevements->pluck('id')->toArray();
        $this->prelevementsCollection = collect($this->prelevements);
        $this->calculateTotal();
    }

    // Gestion des analyses
    public function updatedAnalyseSearch()
    {
        if (strlen($this->analyseSearch) < 2) {
            $this->analyseSuggestions = [];
            return;
        }

        $searchTerm = Str::lower($this->analyseSearch);

        $this->analyseSuggestions = collect($this->analyses)
            ->filter(function ($analyse) use ($searchTerm) {
                $matches = Str::contains(Str::lower($analyse['abr']), $searchTerm) ||
                          Str::contains(Str::lower($analyse['designation']), $searchTerm);

                if (!$matches) return false;

                if (in_array($analyse['abr'], ['HB', 'HSTASE'])) {
                    return $analyse['level'] === 'PARENT';
                }

                if ($analyse['level'] === 'NORMAL') {
                    $isHbOrHemostaseChild = collect($this->analyses)
                        ->where('code', $analyse['parent_code'])
                        ->where('level', 'PARENT')
                        ->contains(function ($parent) {
                            return in_array($parent['abr'], ['HB', 'HSTASE']);
                        });

                    if ($isHbOrHemostaseChild) return false;
                    return empty($analyse['parent_code']);
                }

                return $analyse['level'] === 'PARENT';
            })
            ->take(5)
            ->values()
            ->toArray();
    }

    public function addAnalyse($analyseId)
    {
        $selectedAnalyse = collect($this->analyses)->firstWhere('id', $analyseId);
        if (!$selectedAnalyse) return;

        if (in_array($selectedAnalyse['abr'], ['HB', 'HSTASE'])) {
            if (!in_array($analyseId, $this->selectedAnalyses)) {
                $this->selectedAnalyses[] = $analyseId;

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
        if (!$analyse) return;

        if (in_array($analyse['abr'], ['HB', 'HSTASE'])) {
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
            $this->selectedAnalyses = array_values(array_diff($this->selectedAnalyses, [$analyseId]));
        }

        $this->calculateTotal();
        $this->dispatch('analyse-removed', analyseId: $analyseId);
    }

    // Gestion des prélèvements
    public function togglePrelevement($prelevementId)
    {
        $index = array_search($prelevementId, $this->selectedPrelevements);

        if ($index !== false) {
            unset($this->selectedPrelevements[$index]);
            unset($this->prelevementQuantities[$prelevementId]);
        } else {
            $this->selectedPrelevements[] = $prelevementId;

            $prelevement = collect($this->prelevements)->firstWhere('id', $prelevementId);
            if ($this->hasQuantity($prelevement)) {
                $this->prelevementQuantities[$prelevementId] = 1;
            }
        }

        $this->selectedPrelevements = array_values($this->selectedPrelevements);
        $this->calculateTotal();
        $this->dispatch('prelevement-updated');
    }

    public function updatedPrelevementQuantities($quantity, $prelevementId)
    {
        $quantity = intval($quantity);

        if ($quantity < 1) {
            $this->prelevementQuantities[$prelevementId] = 1;
        } else {
            $this->prelevementQuantities[$prelevementId] = $quantity;
        }

        $this->calculateTotal();
    }

    public function getPrelevementPrice($prelevementId)
    {
        $prelevement = collect($this->prelevements)->firstWhere('id', $prelevementId);
        if (!$prelevement) return 0;

        $isTubeAiguille = in_array(strtolower($prelevement['nom']), [
            'tube aiguille', 'tube/aiguille', 'tube sanguin', 'tube', 'aiguille'
        ]);

        if ($isTubeAiguille) {
            $quantity = isset($this->prelevementQuantities[$prelevementId]) ?
                intval($this->prelevementQuantities[$prelevementId]) : 1;
            return $quantity >= 2 ? 3500 : 2000;
        }

        return $prelevement['prix'] ?? 0;
    }

    // Calculs et totaux
    public function calculateTotal()
    {
        $this->totalPrelevementsPrice = collect($this->selectedPrelevements)
            ->reduce(function ($total, $prelevementId) {
                return $total + $this->getPrelevementPrice($prelevementId);
            }, 0);

        $analysesTotal = collect($this->selectedAnalyses)
            ->reduce(function ($total, $analyseId) {
                $analyse = collect($this->analyses)->firstWhere('id', $analyseId);
                return $total + ($analyse['prix'] ?? 0);
            }, 0);

        $this->totalPrice = $analysesTotal + $this->totalPrelevementsPrice;

        if ($this->patient_type === 'URGENCE-NUIT') {
            $this->totalPrice += 20000;
        } elseif ($this->patient_type === 'URGENCE-JOUR') {
            $this->totalPrice += 15000;
        }

        if ($this->remise > 0) {
            $this->totalPrice = $this->totalPrice * (1 - ($this->remise / 100));
        }
    }

    // Navigation entre les étapes
    public function nextStep()
    {
        $currentRules = $this->getValidationRules()[$this->step] ?? [];
        $this->validate($currentRules);

        if ($this->step < 3) {
            $this->step++;
            $this->dispatch('stepUpdated', step: $this->step);
        } elseif ($this->step == 3) {
            $this->updatePrescription();
        }
    }

    public function previousStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    // Mise à jour de la prescription
    public function updatePrescription()
    {
        foreach ($this->getValidationRules() as $step => $rules) {
            $this->validate($rules);
        }

        try {
            DB::transaction(function () {
                $prescription = Prescription::findOrFail($this->prescriptionId);
                $prescription->patient->update($this->getPatientData());
                $prescription->update($this->getPrescriptionData());
                $this->updateAnalyses($prescription);
                $this->updatePrelevements($prescription);
                $prescription->updateStatus();
            });

            $this->alert('success', 'Prescription mise à jour avec succès.');
            return redirect()->route('secretaire.patients.index');
        } catch (\Exception $e) {
            $this->alert('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
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
            'nouveau_prescripteur_nom' => $this->prescripteur_id ? null : $this->nouveau_prescripteur_nom,
            'montant_total' => $this->totalPrice
        ];
    }

    protected function updateAnalyses($prescription)
    {
        $currentAnalyses = $prescription->analyses->pluck('id')->toArray();
        $analysesToAdd = array_diff($this->selectedAnalyses, $currentAnalyses);
        $analysesToRemove = array_diff($currentAnalyses, $this->selectedAnalyses);

        $prescription->analyses()->detach($analysesToRemove);

        foreach ($this->selectedAnalyses as $analyseId) {
            $analyse = collect($this->analyses)->firstWhere('id', $analyseId);
            $prescription->analyses()->syncWithoutDetaching([
                $analyseId => [
                    'prix' => $analyse['prix'],
                    'status' => in_array($analyseId, $analysesToAdd) ? AnalysePrescription::STATUS_EN_ATTENTE : DB::raw('status'),
                    'updated_at' => now()
                ]
            ]);
        }

        Cache::forget('analyses_list_v2');
    }

    protected function updatePrelevements($prescription)
    {
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

    // Gestion des prescripteurs
    public function updatedPrescripteurSearch()
    {
        if (strlen($this->prescripteur_search) < 2) {
            $this->suggestions = [];
            $this->showCreateOption = false;
            return;
        }

        $this->suggestions = Cache::remember(
            'prescripteur_search_' . $this->prescripteur_search,
            now()->addMinutes(30),
            function () {
                return User::role('prescripteur')
                    ->select(['id', 'name'])
                    ->where('name', 'like', '%' . $this->prescripteur_search . '%')
                    ->take(5)
                    ->get()
                    ->toArray();
            }
        );

        $this->showCreateOption = empty($this->suggestions);
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

    // Règles de validation
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
                'prescripteur_search' => 'required|string|max:255',
                'prescripteur_id' => 'required_without:nouveau_prescripteur_nom|nullable|integer',
                'nouveau_prescripteur_nom' => 'required_without:prescripteur_id|nullable|string|max:255',
            ],
            3 => [
                'selectedAnalyses' => 'required|array|min:1',
                'selectedAnalyses.*' => 'required|integer|exists:analyses,id',
                'prelevementQuantities.*' => 'nullable|integer|min:1'
            ]
        ];
    }

    // Méthodes utilitaires
    public function hasQuantity($prelevement)
    {
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

    // Propriétés calculées
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

    // Rendu
    public function render()
    {
        return view('livewire.secretaire.edit-prescription', [
            'analyses' => $this->analyses,
            'analysesPrices' => $this->analysesPrices,
            'prelevements' => $this->prelevements,
            'totalPrelevementsPrice' => $this->totalPrelevementsPrice,
            'progressPercentage' => $this->getProgressPercentageProperty()
        ]);
    }
}
