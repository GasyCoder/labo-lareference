<?php

namespace App\Livewire\Secretaire;

use App\Models\Prescription;
use Livewire\Component;

class ProfilePrescription extends Component
{
    public Prescription $prescription;

    public function mount($id)
    {
        $this->prescription = Prescription::with('patient', 'prescripteur', 'analyses')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.secretaire.profile-prescription');
    }
}
