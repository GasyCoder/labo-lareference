{{-- resources/views/livewire/technicien/details-prescription.blade.php --}}
<div class="container-fluid py-4">
    <div class="row g-4">
        <!-- Sidebar des analyses -->
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-vial me-2"></i>ANALYSES
                    </h5>
                </div>
                <div class="list-group list-group-flush">
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
                        {{-- @if($selectedAnalyse && $selectedAnalyse->id == $analyse->id)
                            @foreach($analyse->children as $child)
                                <button wire:click="selectAnalyse({{ $child->id }})"
                                        class="list-group-item list-group-item-action ps-4">
                                    <span>{{ $child->designation }}</span>
                                    @if($child->is_bold)
                                        <span class="badge bg-danger rounded-pill">
                                            <i class="fas fa-asterisk"></i>
                                        </span>
                                    @endif
                                </button>
                            @endforeach
                        @endif --}}
                    @endforeach
                </div>

                @if($validation)
                    <div class="card-footer bg-light p-3">
                        <button wire:click="validateAnalyse"
                                class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-2">
                            <i class="fas fa-check"></i>
                            <span>Terminer l'analyse</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="col-md-9">
            @if($showForm && $selectedAnalyse)
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white py-3">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-microscope me-2"></i>
                            {{ strtoupper($selectedAnalyse->designation) }}
                        </h4>
                    </div>

                    <div class="card-body p-4">
                        <!-- Formulaire d'analyse -->
                        <form wire:submit.prevent="saveResult({{ $selectedAnalyse->id }})">
                            @include('livewire.technicien.partials.analyse-recursive', [
                                'analyses' => $selectedAnalyse,
                                'bacteries' => $showBactery,
                                'antibiotics_name' => $antibiotics_name
                            ])

                            <div class="mt-4 border-top pt-4">
                                <h5 class="mb-3">
                                    <i class="fas fa-clipboard-check me-2"></i>
                                    {{ __('Conclusion') }}
                                </h5>
                                <div class="mb-3">
                                    <textarea wire:model="conclusion"
                                             class="form-control"
                                             rows="4"
                                             placeholder="{{ __('Ajouter vos commentaires sur le résultat de l\'analyse') }}"></textarea>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <button type="submit"
                                            class="btn btn-primary d-flex align-items-center gap-2">
                                        <i class="fas fa-save"></i>
                                        <span>{{ __('Enregistrer') }}</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="alert alert-info d-flex align-items-center gap-3">
                    <i class="fas fa-info-circle fs-4"></i>
                    <span>{{ __('Veuillez sélectionner une analyse dans la liste') }}</span>
                </div>
            @endif
        </div>
    </div>
</div>
