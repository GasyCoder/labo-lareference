{{-- views/livewire/secretaire/partials/prescription-card.blade.php --}}
@if($prescriptions->isNotEmpty())
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Patient</th>
                <th scope="col">Prescripteur</th>
                <th scope="col">Analyses</th>
                <th scope="col">Créé</th>
                <th scope="col">Statut</th>
                <th scope="col" class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($prescriptions as $prescription)
                <tr>
                    <td class="fw-medium">
                        {{ $prescription->patient->formatted_ref ?? 'Non défini' }}
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
                            <span>{{ $prescription->prescripteur ? $prescription->prescripteur->name : ($prescription->nouveau_prescripteur_nom ?? 'Non assigné') }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($prescription->analyses->take(3) as $analyse)
                                <span class="badge rounded-pill bg-primary px-1 py-1">
                                    <i class="fas fa-flask me-1"></i>{{ $analyse->abr }}
                                </span>
                            @endforeach
                            @if($prescription->analyses->count() > 3)
                                <div class="dropdown">
                                    <span class="badge rounded-pill bg-secondary dropdown-toggle px-2 py-1"
                                        role="button"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        +{{ $prescription->analyses->count() - 3 }}
                                    </span>
                                    <ul class="dropdown-menu py-1">
                                        @foreach($prescription->analyses->skip(3) as $analyse)
                                            <li>
                                                <span class="dropdown-item py-1">
                                                    <i class="fas fa-flask me-1"></i>{{ $analyse->abr }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-1">
                            <i class="fas fa-calendar-alt text-muted"></i>
                            <small>{{ $prescription->created_at->diffForHumans(['parts' => 1, 'short' => true]) }}</small>
                        </div>
                    </td>
                    <td>
                        <x-prescription-status :status="$prescription->status" />
                    </td>
                    <td>
                        <div class="d-flex gap-2 justify-content-end">
                            @if($prescription->trashed())
                                {{-- Actions pour les prescriptions dans la corbeille --}}
                                <a href="{{ route('secretaire.prescriptions.profil', ['id' => $prescription->id]) }}"
                                   class="btn btn-sm btn-info"
                                   title="Voir détails">
                                    <i class="fas fa-user"></i>
                                </a>
                                <button wire:click="confirmRestore({{ $prescription->id }})"
                                        class="btn btn-sm btn-warning"
                                        title="Restaurer">
                                    <i class="fas fa-undo-alt"></i>
                                </button>
                                <button wire:click="confirmPermanentDelete({{ $prescription->id }})"
                                        class="btn btn-sm btn-danger"
                                        title="Supprimer définitivement">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            @else
                                {{-- Actions communes pour les prescriptions actives --}}
                                <a href="{{ route('secretaire.prescriptions.profil', ['id' => $prescription->id]) }}"
                                   class="btn btn-sm btn-info"
                                   title="Voir détails">
                                    <i class="fas fa-user"></i>
                                </a>

                                @if(!$prescription->status === 'VALIDE')
                                <a href="{{ route('secretaire.prescriptions.edit', ['id' => $prescription->id]) }}"
                                    class="btn btn-sm btn-success"
                                    title="Modifier">
                                     <i class="fas fa-edit"></i>
                                 </a>
                                 <button wire:click="confirmDelete({{ $prescription->id }})"
                                    class="btn btn-sm btn-danger"
                                    title="Corbeille">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif

                                @if($prescription->status === 'VALIDE')
                                    <x-pdf-download-button :prescription="$prescription" title="Aperçu en pdf" />
                                    @if(!$prescription->is_archive)
                                        <button wire:click="confirmArchive({{ $prescription->id }})"
                                                class="btn btn-sm btn-secondary"
                                                title="Archiver">
                                            <i class="fas fa-archive"></i>
                                        </button>
                                    @else
                                        <button wire:click="confirmUnarchive({{ $prescription->id }})"
                                                class="btn btn-sm btn-warning"
                                                title="Désarchiver">
                                            <i class="fas fa-box-open"></i>
                                        </button>
                                    @endif
                                @endif
                            @endif
                        </div>
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
    Aucune prescription {{ $tab === 'actifs' ? 'active' : ($tab === 'valide' ? 'validée' : 'dans la corbeille') }} trouvée.
</div>
@endif
@include('layouts.scripts')
