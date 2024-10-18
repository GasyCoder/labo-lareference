<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class GestionUsers extends Component
{
    use WithPagination, AuthorizesRequests, LivewireAlert;

    public $userIdToDelete;
    protected $listeners = ['deleteConfirmed' => 'deleteUser'];

    public function render()
    {
        return view('livewire.admin.users.index', [
            'allusers' => User::whereDoesntHave('roles', fn($q) => $q->where('name', 'superadmin'))->latest()->paginate(10),
            'biologistes' => User::role('biologiste')->latest()->paginate(10),
            'secretaires' => User::role('secretaire')->latest()->paginate(10),
            'techniciens' => User::role('technicien')->latest()->paginate(10),
            'prescripteurs' => User::role('prescripteur')->latest()->paginate(10),
        ]);
    }

    public function confirmDelete($userId)
    {
        $this->userIdToDelete = $userId;

        $this->confirm('Êtes-vous sûr de vouloir supprimer Utilisateur ?', [
            'toast' => true,
            'position' => 'center',
            'showConfirmButton' => true,
            'confirmButtonText' => 'Oui, supprimer',
            'cancelButtonText' => 'Annuler',
            'onConfirmed' => 'deleteConfirmed',
            'onCancelled' => 'cancelledDelete'
        ]);
    }

    public function deleteUser()
    {
        $user = User::find($this->userIdToDelete);

        if ($user) {
            $user->delete();
            $this->alert('success', 'Utilisateur supprimé avec succès.');
        } else {
            $this->alert('error', 'Utilisateur non trouvé.');
        }

        $this->userIdToDelete = null;
    }

    public function cancelledDelete()
    {
        $this->alert('info', 'Suppression annulée');
        $this->userIdToDelete = null;
    }
}
