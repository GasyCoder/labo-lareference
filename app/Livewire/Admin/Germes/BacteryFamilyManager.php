<?php

namespace App\Livewire\Admin\Germes;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\BacteryFamily;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BacteryFamilyManager extends Component
{
    use WithPagination, LivewireAlert, AuthorizesRequests;

    protected $paginationTheme = 'bootstrap';

    public $name = '';
    public $antibiotics = '';
    public $bacteries = '';
    public $editingFamilyId;

    public $status = true;

    public $germeIdToDelete;
    protected $listeners = ['deleteConfirmed' => 'deleteGerme'];

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'min:3',
                function ($attribute, $value, $fail) {
                    $query = BacteryFamily::where('name', $value);
                    if ($this->editingFamilyId) {
                        $query->where('id', '!=', $this->editingFamilyId);
                    }
                    if ($query->exists()) {
                        $fail('Ce nom de famille existe déjà.');
                    }
                },
            ],
            'antibiotics' => 'nullable',
            'bacteries' => 'nullable',
            'status' => 'boolean',
        ];
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'antibiotics' => $this->formatArray($this->antibiotics),
            'bacteries' => $this->formatArray($this->bacteries),
            'status' => $this->status ? true : false,
        ];

        if ($this->editingFamilyId) {
            $family = BacteryFamily::find($this->editingFamilyId);
            $family->update($data);
            $this->alert('success', "Famille mise à jour avec succès.");
        } else {
            BacteryFamily::create($data);
            $this->alert('success', "Nouvelle famille ajoutée avec succès.");
        }

        $this->resetForm();
        return redirect()->route('admin.germes.list');
    }

    public function edit($id)
    {
        $family = BacteryFamily::findOrFail($id);
        $this->editingFamilyId = $id;
        $this->name = $family->name;
        $this->antibiotics = implode(', ', $family->antibiotics);
        $this->bacteries = implode(', ', $family->bacteries);
    }


    public function delete($id)
    {
        BacteryFamily::destroy($id);
        session()->flash('message', 'Famille supprimée avec succès.');
    }

    private function resetForm()
    {
        $this->reset(['name', 'antibiotics', 'bacteries', 'editingFamilyId']);
        $this->resetValidation();
    }

    private function formatArray($string)
    {
        return array_map('trim', explode(',', $string));
    }


    public function confirmDelete($germeId)
    {
        $this->germeIdToDelete = $germeId;
        $this->confirm('Êtes-vous sûr de vouloir supprimer cette famille de bactéries ?', [
            'toast' => true,
            'position' => 'center',
            'showConfirmButton' => true,
            'confirmButtonText' => 'Oui, supprimer',
            'cancelButtonText' => 'Annuler',
            'onConfirmed' => 'deleteConfirmed',
            'onCancelled' => 'cancelledDelete'
        ]);
    }

    public function deleteGerme()
    {
        $germe = BacteryFamily::find($this->germeIdToDelete);

        if ($germe) {
            $germe->delete();
            $this->alert('success', 'Bacterie supprimé avec succès.');
        } else {
            $this->alert('error', 'Bacterie non trouvé.');
        }

        $this->germeIdToDelete = null;
    }

    public function cancelledDelete()
    {
        $this->alert('info', 'Suppression annulée');
        $this->germeIdToDelete = null;
    }

    public function render()
    {
        return view('livewire.admin.germes.bactery-family-manager', [

            'families' => BacteryFamily::latest()->paginate(10),

        ]);
    }
}
