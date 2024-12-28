<div>
    <div class="container my-4">
        <!-- Titre Principal -->
        <h5 class="mb-4">Historique des validations du jour</h5>

        <!-- Compteurs de Statut -->
        <div class="mb-4">
            <span class="badge bg-success me-2">Validations : {{ $counters['validations'] }}</span>
            <span class="badge bg-warning">À valider : {{ $counters['aValider'] }}</span>
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

        <!-- Accordéon des Validations -->
        <div class="accordion" id="accordionValidations">
            @forelse($groupedValidations as $groupKey => $validationsGroup)
                @php
                    list($patientId, $dateTime) = explode('|', $groupKey);
                    $firstValidation = $validationsGroup->first();
                    $patient = $firstValidation->prescription->patient;
                    $parent = $firstValidation->analyse->parent;
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
                    <div id="collapse{{ $accordionId }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $accordionId }}" data-bs-parent="#accordionValidations">
                        <div class="accordion-body">
                            <!-- Tableau des Validations -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Heure</th>
                                            <th>Analyse</th>
                                            <th>Résultat</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Afficher l'analyse PARENT si disponible -->
                                        @if($parent)
                                            <tr class="table-secondary">
                                                <td colspan="4"><strong>{{ $parent->designation }}</strong></td>
                                            </tr>
                                        @endif

                                        <!-- Afficher les validations -->
                                        @foreach($validationsGroup as $validation)
                                            @php
                                                // Déterminer si la ligne doit être en gras
                                                $isPathologique = $validation->interpretation === 'PATHOLOGIQUE';
                                                $isBold = $validation->analyse->is_bold ?? false;
                                                $rowClass = ($isPathologique || $isBold) ? 'fw-bold' : '';

                                                // Vérifier si l'analyse est un antibiogramme ou leucocytes
                                                $isAntibiogram = isset($validation->analyse->result_disponible['bacteries']);
                                                $isLeucocytes = $validation->analyse_type_id === 13;
                                            @endphp
                                            <tr class="{{ $rowClass }}">
                                                <td>{{ $validation->validated_at->format('H:i') }}</td>
                                                <td>{{ $validation->analyse->designation }}</td>
                                                <td>
                                                    @if($validation->resultats)
                                                        {{ $validation->resultats }}
                                                    @endif
                                                    @if($validation->valeur)
                                                        @if($validation->resultats) - @endif
                                                        {{ trim($validation->valeur, '"') }}
                                                    @endif
                                                    @if(isset($validation->analyse->result_disponible['unite']))
                                                        {{ $validation->analyse->result_disponible['unite'] }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        switch($validation->status) {
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
                                                    <span class="badge {{ $badgeClass }}">{{ $validation->status }}</span>
                                                </td>
                                            </tr>

                                            {{-- Affichage des analyses spécifiques (Antibiogramme, Leucocytes) --}}
                                            @if($isAntibiogram)
                                                @foreach($validation->analyse->result_disponible['bacteries'] as $bacterieName => $bacterieData)
                                                    @php
                                                        $antibiogramData = $bacterieData['antibiotics'] ?? [];
                                                    @endphp
                                                    <tr class="{{ $rowClass }}">
                                                        <td class="ps-4">Bactérie : {{ $bacterieName }}</td>
                                                        <td colspan="2"></td>
                                                        <td></td>
                                                    </tr>
                                                    @foreach($antibiogramData as $antibiotic => $sensibilite)
                                                        <tr class="{{ $rowClass }}">
                                                            <td class="ps-5">{{ $antibiotic }}</td>
                                                            <td>{{ ucfirst($sensibilite) }}</td>
                                                            <td colspan="2"></td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                            @endif

                                            @if($isLeucocytes)
                                                @php
                                                    $leucocytesData = json_decode($validation->valeur, true);
                                                @endphp
                                                @if($leucocytesData)
                                                    <tr class="{{ $rowClass }}">
                                                        <td class="ps-4">Polynucléaires</td>
                                                        <td>{{ $leucocytesData['polynucleaires'] ?? 'N/A' }}%</td>
                                                        <td colspan="2"></td>
                                                    </tr>
                                                    <tr class="{{ $rowClass }}">
                                                        <td class="ps-4">Lymphocytes</td>
                                                        <td>{{ $leucocytesData['lymphocytes'] ?? 'N/A' }}%</td>
                                                        <td colspan="2"></td>
                                                    </tr>
                                                @endif
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
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
                    @if($paginationInfo->total() > 0)
                        Montrant {{ $paginationInfo->firstItem() }} à {{ $paginationInfo->lastItem() }} de {{ $paginationInfo->total() }} résultats
                    @else
                        Aucun résultat trouvé
                    @endif
                </div>
                <nav>
                    {{ $paginationInfo->links('pagination::bootstrap-5') }}
                </nav>
            </div>
        </div>

    </div>

    <!-- Initialisation des Icônes Feather (si utilisé via CDN) -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (feather) {
                feather.replace();
            }
        });
    </script>
</div>
