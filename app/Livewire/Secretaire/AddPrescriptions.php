<?php

namespace App\Livewire\Secretaire;

use App\Models\User;
use App\Models\Analyse;
use App\Models\Patient;
use Livewire\Component;
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

    public function mount()
    {
        $this->loadAnalyses();
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
            });

            $this->alert('success', 'Patient, prescription et analyses enregistrés avec succès.');
            $this->reset();
            return redirect()->route('secretaire.patients.index');
        } catch (\Exception $e) {
            $this->alert('error', 'Une erreur est survenue lors de l\'enregistrement: ' . $e->getMessage());
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

    public function calculateTotal()
    {
        $this->totalPrice = collect($this->analyses)
            ->whereIn('id', $this->selectedAnalyses)
            ->sum('prix');
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

}
