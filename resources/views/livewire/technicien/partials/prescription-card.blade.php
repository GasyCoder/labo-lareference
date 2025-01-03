{{-- resources/views/livewire/technicien/partials/prescription-card.blade.php --}}
@if($prescriptions->isNotEmpty())
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Réf</th>
                <th>Patient</th>
                <th>Prescripteur</th>
                <th>Analyses</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($prescriptions as $prescription)
                <tr>
                    <td class="fw-medium">
                        #{{ $prescription->patient->formatted_ref ?? 'Non défini' }}
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-success d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <span class="text-white fw-bold">
                                    {{ strtoupper(substr($prescription->patient->nom, 0, 1) . substr($prescription->patient->prenom, 0, 1)) }}
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $prescription->patient->nom }} {{ $prescription->patient->prenom }}</h6>
                                @if($prescription->patient_type == 'URGENCE-NUIT')
                                <small class="text-muted text-danger">
                                    <i class="fas fa-ambulance me-1"></i>
                                    Urgence nuit
                                </small>
                                @elseif($prescription->patient_type == 'URGENCE-JOUR')
                                <small class="text-muted text-danger">
                                    <i class="fas fa-ambulance me-1"></i>
                                    Urgence jour
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
                    {{-- Analyses --}}
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($prescription->analyses->take(3) as $analyse)
                                <span class="badge bg-primary-soft px-2 py-1">
                                   {{ $analyse->abr }}
                                </span>
                            @endforeach
                            @if($prescription->analyses->count() > 3)
                                <div class="dropdown">
                                    <span class="badge bg-secondary-soft dropdown-toggle px-2 py-1"
                                        role="button"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        +{{ $prescription->analyses->count() - 3 }}
                                    </span>
                                    <ul class="dropdown-menu py-1">
                                        @foreach($prescription->analyses->skip(3) as $analyse)
                                            <li>
                                                <span class="dropdown-item py-1">
                                                    {{ $analyse->abr }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </td>
                    {{-- Actions --}}
                    <td class="d-flex gap-2 align-items-center">
                        <button wire:click="openPrescription({{ $prescription->id }})"
                                class="btn btn-sm btn-primary d-flex align-items-center justify-content-center"
                                title="Voir détails" style="width: 32px; height: 32px;">
                            <i class="fas fa-eye text-white"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">
    {{ $prescriptions->links() }}
</div>
@else
<div class="alert alert-info">
    Aucune prescription {{ $tab === 'actifs' ? 'active' : ($tab === 'termine' ? 'terminée' : 'dans la corbeille') }} trouvée.
</div>
@endif
@include('layouts.scripts')




