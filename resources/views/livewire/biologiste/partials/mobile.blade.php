<!-- Mode mobile -->
<div class="d-md-none">
    @forelse($prescriptions as $key => $prescription)
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <!-- En-tête avec avatar et nom -->
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex justify-content-center align-items-center bg-primary text-white"
                        style="width: 40px; height: 40px;">
                        {{ strtoupper(substr($prescription->patient->nom, 0, 1)) }}{{ strtoupper(substr($prescription->patient->prenom, 0, 1)) }}
                    </div>
                    <div>
                        <h6 class="mb-0">{{ $prescription->patient->nom }} {{ $prescription->patient->prenom }}</h6>
                        @if($prescription->patient_type == 'URGENCE-NUIT')
                            <small class="text-danger"><i class="fas fa-ambulance me-1"></i> Urgence nuit</small>
                        @elseif($prescription->patient_type == 'URGENCE-JOUR')
                            <small class="text-danger"><i class="fas fa-ambulance me-1"></i> Urgence jour</small>
                        @endif
                    </div>
                </div>

                <!-- Informations prescripteur -->
                <p class="mt-3 mb-2"><strong>Presc:</strong>
                    <span title="{{ $prescription->prescripteur?->nom }}">
                        {{ Str::limit($prescription->prescripteur?->nom, 25, '...') ?? 'Non assigné' }}
                    </span>
                </p>

                <!-- Badges analyses -->
                <div class="mb-3">
                    @foreach($prescription->analyses as $analyse)
                        <span class="badge bg-info me-1 mb-1">{{ $analyse->abr }}</span>
                    @endforeach
                </div>

                <!-- Actions buttons -->
                <div class="d-flex gap-2">
                    <!-- Bouton Aperçu PDF -->
                    <x-preview-mobile :prescription="$prescription"/>
                    
                    @if($prescription->status !== 'VALIDE')
                    <!-- Bouton Valider -->
                    <button wire:click="validateAnalyse({{ $prescription->id }})"
                        wire:loading.attr="disabled"
                        class="btn btn-success flex-grow-1 d-flex align-items-center justify-content-center"
                        style="font-size: 13px; padding: 8px 0; background-color: #198754; border-radius: 6px;">
                    <span wire:loading.remove>
                        <i class="fas fa-check-circle"></i>
                        Valider
                    </span>
                    <span wire:loading>
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                    </button>
                    @endif
                    <!-- Bouton Voir détails -->
                    <button wire:click="openAnalyse({{ $prescription->id }})" 
                        class="btn btn-primary flex-grow-1 d-flex align-items-center justify-content-center"
                        style="font-size: 13px; padding: 8px 0; background-color: #6f42c1; border-radius: 6px;">
                        <i class="fas fa-eye me-2"></i>
                        Détails
                    </button>
                </div>

            </div>
        </div>
    @empty
        <div class="alert alert-danger text-center">
            Aucune analyse {{ $tab === 'actifs' ? 'active' : ($tab === 'valide' ? 'validée' : 'terminé par technicien') }} sur cet onglet.
        </div>
    @endforelse
</div>