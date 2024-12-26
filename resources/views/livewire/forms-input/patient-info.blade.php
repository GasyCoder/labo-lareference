@php
    $statusConfig = [
        'EN_COURS' => ['class' => 'info', 'icon' => 'spinner', 'text' => 'En cours'],
        'TERMINE' => ['class' => 'primary', 'icon' => 'check', 'text' => 'Terminé'],
        'VALIDE' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Validé'],
    ][$prescription->status] ?? ['class' => 'secondary', 'icon' => 'question', 'text' => 'Inconnu'];
@endphp

<!-- Informations du patient -->
<div class="col-lg-8">
    <div class="d-flex align-items-center mb-3">
        <div class="icon-circle bg-primary-soft me-3">
            <i class="fas fa-flask text-primary"></i>
        </div>
        <h2 class="mb-0">Résultats d'analyses</h2>
    </div>

    <div class="row g-3">
        <!-- Infos patient -->
        <div class="col-md-6">
            <div class="info-card mb-2">
                <div class="info-icon">
                    <i class="fas fa-user text-primary"></i>
                </div>
                <div class="info-content">
                    <label>Patient</label>
                    <strong>{{ $prescription->patient->nom }} {{ $prescription->patient->prenom }}</strong>
                </div>
            </div>
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-hashtag text-primary"></i>
                </div>
                <div class="info-content">
                    <label>Référence</label>
                    <strong>{{ $prescription->patient->formatted_ref ?? 'N/A' }}</strong>
                </div>
            </div>
        </div>
        <!-- Infos prescription -->
        <div class="col-md-6">
            <div class="info-card mb-2">
                <div class="info-icon">
                    <i class="fas fa-calendar text-primary"></i>
                </div>
                <div class="info-content">
                    <label>Date de prescription</label>
                    <strong>{{ $prescription->created_at->format('d/m/Y') }}</strong>
                </div>
            </div>
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-clock text-primary"></i>
                </div>
                <div class="info-content">
                    <label>Statut</label>
                    @if($prescription->resultats()->where('validated_by', '!=', NULL)->exists())
                        <span class="badge bg-success rounded-pill">
                            <i class="fas fa-check-double me-1"></i> Validé
                        </span>
                    @else
                        <span class="badge bg-{{ $statusConfig['class'] }} rounded-pill">
                            <i class="fas fa-{{ $statusConfig['icon'] }} me-1"></i> {{ $statusConfig['text'] }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
