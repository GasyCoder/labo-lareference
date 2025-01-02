<div class="container-fluid py-4">
    <section class="container-fluid p-4">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h3>
                <i class="fas fa-archive me-2"></i>
                Prescriptions archivées ({{ $archivedPrescriptions->total() }})
            </h3>
        </div>
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       class="form-control"
                       placeholder="Rechercher...">
            </div>
        </div>
    </div>

    <!-- Tableau des archives -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Patient</th>
                            <th>Prescripteur</th>
                            <th>Analyses</th>
                            <th>Date d'archivage</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($archivedPrescriptions as $prescription)
                            <tr>
                                <td>#{{ $prescription->patient->formatted_ref }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <span class="text-white fw-bold">
                                                {{ strtoupper(substr($prescription->patient->nom, 0, 1) . substr($prescription->patient->prenom, 0, 1)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $prescription->patient->nom }} {{ $prescription->patient->prenom }}</h6>
                                            @if($prescription->patient->telephone)
                                                <small class="text-muted">
                                                    <i class="fas fa-phone-alt me-1"></i>{{ $prescription->patient->telephone }}
                                                </small>
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
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($prescription->analyses->take(3) as $analyse)
                                            <span class="badge bg-info">{{ $analyse->abr }}</span>
                                        @endforeach
                                        @if($prescription->analyses->count() > 3)
                                            <span class="badge bg-secondary">+{{ $prescription->analyses->count() - 3 }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    {{ $prescription->updated_at->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-end">
                                        <x-pdf-download-button :prescription="$prescription" title="Aperçu en pdf" />
                                        @if(auth()->user()->can('secretaire'))
                                        <button wire:click="confirmUnarchive({{ $prescription->id }})"
                                                class="btn btn-sm btn-warning"
                                                title="Désarchiver">
                                            <i class="fas fa-box-open"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-archive fa-2x mb-3"></i>
                                        <p class="mb-0">Aucune prescription archivée trouvée</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $archivedPrescriptions->links() }}
            </div>
        </div>
    </div>
    </section>
</div>
