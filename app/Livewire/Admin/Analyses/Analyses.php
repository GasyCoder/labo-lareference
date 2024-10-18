<?php

namespace App\Livewire\Admin\Analyses;

use App\Models\Analyse;
use App\Models\Examen;
use App\Models\AnalyseType;
use App\Enums\AnalyseLevel;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Analyses extends Component
{
    use WithPagination, LivewireAlert, AuthorizesRequests;

    protected $paginationTheme = 'bootstrap';

    #[Rule('nullable|string|numeric|min:547')]
    public $code = '';

    #[Rule('required')]
    public $level = AnalyseLevel::PARENT;

    #[Rule('nullable|string')]
    public $parent_code = null;

    #[Rule('nullable|string')]
    public $abr = null;

    #[Rule('required|string|max:255')]
    public $designation = '';

    #[Rule('nullable|string')]
    public $description = '';

    #[Rule('nullable|numeric|min:0')]
    public $prix;

    #[Rule('boolean')]
    public $is_bold = false;

    #[Rule('required|exists:examens,id')]
    public $examen_id;

    #[Rule('required|exists:analyse_types,id')]
    public $analyse_type_id;

    #[Rule('nullable|json')]
    public $result_disponible = null;

    #[Rule('nullable|integer')]
    public $ordre = null;

    #[Rule('boolean')]
    public $status = true;

    public $editingAnalyseId = null;
    public $viewingAnalyseId = null;
    public $viewingAnalyse = null;
    public $analysesHierarchy = [];
    public $analyseIdToDelete;
    public $search = '';

    protected $queryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    protected $listeners = ['deleteConfirmed' => 'deleteAnalyse'];

    public function render()
    {
        return view('livewire.admin.analyses.analyse-principal', [
            'analyses' => Analyse::where('level', 'PARENT')
                ->where(function ($query) {
                    $query->where('code', 'like', '%' . $this->search . '%')
                        ->orWhere('designation', 'like', '%' . $this->search . '%')
                        ->orWhere('abr', 'like', '%' . $this->search . '%');
                })
                ->orderByRaw('CASE WHEN prix > 0 THEN 0 ELSE 1 END')
                ->orderBy('created_at', 'desc')
                ->paginate(20),
            'examens' => Examen::all(),
            'analyseTypes' => AnalyseType::all(),
            'levelOptions' => AnalyseLevel::cases(),
        ]);
    }

    protected function rules()
    {
        return [
            'code' => [
                'required',
                'string',
                'min:547',
                "unique:analyses,code,{$this->editingAnalyseId}",
            ],
        ];
    }

    public function save()
    {
        $validatedData = $this->validate();

        if ($this->editingAnalyseId) {
            $analyse = Analyse::findOrFail($this->editingAnalyseId);
            $analyse->update($validatedData);
            $this->alert('success', 'Analyse mise à jour avec succès.');
        } else {
            Analyse::create($validatedData);
            $this->alert('success', 'Nouvelle analyse ajoutée avec succès.');
        }

        $this->reset();
        return redirect()->route('admin.analyse.list');
    }

    public function edit($analyseId)
    {
        $this->editingAnalyseId = $analyseId;
        $analyse = Analyse::findOrFail($analyseId);
        $this->fill($analyse->toArray());
    }

    public function viewDetails($analyseId)
    {
        $this->viewingAnalyseId = $analyseId;
        $this->viewingAnalyse = Analyse::with([
            'examen',
            'analyseType'
        ])->findOrFail($analyseId);

        $this->loadAnalysesHierarchy($this->viewingAnalyse->code);
    }

    private function loadAnalysesHierarchy($parentCode)
    {
        $this->analysesHierarchy = $this->getAnalysesHierarchy($parentCode);
    }

    private function getAnalysesHierarchy($parentCode)
    {
        $analyses = Analyse::where('parent_code', $parentCode)
            ->orderBy('ordre')
            ->get();

        $hierarchy = [];
        foreach ($analyses as $analyse) {
            $item = [
                'analyse' => $analyse,
                'children' => $this->getAnalysesHierarchy($analyse->code)
            ];
            $hierarchy[] = $item;
        }

        return $hierarchy;
    }

    public function confirmDelete($analyseId)
    {
        $this->analyseIdToDelete = $analyseId;

        $this->confirm('Êtes-vous sûr de vouloir supprimer cette analyse ?', [
            'toast' => true,
            'position' => 'center',
            'showConfirmButton' => true,
            'confirmButtonText' => 'Oui, supprimer',
            'cancelButtonText' => 'Annuler',
            'onConfirmed' => 'deleteConfirmed',
            'onCancelled' => 'cancelledDelete'
        ]);
    }

    public function deleteAnalyse()
    {
        $analyse = Analyse::find($this->analyseIdToDelete);

        if ($analyse) {
            $analyse->delete();
            $this->alert('success', 'Analyse supprimée avec succès.');
        } else {
            $this->alert('error', 'Analyse non trouvée.');
        }

        $this->analyseIdToDelete = null;
    }
}
