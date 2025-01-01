<div class="mb-4">
    <button class="btn btn-outline-primary d-flex align-items-center gap-2 mb-3"
            wire:click="$toggle('showPrelevements')"
            type="button">
        <i class="fas fa-{{ $showPrelevements ? 'minus' : 'plus' }}"></i>
        Options de prélèvement
        @if(count($selectedPrelevements) > 0)
            <span class="badge bg-danger">{{ count($selectedPrelevements) }}</span>
        @endif
    </button>

    @if($showPrelevements)
        <div class="card border">
            <div class="card-body">
                <!-- Groupes de prélèvements -->
                <div class="row g-4">
                    <!-- Prélèvements standards -->
                    <div class="col-12">
                        <h6 class="text-muted mb-3">Prélèvements standards</h6>
                        <div class="row g-3">
                            @foreach($prelevements as $prelevement)
                                @if(!str_contains(strtolower($prelevement['nom']), 'tube') && !str_contains(strtolower($prelevement['nom']), 'flacon'))
                                    <div class="col-md-4" wire:key="prelevement-{{ $prelevement['id'] }}">
                                        @include('livewire.secretaire.partials.prelevement-item', ['prelevement' => $prelevement])
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Tubes et Flacons -->
                    <div class="col-12">
                        <h6 class="text-muted mb-3">Tubes et Flacons</h6>
                        <div class="row g-3">
                            @foreach($prelevements as $prelevement)
                                @if(str_contains(strtolower($prelevement['nom']), 'tube') || str_contains(strtolower($prelevement['nom']), 'flacon'))
                                    <div class="col-md-4" wire:key="prelevement-{{ $prelevement['id'] }}">
                                        <div class="d-flex align-items-center p-3 border rounded hover:shadow-sm transition-all
                                            @if($this->isPrelevementSelected($prelevement['id']))
                                                border-primary bg-light
                                            @endif">
                                            <div class="form-check flex-grow-1">
                                                <input type="checkbox"
                                                       class="form-check-input"
                                                       id="prelevement-{{ $prelevement['id'] }}"
                                                       wire:model.live="selectedPrelevements"
                                                       value="{{ $prelevement['id'] }}"
                                                       wire:change="calculateTotal">
                                                <label class="form-check-label w-100" for="prelevement-{{ $prelevement['id'] }}">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span>{{ $prelevement['nom'] }}</span>
                                                        <span class="badge bg-primary">
                                                            {{ number_format($prelevement['prix'], 2) }} Ar
                                                        </span>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>

                                        @if($this->isPrelevementSelected($prelevement['id']) && $this->hasQuantity($prelevement))
                                            <div class="mt-2" wire:transition>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">Qté</span>
                                                    <input type="number"
                                                           class="form-control form-control-sm"
                                                           wire:model.live="prelevementQuantities.{{ $prelevement['id'] }}"
                                                           min="1"
                                                           placeholder="Quantité">
                                                </div>
                                                <div class="small text-muted mt-1">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Total : <strong>{{ number_format($this->getPrelevementPrice($prelevement['id']), 2) }} Ar</strong>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
