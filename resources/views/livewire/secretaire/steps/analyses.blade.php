<h4 class="card-title mb-4">Sélectionner les analyses</h4>

<div class="mb-3">
    <label for="analyseSearch" class="form-label">
        <i class="fas fa-flask me-2 text-primary"></i>
        Rechercher des analyses
    </label>
    <div class="input-group">
        <input type="text" wire:model.live="analyseSearch" id="analyseSearch" class="form-control" placeholder="Tapez pour rechercher...">
        <button class="btn btn-outline-secondary" type="button" wire:click="$set('analyseSearch', '')">Effacer</button>
    </div>
 </div>

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


<div class="mb-3">
    <h5>Analyses (<span class="text-danger">{{ $this->selectedAnalysesCount }}</span>) sélectionnées :</h5>
    <div class="list-group">
        @php
            $currentGroup = null;
            $totalGroupPrice = 0;
        @endphp

        @foreach($selectedAnalyses as $analyseId)
            @php
                $analyse = collect($analyses)->firstWhere('id', $analyseId);
                $isSpecialGroup = in_array($analyse['designation'], ['HEPATITE B', 'HEMOSTASE']);

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
                            {{-- @if($isSpecialGroup)
                                <span class="badge bg-info ms-2">Groupe</span>
                            @endif --}}
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



<div class="mb-4">
    {{-- Button pour afficher/masquer les prélèvements --}}
    <button class="btn btn-outline-dark-primary d-flex align-items-center gap-2 mb-3"
            wire:click="$toggle('showPrelevements')"
            type="button">
        <i class="fas fa-{{ $showPrelevements ? 'minus' : 'plus' }}"></i>
        Options de prélèvement
        @if(count($selectedPrelevements) > 0)
            <span class="badge bg-danger text-white ms-2">{{ count($selectedPrelevements) }}</span>
        @endif
    </button>

    {{-- Section des prélèvements en accordion --}}
    @if($showPrelevements)
        <div class="card border">
            <div class="card-body">
                <div class="row g-3">
                    @foreach($prelevements as $prelevement)
                        <div class="col-md-4">
                            <div class="d-flex align-items-center justify-content-between p-3 border rounded @if($this->isPrelevementSelected($prelevement['id'])) border-primary bg-light @endif">
                                <div class="form-check">
                                    <input type="checkbox"
                                        class="form-check-input"
                                        id="prelevement-{{ $prelevement['id'] }}"
                                        wire:model.live="selectedPrelevements"
                                        value="{{ $prelevement['id'] }}">
                                    <label class="form-check-label" for="prelevement-{{ $prelevement['id'] }}">
                                        {{ $prelevement['nom'] }}
                                        <div class="badge bg-primary">
                                            {{ number_format($this->getPrelevementPrice($prelevement['id']), 2) }} Ar
                                        </div>
                                    </label>
                                </div>
                            </div>
                            @if($this->isPrelevementSelected($prelevement['id']) && $this->hasQuantity($prelevement))
                                <div class="mt-2">
                                    <input type="number"
                                        class="form-control form-control-sm"
                                        min="1"
                                        wire:model.live="prelevementQuantities.{{ $prelevement['id'] }}"
                                        placeholder="Quantité">
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Récapitulatif des totaux --}}
    <div class="card-footer bg-light">
        <div class="list-group list-group-flush">
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <span>Sous-total Analyses</span>
                <strong>{{ number_format($totalPrice - $totalPrelevementsPrice, 2) }} Ar</strong>
            </div>
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <span>Sous-total Prélèvements</span>
                <strong>{{ number_format($totalPrelevementsPrice, 2) }} Ar</strong>
            </div>
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <span>Total Général</span>
                <strong>{{ number_format($totalPrice, 2) }} Ar</strong>
            </div>
        </div>
    </div>
</div>

@error('selectedAnalyses') <div class="text-danger">{{ $message }}</div> @enderror
