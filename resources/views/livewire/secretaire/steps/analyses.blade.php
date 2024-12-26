<h4 class="card-title mb-4">Sélectionner les analyses</h4>

<div class="mb-3">
    <label for="analyseSearch" class="form-label">Rechercher une analyse</label>
    <div class="input-group">
        <input type="text" wire:model.live="analyseSearch" id="analyseSearch" class="form-control" placeholder="Tapez pour rechercher...">
        <button class="btn btn-outline-secondary" type="button" wire:click="$set('analyseSearch', '')">Effacer</button>
    </div>
</div>

@if(!empty($analyseSuggestions))
    <div class="list-group mb-3">
        @foreach($analyseSuggestions as $analyse)
            <button type="button" class="list-group-item list-group-item-action" wire:click="addAnalyse({{ $analyse['id'] }})">
                {{ $analyse['abr'] }} - {{ $analyse['designation'] }} ({{ number_format($analyse['prix'], 2) }} Ariary)
            </button>
        @endforeach
    </div>
@endif

<div class="mb-3">
    <h5>Analyses (<span class="text-danger">{{ $this->selectedAnalysesCount }}</span>) sélectionnées :</h5>
    <ul class="list-group">
    @foreach($selectedAnalyses as $key => $analyseId)
        @php $analyse = collect($analyses)->firstWhere('id', $analyseId); @endphp
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <span class="fw-bold me-2">{{ $loop->iteration }} -</span>
                {{ $analyse['abr'] }} - {{ $analyse['designation'] }}
            </div>
            <span>
                {{ number_format($analyse['prix'], 2) }} Ariary
                <button type="button" class="btn btn-sm btn-danger ms-2" wire:click="removeAnalyse({{ $analyseId }})" wire:loading.attr="disabled">
                    <i class="fas fa-trash"></i>
                </button>
            </span>
        </li>
    @endforeach
    </ul>
</div>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Options de prélèvement</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($prelevements as $prelevement)
            <div class="col-md-6">
                <div class="border rounded p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="form-check">
                            <input type="checkbox"
                                class="form-check-input"
                                id="prelevement-{{ $prelevement['id'] }}"
                                wire:model.live="selectedPrelevements"
                                value="{{ $prelevement['id'] }}">
                            <label class="form-check-label" for="prelevement-{{ $prelevement['id'] }}">
                                {{ $prelevement['nom'] }}
                            </label>
                        </div>
                        <span class="badge bg-primary">{{ number_format($this->getPrelevementPrice($prelevement['id']), 2) }} Ariary</span>
                    </div>
                    @if($this->isPrelevementSelected($prelevement['id']))
                        <div class="mt-2">
                            <label class="form-label">Quantité</label>
                            <input type="number"
                                class="form-control"
                                min="1"
                                wire:model.live="prelevementQuantities.{{ $prelevement['id'] }}"
                                placeholder="Nombre">
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
        </div>
    </div>
</div>

<div class="alert alert-info">
    <div class="d-flex justify-content-between mb-2">
        <span>Sous-total Analyses:</span>
        <strong>{{ number_format($totalPrice - $totalPrelevementsPrice, 2) }} Ariary</strong>
    </div>
    <div class="d-flex justify-content-between mb-2">
        <span>Sous-total Prélèvements:</span>
        <strong>{{ number_format($totalPrelevementsPrice, 2) }} Ariary</strong>
    </div>
    <hr class="my-2">
    <div class="d-flex justify-content-between">
        <span>Total Général:</span>
        <strong>{{ number_format($totalPrice, 2) }} Ariary</strong>
    </div>
</div>

@error('selectedAnalyses') <div class="text-danger">{{ $message }}</div> @enderror
