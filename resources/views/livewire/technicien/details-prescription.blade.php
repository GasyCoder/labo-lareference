<div class="container-fluid py-4">
    <section class="row g-4">
        <!-- Sidebar des analyses -->
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-vial me-2"></i>ANALYSES
                    </h5>
                </div>

                <div class="list-group list-group-flush">
                    <!-- Analyses principales -->
                    @foreach($topLevelAnalyses as $analyse)
                        <button wire:click="selectAnalyse({{ $analyse->id }})"
                                @class([
                                    'list-group-item list-group-item-action d-flex justify-content-between align-items-center',
                                    'active' => $selectedAnalyse && $selectedAnalyse->id == $analyse->id
                                ])>
                            <div>
                                <i class="fas fa-flask me-2"></i>
                                <span class="fw-medium">{{ strtoupper($analyse->designation) }}</span>
                                <span class="badge bg-secondary ms-2">{{ $analyse->abr }}</span>
                            </div>
                        </button>
                    @endforeach

                    <!-- Sous-analyses -->
                    @foreach($childAnalyses as $parentCode => $children)
                        @foreach($children as $analyse)
                            <button wire:click="selectAnalyse({{ $analyse->id }})"
                                    @class([
                                        'list-group-item list-group-item-action d-flex justify-content-between align-items-center ps-4',
                                        'active' => $selectedAnalyse && $selectedAnalyse->id == $analyse->id
                                    ])>
                                <span>{{ $analyse->designation }}</span>
                                @if($analyse->is_bold)
                                    <span class="badge bg-danger rounded-pill">
                                        <i class="fas fa-asterisk"></i>
                                    </span>
                                @endif
                            </button>
                        @endforeach
                    @endforeach
                </div>

                @if($validation)
                    <div class="card-footer bg-light p-3">
                        <button wire:click="validateAnalyse"
                                class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-2">
                            <i class="fas fa-check"></i>
                            <span>Valider l'analyse</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Zone de contenu principal -->
        <div class="col-md-9">
            @if($showForm && $selectedAnalyse)
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">
                                <i class="fas fa-microscope me-2"></i>
                                {{ strtoupper($selectedAnalyse->designation) }}
                            </h4>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        @include('livewire.technicien.partials.analyse-recursive', [
                            'analyses' => $selectedAnalyse,
                            'bacteries' => $showBactery,
                            'antibiotics_name' => $antibiotics_name
                        ])

                        <div class="mt-4 border-top pt-4">
                            <h4 class="mb-3">
                                <i class="fas fa-clipboard-check me-2"></i>
                                {{ __('Conclusion') }}
                            </h4>
                            <div class="mb-3">
                                <textarea wire:model="conclusion"
                                         class="form-control"
                                         rows="4"
                                         placeholder="{{ __('Ajouter ici vos commentaires sur le résultat de l\'analyse') }}"></textarea>
                            </div>

                            <div class="mt-4">
                                <button wire:click="saveResult({{ $selectedAnalyse->id }})"
                                        class="btn btn-primary d-flex align-items-center gap-2">
                                    <i class="fas fa-save"></i>
                                    <span>Enregistrer</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info d-flex align-items-center gap-3 shadow-sm">
                    <i class="fas fa-info-circle fs-4"></i>
                    <span>Veuillez sélectionner une analyse dans la liste</span>
                </div>
            @endif
        </div>
    </section>
</div>
