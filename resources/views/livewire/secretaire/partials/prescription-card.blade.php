{{-- resources/views/livewire/secretaire/partials/prescription-card.blade.php --}}
<div class="card h-100 shadow-sm border-0 rounded-lg hover-shadow-md transition-all">
    <div class="card-body">


        <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="d-flex align-items-center" style="max-width: 80%;">
                <div class="rounded-circle bg-{{ $isArchived ? 'secondary' : 'primary' }}
                text-white d-flex justify-content-center align-items-center shadow-sm"
                style="width: 40px; height: 40px; flex-shrink: 0;">
                    <span class="fs-6 fw-bold">
                        {{ strtoupper(substr($prescription->patient->nom, 0, 1) . substr($prescription->patient->prenom, 0, 1)) }}
                    </span>
                </div>
                <div class="ms-2 overflow-hidden">
                    <h6 class="card-title fw-bold mb-0 text-truncate" title="{{ $prescription->patient->nom }} {{ $prescription->patient->prenom }}">
                        {{ $prescription->patient->nom }} {{ $prescription->patient->prenom }}
                    </h6>
                </div>
            </div>
            @php
            $statusConfig = [
                'EN_ATTENTE' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'En attente'],
                'EN_COURS' => ['class' => 'info', 'icon' => 'spinner', 'text' => 'En cours'],
                'TERMINE' => ['class' => 'success', 'icon' => 'check', 'text' => ''],
                'VALIDE' => ['class' => 'primary', 'icon' => 'thumbs-up', 'text' => ''],
                'ARCHIVE' => ['class' => 'secondary', 'icon' => 'archive', 'text' => ''],
            ][$prescription->status] ?? ['class' => 'secondary', 'icon' => 'question', 'text' => 'Inconnu'];
        @endphp
        <span class="badge bg-{{ $statusConfig['class'] }} text-white d-inline-flex align-items-center"
              style="font-size: 0.7rem; padding: 0.25em 0.5em;"
              title="{{ $prescription->status }}">
            <i class="fas fa-{{ $statusConfig['icon'] }} {{ $statusConfig['text'] ? 'me-1' : '' }}"></i>
            {{ $statusConfig['text'] }}
        </span>
        </div>


        <div class="mb-2">
            @if($prescription->patient->telephone)
                <p class="card-text mb-1 text-muted" style="font-size: 0.85rem;">
                    <i class="fas fa-phone-alt me-2 text-primary"></i>
                    {{ $prescription->patient->telephone }}
                </p>
            @endif
            <p class="card-text mb-1">
                <i class="fas fa-user-md me-2 text-primary"></i>
                <strong>Prescripteur:</strong>
                {{ $prescription->prescripteur ? $prescription->prescripteur->name : ($prescription->nouveau_prescripteur_nom ?? 'Non assigné') }}
            </p>
            <p class="card-text mb-1" title="{{ $prescription->created_at->format('d/m/Y H:i') }}">
                <i class="fas fa-calendar-alt me-2 text-primary"></i>
                <strong>Créé:</strong>
                {{ $prescription->created_at->diffForHumans() }}
            </p>
        </div>
        <div>
            <strong><i class="fas fa-vial me-2 text-primary"></i>Analyses:</strong>
            <div class="mt-2">
                @foreach($prescription->analyses->take(3) as $analyse)
                    <span class="badge bg-info text-dark me-1 mb-1">{{ $analyse->abr }}</span>
                @endforeach
                @if($prescription->analyses->count() > 3)
                    <span class="badge bg-secondary text-white">+{{ $prescription->analyses->count() - 3 }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="card-footer bg-transparent border-top pt-3">
        <div class="d-flex justify-content-between">
            @if(!$isArchived)
                <a href="{{ route('admin.prescriptions.edit', ['id' => $prescription->id]) }}"
                   class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit me-1"></i>Modifier
                </a>
                <button wire:click="confirmDelete({{ $prescription->id }})"
                        class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-trash me-1"></i>Corbeille
                </button>
            @else
            <button wire:click="confirmRestore({{ $prescription->id }})"
                    class="btn btn-sm btn-outline-success">
                <i class="fas fa-undo-alt me-1"></i>Restaurer
            </button>
            <button wire:click="confirmPermanentDelete({{ $prescription->id }})"
                class="btn btn-sm btn-outline-danger">
                <i class="fas fa-trash-alt me-1"></i>Supprimer définitif
            </button>
            @endif
        </div>
    </div>
</div>
