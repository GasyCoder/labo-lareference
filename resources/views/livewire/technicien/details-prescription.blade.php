{{-- resources/views/livewire/technicien/details-prescription.blade.php --}}
<div class="container-fluid py-4">
    <section class="container-fluid p-4">
    <div class="row">
        <!-- Sidebar avec toutes les analyses -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Toutes les analyses</h5>
                </div>

                <div class="list-group list-group-flush">
                    {{-- Analyses principales --}}
                    @foreach($topLevelAnalyses as $analyse)
                        <button
                            wire:click="selectAnalyse({{ $analyse->id }})"
                            class="list-group-item list-group-item-action
                                {{ $selectedAnalyse && $selectedAnalyse->id == $analyse->id ? 'active' : '' }}">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $analyse->designation. ' (' .$analyse->abr. ') ' }}</h6>
                                @if($analyse->is_bold)
                                    <span class="">*</span>
                                @endif
                            </div>
                        </button>
                    @endforeach

                    {{-- Sous-analyses (normal ou child ici) --}}
                    @foreach($childAnalyses as $parentCode => $children)
                        {{-- <div class="list-group-item list-group-item-secondary">
                            {{ optional(app\Models\Analyse::find($parentCode))->designation }}
                        </div> --}}
                        @foreach($children as $analyse)
                        <button
                            wire:click="selectAnalyse({{ $analyse->id }})"
                            class="list-group-item list-group-item-action ps-4
                                {{ $selectedAnalyse && $selectedAnalyse->id == $analyse->id ? 'active' : '' }}">
                            <div class="d-flex w-100 justify-content-between">
                                <span>{{ $analyse->designation }}</span>
                                @if($analyse->is_bold)
                                    <span class="badge bg-danger">*</span>
                                @endif
                            </div>
                        </button>
                        @endforeach
                    @endforeach
                </div>

                @if($validation)
                    <div class="card-footer">
                        <button
                            wire:click="validateAnalyse"
                            class="btn btn-success w-100"
                        >
                            <i class="fas fa-check me-2"></i> Valider
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="col-md-9">
            @if($showForm && $selectedAnalyse)
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">{{ $selectedAnalyse->designation }}</h4>
                    </div>
                    <div class="card-body">
                        @include('livewire.technicien.partials.analyse-recursive', [
                            'analyses' => $selectedAnalyse,
                            'bacteries' => $showBactery,
                            'antibiotics_name' => $antibiotics_name
                        ])

                        <div class="mt-4">
                            <button
                                wire:click="saveResult({{ $selectedAnalyse->id }})"
                                class="btn btn-primary"
                            >
                                <i class="fas fa-save me-2"></i> Enregistrer
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Veuillez sélectionner une analyse dans la liste
                </div>
            @endif
        </div>
    </div>
    </section>
</div>
