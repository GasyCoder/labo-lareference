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
           <button type="button" class="list-group-item list-group-item-action" wire:click="addAnalyse({{ $analyse['id'] }})">
               {{ $analyse['designation'] }} {{ $analyse['level'] === 'PARENT' ? '(Groupe)' : '' }}
               ({{ number_format($analyse['prix'], 2) }} Ariary)
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
            @php
                $currentGroup = null;
                $totalGroupPrice = 0;
            @endphp

            @foreach($selectedAnalyses as $analyseId)
                @php
                    $analyse = collect($analyses)->firstWhere('id', $analyseId);
                    $isSpecialGroup = in_array($analyse['abr'], ['HB', 'HSTASE']);

                    if ($isSpecialGroup) {
                        $currentGroup = $analyse;
                        $totalGroupPrice = $analyse['prix'];
                    } elseif($currentGroup && $analyse['parent_code'] === $currentGroup['code']) {
                        $totalGroupPrice += $analyse['prix'];
                    }
                @endphp

                <div class="list-group-item @if($isSpecialGroup) border-start border-1 border-primary bg-light @endif">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @if($analyse['level'] === 'PARENT' || $isSpecialGroup)
                                {{-- Pour les analyses parent et groupes spéciaux --}}
                                <span class="fw-bold me-2">{{ $loop->iteration }} -</span>
                                <span class="@if($isSpecialGroup) text-primary @endif fw-medium">
                                    {{ $analyse['designation'] }}
                                    @if($analyse['abr'])
                                        <span class="badge bg-info ms-2">{{ $analyse['abr'] }}</span>
                                    @endif
                                </span>
                            @else
                                {{-- Pour les enfants des groupes --}}
                                <span class="ms-4 text-muted">
                                    <i class="fas fa-arrow-right me-2"></i>
                                    {{ $analyse['designation'] }}
                                    @if($analyse['abr'])
                                        ({{ $analyse['abr'] }})
                                    @endif
                                </span>
                            @endif
                        </div>
                        <div>
                            <span class="me-3">{{ number_format($analyse['prix'], 2) }} Ariary</span>
                            <button type="button" class="btn btn-outline-danger btn-sm" wire:click="removeAnalyse({{ $analyseId }})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    {{-- Afficher le message pour les groupes spéciaux --}}
                    @if($isSpecialGroup)
                        <div class="mt-2 small text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Ce groupe inclut plusieurs analyses complémentaires
                        </div>
                    @endif
                </div>

                @if($loop->last && $currentGroup)
                    <div class="list-group-item bg-light text-end">
                        Total du groupe {{ $currentGroup['designation'] }}:
                        <strong>{{ number_format($totalGroupPrice, 2) }} Ariary</strong>
                    </div>
                @endif
            @endforeach
        </div>

    </div>

    {{-- Section Prélèvements --}}
    @include('livewire.secretaire.steps.prelevements')

    {{-- Récapitulatif --}}
    <div class="card mt-4" wire:key="recap-section">
        <div class="card-body">
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                    <span>Sous-total Analyses</span>
                    <strong>{{ number_format($totalPrice - $totalPrelevementsPrice, 2) }} Ar</strong>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                    <span>Sous-total Prélèvements</span>
                    <span>{{ number_format($totalPrelevementsPrice, 2) }} Ar</span>
                </div>
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
