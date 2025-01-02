<div class="table-responsive">
    <table class="table table-hover align-middle d-none d-md-table">
        <thead class="bg-light text-sm">
            <tr>
                <th>ID</th>
                <th>Patient</th>
                <th>Prescripteur</th>
                <th>Analyses</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($prescriptions as $key => $prescription)
                <tr>
                    <td class="fw-semibold text-center">{{ $key + 1 }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex justify-content-center align-items-center bg-primary text-white"
                                style="width: 40px; height: 40px;">
                                {{ strtoupper(substr($prescription->patient->nom, 0, 1)) }}{{ strtoupper(substr($prescription->patient->prenom, 0, 1)) }}
                            </div>
                            <div>
                                <span class="d-block">{{ $prescription->patient->nom }} {{ $prescription->patient->prenom }}</span>
                                @if($prescription->patient_type == 'URGENCE-NUIT')
                                    <small class="text-danger"><i class="fas fa-ambulance me-1"></i> Urgence nuit</small>
                                @elseif($prescription->patient_type == 'URGENCE-JOUR')
                                    <small class="text-danger"><i class="fas fa-ambulance me-1"></i> Urgence jour</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-1">
                            <i class="fas fa-user-md text-primary"></i>
                            <span title="{{ $prescription->prescripteur?->nom }}">
                                {{ Str::limit($prescription->prescripteur?->nom, 25, '...') ?? 'Non assigné' }}
                            </span>
                        </div>
                     </td>
                    <td>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($prescription->analyses->take(3) as $analyse)
                                <span class="badge rounded-pill bg-info px-3 py-1">
                                    <i class="fas fa-flask me-1"></i>{{ $analyse->abr }}
                                </span>
                            @endforeach
                            @if($prescription->analyses->count() > 3)
                                <div class="dropdown">
                                    <span class="badge rounded-pill bg-secondary dropdown-toggle px-3 py-1"
                                          role="button"
                                          data-bs-toggle="dropdown"
                                          aria-expanded="false">
                                        +{{ $prescription->analyses->count() - 3 }}
                                    </span>
                                    <ul class="dropdown-menu">
                                        @foreach($prescription->analyses->skip(3) as $analyse)
                                            <li>
                                                <span class="dropdown-item">
                                                    <i class="fas fa-flask me-1"></i>{{ $analyse->abr }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </td>
                    <td class="d-flex flex-wrap gap-2">
                        <button wire:click="openAnalyse({{ $prescription->id }})" class="btn btn-sm btn-primary rounded-circle" title="Voir l'analyse">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        Aucune analyse trouvée pour cet onglet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Mode mobile -->
    <div class="d-md-none">
        @forelse($prescriptions as $key => $prescription)
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
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
                    <p class="mt-3"><strong>Presc:</strong>
                        <span title="{{ $prescription->prescripteur?->nom }}">
                            {{ Str::limit($prescription->prescripteur?->nom, 25, '...') ?? 'Non assigné' }}
                        </span>
                    </p>
                    <p>
                        @foreach($prescription->analyses as $analyse)
                            <span class="badge bg-info">{{ $analyse->abr }}</span>
                        @endforeach
                    </p>
                    <button wire:click="openAnalyse({{ $prescription->id }})" class="btn btn-primary btn-sm">
                        Voir l'analyse
                    </button>
                </div>
            </div>
        @empty
            <p class="text-center text-muted">Aucune analyse trouvée pour cet onglet.</p>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-4 d-flex justify-content-center">
        {{ $prescriptions->links() }}
    </div>
</div>

<style>
@media (max-width: 768px) {
    .table {
        display: none;
    }

    .card {
        border-radius: 10px;
    }

    .card-body {
        padding: 1rem;
    }

    .badge {
        font-size: 0.75rem;
    }
}
</style>
