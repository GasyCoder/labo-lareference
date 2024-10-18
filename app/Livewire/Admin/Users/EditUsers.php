<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class EditUsers extends Component
{
    use LivewireAlert, AuthorizesRequests;

    public User $user;

    #[Rule('required|string|max:255')]
    public $name = '';

    #[Rule('required|string|email|max:255')]
    public $email = '';

    public $password = '';

    public $password_confirmation = '';

    #[Rule('required|string|in:biologiste,secretaire,technicien,prescripteur')]
    public $role = '';

    public function mount(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->roles->first()->name ?? '';
    }

    public function updateUser()
    {

        $validatedData = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $this->user->id,
            'role' => 'required|string|in:biologiste,secretaire,technicien,prescripteur',
            'password' => $this->password ? 'string|min:8|confirmed' : '',
        ]);

        $this->user->update([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
        ]);

        if ($this->password) {
            $this->user->update(['password' => Hash::make($this->password)]);
        }

        $this->user->syncRoles([$validatedData['role']]);

        if (!$this->user->hasRole($validatedData['role'])) {
            \Log::error("Échec de l'attribution du rôle {$validatedData['role']} à l'utilisateur {$this->user->id}");
            $this->alert('error', "Erreur lors de l'attribution du rôle. Veuillez contacter l'administrateur.");
            return;
        }

        $this->alert('success', "L'utilisateur a été mis à jour avec succès.");
        return redirect()->route('admin.users.list');
    }

    public function render()
    {
        return view('livewire.admin.users.edit-users');
    }
}
