{{-- resources/views/emails/facture.blade.php --}}
<div>
    <p>Bonjour {{ $prescription->patient->nom }} {{ $prescription->patient->prenom }},</p>

    <p>Veuillez trouver ci-joint votre facture pour les analyses effectuées le {{ $prescription->created_at->format('d/m/Y') }}.</p>

    <p>Cordialement,<br>
    L'équipe du laboratoire</p>
</div>
