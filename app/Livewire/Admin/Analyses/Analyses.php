<?php

namespace App\Livewire\Admin\Analyses;

use App\Models\Analyse;
use App\Models\Examen;
use App\Models\AnalyseType;
use App\Enums\AnalyseLevel;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use League\Csv\Writer;

class Analyses extends Component
{
    use WithPagination, LivewireAlert, AuthorizesRequests;

    protected $paginationTheme = 'bootstrap';

    // Propriétés du formulaire
    public $code = '';
    public $level = null;
    public $parent_code = null;
    public $abr = null;
    public $designation = '';
    public $description = '';
    public $prix;
    public $is_bold = false;
    public $examen_id;
    public $analyse_type_id;
    public $result_disponible = null;
    public $ordre = null;
    public $status = true;

    // Propriétés de gestion
    public $editingAnalyseId = null;
    public $viewingAnalyseId = null;
    public $viewingAnalyse = null;
    public $analysesHierarchy = [];
    public $analyseIdToDelete;

    // Propriétés de filtrage et tri
    public $search = '';
    public $filterExamen = '';
    public $filterType = '';
    public $filterStatus = '';
    public $filterLevel = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 20;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterExamen' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterLevel' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 20],
    ];

    protected $listeners = [
        'deleteConfirmed' => 'deleteAnalyse',
        'refreshAnalyses' => '$refresh'
    ];

    public function mount()
    {
        $this->level = AnalyseLevel::PARENT->value;
    }

    public function rules()
    {
        $rules = [
            'level' => ['required'],
            'parent_code' => ['nullable', 'string'],
            'abr' => ['nullable', 'string'],
            'designation' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'prix' => ['nullable', 'numeric', 'min:0'],
            'is_bold' => ['boolean'],
            'examen_id' => ['required', 'exists:examens,id'],
            'analyse_type_id' => ['required', 'exists:analyse_types,id'],
            'result_disponible' => ['nullable'],
            'ordre' => ['nullable', 'integer'],
            'status' => ['boolean'],
        ];

        if ($this->editingAnalyseId) {
            $rules['code'] = [
                'nullable',
                'string',
                "unique:analyses,code,{$this->editingAnalyseId}"
            ];
        } else {
            $rules['code'] = [
                'nullable',
                'string',
                'min:547',
                'unique:analyses,code'
            ];
        }

        return $rules;
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function save()
    {
        $validatedData = $this->validate();

        try {
            DB::beginTransaction();

            if ($this->editingAnalyseId) {
                $analyse = Analyse::findOrFail($this->editingAnalyseId);

                if (empty($validatedData['code'])) {
                    unset($validatedData['code']);
                }

                if (isset($validatedData['result_disponible']) && !is_null($validatedData['result_disponible'])) {
                    if (!is_string($validatedData['result_disponible'])) {
                        $validatedData['result_disponible'] = json_encode($validatedData['result_disponible']);
                    }
                }

                $analyse->update($validatedData);
                $this->alert('success', 'Analyse mise à jour avec succès.');
            } else {
                if (empty($validatedData['code'])) {
                    unset($validatedData['code']);
                }
                Analyse::create($validatedData);
                $this->alert('success', 'Nouvelle analyse ajoutée avec succès.');
            }

            DB::commit();
            $this->reset();
            return redirect()->route('admin.analyse.list');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->alert('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    public function edit($analyseId)
    {
        $analyse = Analyse::findOrFail($analyseId);
        $this->editingAnalyseId = $analyseId;
        $this->fill($analyse->toArray());
    }

    public function viewDetails($analyseId)
    {
        $this->viewingAnalyseId = $analyseId;
        $this->viewingAnalyse = Analyse::with(['examen', 'analyseType'])
            ->findOrFail($analyseId);
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

    public function duplicate($analyseId)
    {
        try {
            DB::beginTransaction();

            $original = Analyse::findOrFail($analyseId);
            $newAnalyse = $original->replicate();
            $newAnalyse->code = null;
            $newAnalyse->designation = $original->designation . ' (copie)';
            $newAnalyse->created_at = now();
            $newAnalyse->save();

            DB::commit();
            $this->alert('success', 'Analyse dupliquée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->alert('error', 'Erreur lors de la duplication: ' . $e->getMessage());
        }
    }

    public function confirmDelete($analyseId)
    {
        $this->analyseIdToDelete = $analyseId;
        $this->confirm('Êtes-vous sûr de vouloir supprimer cette analyse ?', [
            'toast' => false,
            'position' => 'center',
            'showConfirmButton' => true,
            'confirmButtonText' => 'Oui, supprimer',
            'cancelButtonText' => 'Annuler',
            'onConfirmed' => 'deleteConfirmed',
        ]);
    }

    public function deleteAnalyse()
    {
        try {
            DB::beginTransaction();

            $analyse = Analyse::findOrFail($this->analyseIdToDelete);
            $analyse->delete();

            DB::commit();
            $this->alert('success', 'Analyse supprimée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->alert('error', 'Une erreur est survenue lors de la suppression.');
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterExamen', 'filterType', 'filterStatus', 'filterLevel']);
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    protected function getFilteredAnalyses()
    {
        return Analyse::when($this->filterLevel, function ($query) {
                $query->where('level', $this->filterLevel);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('code', 'like', "%{$this->search}%")
                        ->orWhere('designation', 'like', "%{$this->search}%")
                        ->orWhere('abr', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterExamen, function ($query) {
                $query->where('examen_id', $this->filterExamen);
            })
            ->when($this->filterType, function ($query) {
                $query->where('analyse_type_id', $this->filterType);
            })
            ->when($this->filterStatus !== '', function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function export()
    {
        try {
            $analyses = $this->getFilteredAnalyses()
                ->with(['examen', 'analyseType'])
                ->get();

            $csvExporter = Writer::createFromString('');
            $csvExporter->setDelimiter(';');
            $csvExporter->setOutputBOM(Writer::BOM_UTF8);

            $csvExporter->insertOne([
                'Code',
                'Niveau',
                'Abréviation',
                'Désignation',
                'Description',
                'Prix',
                'Examen',
                'Type d\'analyse',
                'Statut',
                'Date de création'
            ]);

            foreach ($analyses as $analyse) {
                $csvExporter->insertOne([
                    $analyse->code,
                    $analyse->level,
                    $analyse->abr,
                    $analyse->designation,
                    $analyse->description,
                    number_format($analyse->prix, 2, ',', ' ') . ' Ar',
                    $analyse->examen->name ?? '',
                    $analyse->analyseType->name ?? '',
                    $analyse->status ? 'Actif' : 'Inactif',
                    $analyse->created_at->format('d/m/Y H:i')
                ]);
            }

            $filename = 'analyses_export_' . date('Y-m-d_His') . '.csv';

            return response()->streamDownload(function() use ($csvExporter) {
                echo $csvExporter->getContent();
            }, $filename, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Encoding' => 'UTF-8',
            ]);

        } catch (\Exception $e) {
            $this->alert('error', 'Erreur lors de l\'export: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.analyses.analyse-principal', [
            'analyses' => $this->getFilteredAnalyses()->paginate($this->perPage),
            'examens' => Cache::remember('examens_list', 3600, fn() => Examen::all()),
            'analyseTypes' => Cache::remember('analyse_types_list', 3600, fn() => AnalyseType::all()),
            'levelOptions' => AnalyseLevel::cases(),
            'levels' => [
                '' => 'Tous les niveaux',
                AnalyseLevel::PARENT->value => 'Parent',
                AnalyseLevel::NORMAL->value => 'Normal',
                AnalyseLevel::CHILD->value => 'Child'
            ]
        ]);
    }
}
