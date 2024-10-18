<?php

namespace App\Livewire\Admin\Analyses;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use App\Models\AnalyseElement;
use App\Models\AnalysePrincipale;
use App\Models\Examen;
use App\Models\AnalyseType;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AnalysesElement extends Component
{
    use WithPagination, LivewireAlert, AuthorizesRequests;

    protected $paginationTheme = 'bootstrap';

    public $code = '';

    #[Rule('required|in:CHILD,NORMAL')]
    public $level = 'NORMAL';

    #[Rule('nullable|exists:analyse_principales,id')]
    public $analyse_principal_id;

    #[Rule('required|string|max:255')]
    public $designation = '';

    #[Rule('boolean')]
    public $is_bold = false;

    #[Rule('required|exists:examens,id')]
    public $examen_id;

    #[Rule('required|exists:analyse_types,id')]
    public $analyse_type_id;

    #[Rule('nullable|json')]
    public $result_disponible;

    #[Rule('nullable|integer')]
    public $ordre = null;

    #[Rule('boolean')]
    public $status = true;

    public $editingAnalyseElementId = null;

    public $analyseElementIdToDelete;

    protected $listeners = ['deleteConfirmed' => 'deleteAnalyseElement'];

    protected function rules()
    {
        return [
            'code' => [
                'required',
                'string',
                'max:255',
                "unique:analyse_elements,code,{$this->editingAnalyseElementId}",
            ],
            // Ajoutez ici les autres règles de validation
        ];
    }

    public function render()
    {
        return view('livewire.admin.analyses.analyses-element', [
            'analyseElements' => AnalyseElement::latest()->paginate(10),
            'analysePrincipales' => AnalysePrincipale::all(),
            'examens' => Examen::all(),
            'analyseTypes' => AnalyseType::all(),
            'levelOptions' => ['CHILD' => 'CHILD', 'NORMAL' => 'NORMAL'],
        ]);
    }

    public function save()
    {
        $validatedData = $this->validate();

        if ($this->editingAnalyseElementId) {
            $analyseElement = AnalyseElement::findOrFail($this->editingAnalyseElementId);
            $analyseElement->update($validatedData);
            $this->alert('success', 'Élément d\'analyse mis à jour avec succès.');
        } else {
            AnalyseElement::create($validatedData);
            $this->alert('success', 'Nouvel élément d\'analyse ajouté avec succès.');
        }

        $this->reset();
    }

    public function edit($analyseElementId)
    {
        $this->editingAnalyseElementId = $analyseElementId;
        $analyseElement = AnalyseElement::findOrFail($analyseElementId);
        $this->fill($analyseElement->toArray());
    }

    public function confirmDelete($analyseElementId)
    {
        $this->analyseElementIdToDelete = $analyseElementId;

        $this->confirm('Êtes-vous sûr de vouloir supprimer cet élément d\'analyse ?', [
            'toast' => true,
            'position' => 'center',
            'showConfirmButton' => true,
            'confirmButtonText' => 'Oui, supprimer',
            'cancelButtonText' => 'Annuler',
            'onConfirmed' => 'deleteConfirmed',
            'onCancelled' => 'cancelledDelete'
        ]);
    }

    public function deleteAnalyseElement()
    {
        $analyseElement = AnalyseElement::find($this->analyseElementIdToDelete);

        if ($analyseElement) {
            $analyseElement->delete();
            $this->alert('success', 'Élément d\'analyse supprimé avec succès.');
        } else {
            $this->alert('error', 'Élément d\'analyse non trouvé.');
        }

        $this->analyseElementIdToDelete = null;
    }

    public function toggleStatus(AnalyseElement $analyseElement)
    {
        $analyseElement->update(['status' => !$analyseElement->status]);
        $this->alert('success', 'Statut mis à jour avec succès.');
    }
}
