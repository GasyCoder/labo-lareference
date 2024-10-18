<?php

namespace App\Livewire\Secretaire;

use App\Models\User;
use App\Models\Analyse;
use App\Models\Patient;
use Livewire\Component;
use Illuminate\Support\Str;
use App\Models\Prescription;
use Illuminate\Support\Facades\DB;
use App\Models\AnalysePrescription;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class EditPrescription extends Component
{
    use LivewireAlert;

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

    public function mount($id)
    {
        $prescription = Prescription::with(['patient', 'prescripteur', 'analyses'])->find($id);

        if (!$prescription) {
            session()->flash('error', 'Prescription introuvable.');
            return redirect()->route('admin.patients.index');
        }

        $this->prescriptionId = $prescription->id;
        $this->loadPrescriptionData($prescription);
        $this->loadAnalyses();
    }

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

    public function render()
    {
        return view('livewire.secretaire.edit-prescription');
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

    public function updatePatientAndPrescription()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $prescription = Prescription::findOrFail($this->prescriptionId);
                $prescription->patient->update($this->getPatientData());
                $prescription->update($this->getPrescriptionData());
                $this->updateAnalyses($prescription);
                $prescription->updateStatus(); // Mise à jour du statut de la prescription
            });

            $this->alert('success', 'Prescription mise à jour avec succès.');
            return redirect()->route('admin.patients.index');
        } catch (\Exception $e) {
            $this->alert('error', 'Une erreur est survenue lors de la mise à jour: ' . $e->getMessage());
        }
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
        ];
    }

    private function updateAnalyses($prescription)
    {
        $currentAnalyses = $prescription->analyses->pluck('id')->toArray();
        $analysesToAdd = array_diff($this->selectedAnalyses, $currentAnalyses);
        $analysesToRemove = array_diff($currentAnalyses, $this->selectedAnalyses);

        // Supprimer les analyses
        $prescription->analyses()->detach($analysesToRemove);

        // Ajouter ou mettre à jour les analyses
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

    public function loadAnalyses()
    {
        $this->analyses = Analyse::select('id', 'abr', 'designation', 'prix')->get()->toArray();
        $this->analysesPrices = collect($this->analyses)->pluck('prix', 'id')->toArray();
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
}
