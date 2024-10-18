<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use App\Models\Profile;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;

class ProfileCreation extends Component
{
    use WithFileUploads;

    public User $user;

    #[Rule('required|in:homme,femme,autre')]
    public $sexe = '';

    #[Rule('required|string|max:255')]
    public $adresse = '';

    #[Rule('required|string|max:100')]
    public $ville = '';

    #[Rule('required|string|max:100')]
    public $province = '';

    #[Rule('nullable|image|max:1024')] // max 1MB
    public $photo;

    public function mount(User $user)
    {
        $this->user = $user;
    }

    public function createProfile()
    {
        $this->validate();

        $photoPath = null;
        if ($this->photo) {
            $photoPath = $this->photo->store('profile-photos', 'public');
        }

        Profile::create([
            'user_id' => $this->user->id,
            'sexe' => $this->sexe,
            'adresse' => $this->adresse,
            'ville' => $this->ville,
            'province' => $this->province,
            'photo_path' => $photoPath,
        ]);

        session()->flash('message', 'Profil créé avec succès!');
        return $this->redirect(route('dashboard'), navigate: true);
    }


    public function render()
    {
        return view('livewire.admin.users.profile-creation');
    }
}
