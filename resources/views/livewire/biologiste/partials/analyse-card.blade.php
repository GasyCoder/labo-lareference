<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="bg-light">
            <tr>
                <th>ID</th>
                <th>Statut</th>
                <th>Patient</th>
                <th>Créé</th>
                <th>Analyses</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>

            @forelse($prescriptions as $prescription)
                <tr>
                    <!-- ID -->
                    <td class="fw-medium">
                        {{ $prescription->patient->formatted_ref ?? 'Non défini' }}
                    </td>
                    <!-- Statut -->
                    <td>
                        <span class="badge rounded-pill px-3 py-2 d-flex align-items-center gap-2">
                            @if($prescription->status === 'TERMINE')
                           <!-- Icône pour Terminé -->
                            <span class="badge bg-warning">
                                <i class="fas fa-check-circle"></i>  Terminé</span>
                            @else
                             <!-- Icône pour Validé -->
                            <span class="badge bg-success">
                                <i class="fas fa-check-double"></i> Validé</span>
                            @endif
                        </span>
                    </td>
                    <!-- Patient -->
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex justify-content-center align-items-center"
                                 style="width: 40px; height: 40px; background-color: #8257FF; color: white;">
                                {{ strtoupper(substr($prescription->patient->nom, 0, 1)) }}{{ strtoupper(substr($prescription->patient->prenom, 0, 1)) }}
                            </div>
                            <span>{{ $prescription->patient->nom }} {{ $prescription->patient->prenom }}</span>
                        </div>
                    </td>

                    <!-- Date de création -->
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-calendar-alt text-muted"></i>
                            <small>{{ $prescription->created_at->diffForHumans(['parts' => 1, 'short' => true]) }}</small>
                        </div>
                    </td>

                    <!-- Analyses -->
                    <!-- Analyses avec design amélioré -->
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            {{-- Premiers badges --}}
                            @foreach($prescription->analyses->take(3) as $analyse)
                                <span class="badge rounded-pill bg-info px-2 py-1">
                                    <i class="fas fa-flask me-1"></i>{{ $analyse->abr }}
                                </span>
                            @endforeach

                            {{-- Badge dropdown plus compact --}}
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
                    <!-- Actions -->
                    <td>
                        <button wire:click="openAnalyse({{ $prescription->id }})"
                                class="btn btn-sm btn-primary rounded-circle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        Aucune analyse trouvée pour cet onglet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $prescriptions->links() }}
    </div>
</div>
