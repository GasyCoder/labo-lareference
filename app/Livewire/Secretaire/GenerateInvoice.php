<?php

namespace App\Livewire\Secretaire;

use Livewire\Component;
use App\Models\Prescription;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateInvoice extends Component
{
    public $prescriptionId;

    public function mount($prescriptionId)
    {
        $this->prescriptionId = $prescriptionId;
    }

    public function generatePdf()
    {
        $prescription = Prescription::with(['patient', 'analyses'])->findOrFail($this->prescriptionId);

        $pdf = Pdf::loadView('pdf.invoice', ['prescription' => $prescription]);

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'facture.pdf');
    }

    public function render()
    {
        return view('livewire.secretaire.generate-invoice');
    }
}
