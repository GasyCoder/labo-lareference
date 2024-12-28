<?php

namespace App\Livewire\Admin\Types;

use Livewire\Component;
use App\Models\AnalyseType;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AnalyseTypes extends Component
{
    use WithPagination, AuthorizesRequests, LivewireAlert;
    protected $paginationTheme = 'bootstrap';

    #[Rule('required|min:3', message: 'Le nom est requis et doit contenir au moins 3 caractères.')]
    public string $name = '';

    #[Rule('required|min:4', message: 'L\'abréviation est requise et doit contenir 4 caractères.')]
    public string $libelle = '';

    public $editingTypeId;

    #[Rule('boolean')]
    public bool $status = true;

    public $typeIdToDelete;
    protected $listeners = ['deleteConfirmed' => 'deleteType'];

    public function save()
    {
        $validatedData = $this->validate();

        $action = $this->editingTypeId ? 'update' : 'create';
        $type = $this->editingTypeId
            ? AnalyseType::findOrFail($this->editingTypeId)
            : new AnalyseType;

        $type->fill($validatedData);
        $type->save();

        $this->alert('success', ($action === 'update' ? 'Type d\'analyse mis à jour' : 'Nouveau type d\'analyse ajouté') . ' avec succès.');
        $this->resetForm();
        return redirect()->route('donnees.types-analyse');
    }

    public function edit($id)
    {
        $type = AnalyseType::findOrFail($id);
        $this->editingTypeId = $id;
        $this->name = $type->name;
        $this->libelle = $type->libelle;
        $this->status = $type->status;
    }

    public function confirmDelete($id)
    {
        $this->typeIdToDelete = $id;
        $this->confirm('Êtes-vous sûr de vouloir supprimer ce type d\'analyse ?', [
            'toast' => true,
            'position' => 'center',
            'showConfirmButton' => true,
            'cancelButtonText' => 'Annuler',
            'onConfirmed' => 'deleteConfirmed',
            'onCancelled' => 'cancelledDelete'
        ]);
    }

    public function deleteType()
    {
        if ($this->typeIdToDelete) {
            $type = AnalyseType::find($this->typeIdToDelete);
            if ($type) {
                // Vérifiez s'il y a des analyses principales ou des éléments liés
                if ($type->analysePrincipales()->count() > 0 || $type->analysesElements()->count() > 0) {
                    $this->alert('error', 'Impossible de supprimer ce type d\'analyse car il est lié à des analyses ou des éléments.');
                } else {
                    $type->delete();
                    $this->alert('success', 'Type d\'analyse supprimé avec succès.');
                }
            }
            $this->typeIdToDelete = null;
        }
    }

    public function cancelledDelete()
    {
        $this->alert('info', 'Vous avez annulé la suppression.');
    }

    private function resetForm()
    {
        $this->reset(['name', 'libelle', 'editingTypeId']);
        $this->resetValidation();
    }


    public function render()
    {
        return view('livewire.admin.types.analyse-types', [
            'types' => AnalyseType::latest()->paginate(10),
        ]);
    }

}
