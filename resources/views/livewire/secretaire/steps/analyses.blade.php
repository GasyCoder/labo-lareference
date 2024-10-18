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
                {{ $analyse['abr'] }} - {{ $analyse['designation'] }} ({{ number_format($analyse['prix'], 2) }} €)
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
                {{ number_format($analyse['prix'], 2) }} €
                <button type="button" class="btn btn-sm btn-danger ms-2" wire:click="removeAnalyse({{ $analyseId }})" wire:loading.attr="disabled">
                    <i class="fas fa-trash"></i>
                </button>
            </span>
        </li>
    @endforeach
    </ul>
</div>

<div class="alert alert-info">
    <strong>Total : {{ number_format($totalPrice, 2) }} €</strong>
</div>

@error('selectedAnalyses') <div class="text-danger">{{ $message }}</div> @enderror
