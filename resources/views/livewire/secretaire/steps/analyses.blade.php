{{-- livewire.secretaire.steps.analyses --}}
<div x-data="{
    showPrelevements: @entangle('showPrelevements'),
    searchTimeout: null,
    updateSearch(value) {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            @this.set('analyseSearch', value);
        }, 300);
    }
}">
    <h4 class="card-title mb-4">Sélectionner les analyses</h4>

    {{-- Barre de recherche --}}
    <div class="mb-3" wire:key="search-section">
        <label for="analyseSearch" class="form-label">
            <i class="fas fa-flask me-2 text-primary"></i>
            Rechercher des analyses
        </label>
        <div class="input-group">
            <input type="text"
                   id="analyseSearch"
                   class="form-control"
                   placeholder="Tapez pour rechercher..."
                   x-on:input="updateSearch($event.target.value)"
                   wire:loading.class="opacity-75"
                   autocomplete="off">
            <button class="btn btn-outline-secondary"
                    type="button"
                    x-on:click="$wire.set('analyseSearch', ''); $el.previousElementSibling.value = ''">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Indicateur de recherche --}}
        <div wire:loading wire:target="analyseSearch" class="text-muted small mt-1">
            <i class="fas fa-spinner fa-spin"></i> Recherche en cours...
        </div>
    </div>

    {{-- Suggestions d'analyses --}}
    @if(!empty($analyseSuggestions))
    <div class="list-group mb-3">
        @foreach($analyseSuggestions as $analyse)
            @php
                $isMainAnalysis = $analyse['level'] === 'PARENT' ||
                                (empty($analyse['parent_code']) && $analyse['level'] === 'NORMAL');
                $isSpecialGroup = in_array($analyse['abr'], ['HB', 'HSTASE']);
            @endphp

            <button type="button"
                    class="list-group-item list-group-item-action {{ !$isMainAnalysis ? 'ps-4' : '' }}"
                    wire:click="addAnalyse({{ $analyse['id'] }})">

                @if(!$isMainAnalysis)
                    <i class="fas fa-arrow-right me-2 text-muted"></i>
                @endif

                <span class="{{ $isMainAnalysis ? 'fw-bold' : '' }}">
                    {{ $analyse['designation'] }}
                    @if($analyse['abr'])
                        <span class="badge {{ $isMainAnalysis ? 'bg-primary' : 'bg-secondary' }} ms-2">
                            {{ $analyse['abr'] }}
                        </span>
                    @endif
                </span>

                @if($isSpecialGroup)
                    <small class="text-muted ms-2">(Groupe avec analyses complémentaires)</small>
                @endif

                <span class="float-end">
                    {{ number_format($analyse['prix'], 2) }} Ariary
                </span>
            </button>
        @endforeach
    </div>
    @endif

    {{-- Analyses sélectionnées --}}
    <div class="mb-3" wire:key="selected-analyses">
        <h5>
            Analyses sélectionnées
            <span class="badge bg-primary">{{ $this->selectedAnalysesCount }}</span>
        </h5>
        <div class="list-group">
            @foreach($selectedAnalyses as $analyseId)
                @php
                    $analyse = collect($analyses)->firstWhere('id', $analyseId);
                    $isSpecialGroup = in_array($analyse['abr'], ['HB', 'HSTASE']);
                @endphp

                <div class="list-group-item @if($isSpecialGroup) border-start border-1 border-primary bg-light @endif">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @if($isSpecialGroup)
                                <span class="fw-bold me-2">{{ $loop->iteration }} -</span>
                                <span class="text-primary fw-medium">
                                    {{ $analyse['designation'] }}
                                    @if($analyse['abr'])
                                        <span class="badge bg-primary ms-2">{{ $analyse['abr'] }}</span>
                                    @endif
                                </span>
                            @else
                                <span class="@if($analyse['level'] === 'PARENT' || empty($analyse['parent_code'])) fw-bold @else ms-4 @endif">
                                    @if($analyse['level'] === 'NORMAL' && !empty($analyse['parent_code']))
                                        <i class="fas fa-arrow-right me-2"></i>
                                    @else
                                        {{ $loop->iteration }} -
                                    @endif
                                    {{ $analyse['designation'] }}
                                    @if($analyse['abr'])
                                        <span class="badge @if($analyse['level'] === 'PARENT' || empty($analyse['parent_code'])) bg-primary @else bg-secondary @endif ms-2">
                                            {{ $analyse['abr'] }}
                                        </span>
                                    @endif
                                </span>
                            @endif
                        </div>
                        <div>
                            {{-- N'afficher le prix que si ce n'est pas HB ou HEMOSTASE --}}
                            @unless($isSpecialGroup)
                                <span class="me-3">{{ number_format($analyse['prix'], 2) }} Ariary</span>
                            @endunless
                            <button type="button" class="btn btn-outline-danger btn-sm" wire:click="removeAnalyse({{ $analyseId }})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    @if($isSpecialGroup)
                        <div class="mt-2 small text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Ce groupe inclut plusieurs analyses complémentaires
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Section Prélèvements --}}
    @include('livewire.secretaire.steps.prelevements')

    {{-- Récapitulatif des prix --}}
    <div class="card mt-4" wire:key="recap-section">
        <div class="card-body">
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                    <span>Sous-total Analyses</span>
                    <strong>{{ number_format($totalPrice - $totalPrelevementsPrice - ($patient_type === 'URGENCE-NUIT' ? 20000 : ($patient_type === 'URGENCE-JOUR' ? 15000 : 0)), 2) }} Ar</strong>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                    <span>Sous-total Prélèvements</span>
                    <span>{{ number_format($totalPrelevementsPrice, 2) }} Ar</span>
                </div>

                @if($patient_type === 'URGENCE-NUIT' || $patient_type === 'URGENCE-JOUR')
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span>Frais d'urgence ({{ $patient_type === 'URGENCE-NUIT' ? 'Nuit' : 'Jour' }})</span>
                        <span class="text-danger">
                            {{ number_format($patient_type === 'URGENCE-NUIT' ? 20000 : 15000, 2) }} Ar
                        </span>
                    </div>
                @endif

                @if($remise > 0)
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span>Remise ({{ $remise }}%)</span>
                        <span class="text-success">
                            -{{ number_format($totalPrice * ($remise / 100), 2) }} Ar
                        </span>
                    </div>
                @endif

                <div class="list-group-item d-flex justify-content-between align-items-center py-3 bg-light">
                    <span class="h5 mb-0">Total Général</span>
                    <strong class="h5 mb-0 text-primary">{{ number_format($totalPrice, 2) }} Ar</strong>
                </div>
            </div>
        </div>
    </div>

    @error('selectedAnalyses')
        <div class="alert alert-danger mt-3">{{ $message }}</div>
    @enderror
</div>
