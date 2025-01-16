{{-- QR CODE ICI --}}
<div class="doctor-info">
    <img
    src="data:image/png;base64,{{ base64_encode($qrcodeImage) }}"
    alt="QR Code-{{ str_pad($prescription->id, 5, '0', STR_PAD_LEFT) }}"
    class="qr-image"
    width="80"
    height="80">
</div>
<div class="patient-info">
    Résultats de : <b>{{ $prescription->patient->sexe }} {{ $prescription->patient->nom. ' ' .$prescription->patient->prenom }}</b><br>
    Age: {{ $prescription->age }} {{ $prescription->unite_age }}<br>
    Réf n° {{ $prescription->patient->formatted_ref ?? 'N/A' }} du {{ $prescription->created_at->format('d/m/Y') }}<br>
    Prescripteur: <b>{{ $prescription->prescripteur->nom ?? 'Non assigné' }}</b>
</div>
