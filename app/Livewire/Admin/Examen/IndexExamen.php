<?php

namespace App\Livewire\Admin\Examen;

use App\Models\Examen;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class IndexExamen extends Component
{
    use WithPagination, LivewireAlert, AuthorizesRequests;
    protected $paginationTheme = 'bootstrap';
    public $name = '';
    public $abr = '';
    public $status = true;
    public $editingExamenId;

    public $examenIdToDelete;
    protected $listeners = ['deleteConfirmed' => 'deleteExamen'];

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'min:3',
                function ($attribute, $value, $fail) {
                    $query = Examen::where('name', $value);
                    if ($this->editingExamenId) {
                        $query->where('id', '!=', $this->editingExamenId);
                    }
                    if ($query->exists()) {
                        $fail('Ce nom d\'examen existe déjà.');
                    }
                },
            ],
            'abr' => 'required|min:2|max:10',
            'status' => 'boolean',
        ];
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'abr' => $this->abr,
            'status' => $this->status,
        ];

        if ($this->editingExamenId) {
            $examen = Examen::findOrFail($this->editingExamenId);
            $examen->update($data);
            $this->alert('success', "Examen mis à jour avec succès.");
        } else {
            Examen::create($data);
            $this->alert('success', "Nouvel examen ajouté avec succès.");
        }

        $this->reset();
        return redirect()->route('admin.examen.list');
    }

    public function edit($id)
    {
        $examen = Examen::findOrFail($id);
        $this->editingExamenId = $id;
        $this->name = $examen->name;
        $this->abr = $examen->abr;
        $this->status = $examen->status;

        $this->dispatch('open-modal', 'newExamen');
    }

    public function confirmDelete($examenId)
    {
        $this->examenIdToDelete = $examenId;

        $this->confirm('Êtes-vous sûr de vouloir supprimer cette famille de examen ?', [
            'toast' => true,
            'position' => 'center',
            'showConfirmButton' => true,
            'confirmButtonText' => 'Oui, supprimer',
            'cancelButtonText' => 'Annuler',
            'onConfirmed' => 'deleteConfirmed',
            'onCancelled' => 'cancelledDelete'
        ]);
    }

    public function deleteExamen()
    {
        $examen = Examen::find($this->examenIdToDelete);

        if ($examen) {
            $examen->delete();
            $this->alert('success', 'Examen supprimé avec succès.');
        } else {
            $this->alert('error', 'Examen non trouvé.');
        }

        $this->examenIdToDelete = null;
    }

    public function render()
    {
        return view('livewire.admin.examen.index', [
            'examens' => Examen::latest()->paginate(10),
        ]);
    }


}
