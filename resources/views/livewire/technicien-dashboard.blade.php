<div class="container my-4">
    <!-- Titre Principal -->
    <h5 class="mb-4">Historique des analyses du jour</h5>

    <!-- Compteurs de Statut -->
    <div class="mb-4">
        <span class="badge bg-secondary me-2">Total : {{ $counters['total'] }}</span>
        <span class="badge bg-warning me-2">En cours : {{ $counters['enCours'] }}</span>
        <span class="badge bg-info">En attente : {{ $counters['enAttente'] }}</span>
    </div>

    <!-- Barre de Filtrage et Recherche -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <input
                wire:model.debounce.300ms="search"
                type="text"
                class="form-control"
                placeholder="Rechercher un patient..."
                aria-label="Rechercher un patient"
            >
        </div>
        <div class="col-md-3 mb-3">
            <select wire:model="perPage" class="form-select" aria-label="Nombre de résultats par page">
                <option value="5">5 par page</option>
                <option value="10">10 par page</option>
                <option value="20">20 par page</option>
            </select>
        </div>
        <div class="col-md-3 mb-3">
            <input
                type="date"
                wire:model="selectedDate"
                class="form-control"
                aria-label="Sélectionner une date"
            >
        </div>
    </div>

    <!-- Accordéon des Analyses -->
    <div class="accordion" id="accordionAnalyses">
        @forelse($groupedResultats as $groupKey => $groupeResultats)
            @php
                list($patientId, $dateTime) = explode('|', $groupKey);
                $firstResult = $groupeResultats->first();
                $patient = $firstResult->prescription->patient;
                $parent = $firstResult->analyse->parent;
                // Générer un ID unique pour chaque accordéon
                $accordionId = 'accordion' . $patientId . str_replace([':', '/', ' '], '', $dateTime);
            @endphp

            <div class="accordion-item">
                <h2 class="accordion-header" id="heading{{ $accordionId }}">
                    <button
                        class="accordion-button collapsed d-flex align-items-center"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse{{ $accordionId }}"
                        aria-expanded="false"
                        aria-controls="collapse{{ $accordionId }}"
                    >
                        <!-- Icône Représentative (Fe Feather Icons) -->
                        <i class="fe fe-user me-3" style="font-size: 1.5rem; color: #0d6efd;"></i>

                        <!-- Informations du Patient -->
                        <div class="flex-grow-1">
                            <div class="fw-bold">Patient : {{ $patient->nom }} {{ $patient->prenom }}</div>
                            <div class="text-muted" style="font-size: 0.9rem;">
                                Réf : #{{ $patient->formatted_ref }} | À : {{ $dateTime }}
                            </div>
                        </div>
                    </button>
                </h2>

                <div id="collapse{{ $accordionId }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $accordionId }}" data-bs-parent="#accordionAnalyses">
                    <div class="accordion-body">
                        <!-- Tableau des Analyses -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Analyse</th>
                                        <th>Résultats</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Afficher l'analyse PARENT -->
                                    @if($parent)
                                        <tr class="table-secondary">
                                            <td colspan="3"><strong>{{ $parent->designation }}</strong></td>
                                        </tr>
                                    @endif

                                    <!-- Afficher les analyses enfants -->
                                    @foreach($groupeResultats as $resultat)
                                        @php
                                            // Déterminer si l'analyse doit être en gras
                                            $isPathologique = $resultat->interpretation === 'PATHOLOGIQUE';
                                            $isBold = $resultat->analyse->is_bold ?? false;
                                            $rowClass = ($isPathologique || $isBold) ? 'fw-bold' : '';

                                            // Vérifier si l'analyse est un antibiogramme ou leucocytes
                                            $isAntibiogram = isset($resultat->analyse->result_disponible['bacteries']);
                                            $isLeucocytes = $resultat->analyse_type_id === 13;
                                        @endphp
                                        <tr class="{{ $rowClass }}">
                                            <td>{{ $resultat->analyse->designation }}</td>
                                            <td>
                                                @if($resultat->resultats)
                                                    {{ $resultat->resultats }}
                                                @endif
                                                @if($resultat->valeur)
                                                    @if($resultat->resultats) - @endif
                                                    {{ trim($resultat->valeur, '"') }}
                                                @endif
                                                @if(isset($resultat->analyse->result_disponible['unite']))
                                                    {{ $resultat->analyse->result_disponible['unite'] }}
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    switch($resultat->status) {
                                                        case 'EN_ATTENTE':
                                                            $badgeClass = 'bg-warning text-dark';
                                                            break;
                                                        case 'EN_COURS':
                                                            $badgeClass = 'bg-info text-dark';
                                                            break;
                                                        case 'TERMINE':
                                                            $badgeClass = 'bg-secondary text-white';
                                                            break;
                                                        case 'VALIDE':
                                                            $badgeClass = 'bg-success';
                                                            break;
                                                        case 'ARCHIVE':
                                                            $badgeClass = 'bg-dark text-white';
                                                            break;
                                                        default:
                                                            $badgeClass = 'bg-light text-dark';
                                                    }
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ $resultat->status }}</span>
                                            </td>
                                        </tr>

                                        {{-- Affichage des analyses spécifiques (Antibiogramme, Leucocytes) --}}
                                        @if($isAntibiogram)
                                            @foreach($resultat->analyse->result_disponible['bacteries'] as $bacterieName => $bacterieData)
                                                @php
                                                    $antibiogramData = $bacterieData['antibiotics'] ?? [];
                                                @endphp
                                                <tr class="{{ $rowClass }}">
                                                    <td class="ps-4">Bactérie : {{ $bacterieName }}</td>
                                                    <td colspan="2"></td>
                                                </tr>
                                                @foreach($antibiogramData as $antibiotic => $sensibilite)
                                                    <tr class="{{ $rowClass }}">
                                                        <td class="ps-5">{{ $antibiotic }}</td>
                                                        <td>{{ ucfirst($sensibilite) }}</td>
                                                        <td></td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @endif

                                        @if($isLeucocytes)
                                            @php
                                                $leucocytesData = json_decode($resultat->valeur, true);
                                            @endphp
                                            @if($leucocytesData)
                                                <tr class="{{ $rowClass }}">
                                                    <td class="ps-4">Polynucléaires</td>
                                                    <td>{{ $leucocytesData['polynucleaires'] ?? 'N/A' }}%</td>
                                                    <td></td>
                                                </tr>
                                                <tr class="{{ $rowClass }}">
                                                    <td class="ps-4">Lymphocytes</td>
                                                    <td>{{ $leucocytesData['lymphocytes'] ?? 'N/A' }}%</td>
                                                    <td></td>
                                                </tr>
                                            @endif
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-4">
                <p class="mb-0">Aucun résultat trouvé</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div>
            Montrant {{ $paginationInfo->firstItem() ?? 0 }} à {{ $paginationInfo->lastItem() ?? 0 }}
            de {{ $paginationInfo->total() }} résultats
        </div>
        <nav>
            {{ $paginationInfo->links('pagination::bootstrap-5') }}
        </nav>
    </div>
</div>
