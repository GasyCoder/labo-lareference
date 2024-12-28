<?php

namespace App\Livewire\Secretaire;

use App\Models\User;
use App\Models\Analyse;
use App\Models\Patient;
use Livewire\Component;
use App\Models\Prelevement;
use Illuminate\Support\Str;
use App\Models\Prescription;
use Illuminate\Support\Facades\DB;
use App\Models\AnalysePrescription;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class EditPrescription extends Component
{
   use LivewireAlert;

   public $showPrelevements = false;
   public $step = 1;
   public $totalSteps = 3;
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

   public $prelevements = [];
   public $selectedPrelevements = [];
   public $prelevementQuantities = [];
   public $totalPrelevementsPrice = 0;
   public $basePrelevementPrice = 2000;
   public $elevatedPrelevementPrice = 3500;

   public function mount($id)
   {
       $prescription = Prescription::with(['patient', 'prescripteur', 'analyses', 'prelevements'])->find($id);
       if (!$prescription) {
           session()->flash('error', 'Prescription introuvable.');
           return redirect()->route('secretaire.patients.index');
       }

       $this->prescriptionId = $prescription->id;
       $this->loadPrescriptionData($prescription);
       $this->loadAnalyses();
       $this->loadPrelevements($prescription);

       // Initialize quantities from existing prelevements
       foreach ($prescription->prelevements as $prelevement) {
           $this->prelevementQuantities[$prelevement->id] = $prelevement->pivot->quantite ?? 1;
       }
   }

   public function loadPrelevements($prescription)
   {
       $this->prelevements = Prelevement::actif()
           ->select('id', 'nom', 'description', 'prix')
           ->orderBy('nom')
           ->get()
           ->toArray();

       $this->selectedPrelevements = $prescription->prelevements->pluck('id')->toArray();
       $this->calculateTotalPrelevements();
   }

   public function togglePrelevement($prelevementId)
   {
       $index = array_search($prelevementId, $this->selectedPrelevements);

       if ($index !== false) {
           unset($this->selectedPrelevements[$index]);
           unset($this->prelevementQuantities[$prelevementId]);
       } else {
           $this->selectedPrelevements[] = $prelevementId;
           $this->prelevementQuantities[$prelevementId] = 1;
       }

       $this->selectedPrelevements = array_values($this->selectedPrelevements);
       $this->calculateTotalPrelevements();
       $this->dispatch('prelevementUpdated');
   }

   public function updatedPrelevementQuantities($value, $key)
   {
       $this->calculateTotalPrelevements();
       $this->dispatch('prelevementUpdated');
   }

   public function getPrelevementPrice($prelevementId)
   {
       $prelevement = collect($this->prelevements)->firstWhere('id', $prelevementId);
       if (!$prelevement) return 0;

       if ($prelevement['nom'] === 'Tube aiguille') {
           $quantity = $this->prelevementQuantities[$prelevementId] ?? 1;
           return $quantity > 1 ? $this->elevatedPrelevementPrice : $this->basePrelevementPrice;
       }

       return $prelevement['prix'];
   }

   public function calculateTotalPrelevements()
   {
       $this->totalPrelevementsPrice = collect($this->prelevements)
           ->whereIn('id', $this->selectedPrelevements)
           ->sum(function ($prelevement) {
               if ($prelevement['nom'] === 'Tube aiguille') {
                   $quantity = $this->prelevementQuantities[$prelevement['id']] ?? 1;
                   return $quantity > 1 ? $this->elevatedPrelevementPrice : $this->basePrelevementPrice;
               }
               return $prelevement['prix'];
           });

       $this->calculateTotal();
   }

   public function isPrelevementSelected($id)
   {
       return in_array($id, $this->selectedPrelevements);
   }

   public function hasQuantity($prelevement)
   {
       return $prelevement['nom'] === 'Tube aiguille';
   }

   private function updatePrelevements($prescription)
   {
       $prelevementsToSync = collect($this->selectedPrelevements)->mapWithKeys(function ($prelevementId) {
           $prix = $this->getPrelevementPrice($prelevementId);
           $quantity = $this->prelevementQuantities[$prelevementId] ?? 1;

           return [$prelevementId => [
               'prix_unitaire' => $prix,
               'quantite' => $quantity,
               'created_at' => now(),
               'updated_at' => now()
           ]];
       })->toArray();

       $prescription->prelevements()->sync($prelevementsToSync);
   }

   public function calculateTotal()
   {
       $analysesTotal = collect($this->analyses)
           ->whereIn('id', $this->selectedAnalyses)
           ->sum('prix');

       $this->totalPrice = $analysesTotal + $this->totalPrelevementsPrice;
   }

   // Load prescription data method
   public function loadPrescriptionData($prescription)
   {
       $prescription->load('patient', 'prescripteur', 'analyses');

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

   // Step navigation methods
   public function nextStep()
   {
       $this->validate();

       if ($this->step < 3) {
           $this->step++;
       } elseif ($this->step == 3) {
           $this->updatePatientAndPrescription();
       }
   }

   public function previousStep()
   {
       if ($this->step > 1) {
           $this->step--;
       }
   }

   // Update methods
   public function updatePatientAndPrescription()
   {
       $this->validate();

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
           $this->alert('error', 'Une erreur est survenue lors de la mise à jour: ' . $e->getMessage());
       }
   }

   private function updateAnalyses($prescription)
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
               ]
           ]);
       }
   }

   // Data getters
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
       ];
   }

   // Analyse management methods
   public function loadAnalyses()
   {
       // Mise à jour pour inclure tous les champs nécessaires
       $this->analyses = Analyse::select('id', 'code', 'level', 'parent_code', 'abr', 'designation', 'prix')
           ->where('status', 1)
           ->orderByRaw("CASE WHEN level = 'PARENT' THEN 0 ELSE 1 END")
           ->orderBy('ordre')
           ->get()
           ->toArray();
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
       $selectedAnalyse = collect($this->analyses)->firstWhere('id', $analyseId);

       // Vérifie si c'est HEPATITE B ou HEMOSTASE
       if (in_array($selectedAnalyse['designation'], ['HEPATITE B', 'HEMOSTASE'])) {
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
       if (in_array($analyse['designation'], ['HEPATITE B', 'HEMOSTASE'])) {
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

   // Prescripteur management methods
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

   // Computed properties
   public function getSelectedAnalysesCountProperty()
   {
       return count($this->selectedAnalyses);
   }

   public function getSelectedPrelevementsCountProperty()
   {
       return count($this->selectedPrelevements);
   }

   public function rules()
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

   public function render()
   {
       return view('livewire.secretaire.edit-prescription');
   }
}
