<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Notifications\NewUserWelcome;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CreateUsers extends Component
{
    use WithPagination, LivewireAlert, AuthorizesRequests;

    #[Rule('required|string|max:255')]
    public $name = '';

    #[Rule('required|string|email|max:255|unique:users')]
    public $email = '';

    #[Rule('required|string|min:8|confirmed')]
    public $password = '';

    public $password_confirmation = '';

    #[Rule('required|string|in:biologiste,secretaire,technicien,prescripteur')]
    public $role = '';

    public function register()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        //dd($user);
        $user->assignRole($this->role);

        // Vérification de l'attribution du rôle
        if (!$user->hasRole($this->role)) {
            \Log::error("Échec de l'attribution du rôle {$this->role} à l'utilisateur {$user->id}");
            $this->alert('error', "Erreur lors de l'attribution du rôle. Veuillez contacter l'administrateur.");
            return;
        }

        $token = Str::random(60);
        $user->forceFill(['remember_token' => $token])->save();
        $user->notify(new NewUserWelcome($token));

        $this->reset();
        $this->alert('success', "L'utilisateur a été créé avec succès avec le rôle de {$this->role}.");
        return redirect()->route('admin.users.create');
    }

    public function render()
    {
        return view('livewire.admin.users.create-users');
    }
}
