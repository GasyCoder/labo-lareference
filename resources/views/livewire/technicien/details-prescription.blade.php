{{-- resources/views/livewire/technicien/details-prescription.blade.php --}}
<div class="container-fluid py-4">
    <div class="row">
        <!-- En-tête -->
        <div class="col-lg-12 mb-4">
            <div class="border-bottom pb-3 d-flex justify-content-between align-items-center">
               {{-- info patient --}}
               @include('livewire.forms-input.patient-info', ['patient' => $prescription->patient])
                <div>
                    <a href="{{ route('technicien.traitement.index')}}" class="btn btn-warning">
                        <i class="fas fa-arrow-left me-2"></i>Retour au précédent
                    </a>
                </div>
            </div>
        </div>

        <!-- Liste des analyses coté technicien -->
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-vial me-2"></i>LISTE DES ANALYSES
                    </h5>
                </div>
                <div class="list-group list-group-flush">
                    @foreach($topLevelAnalyses as $analyse)
                        <button wire:click="selectAnalyse({{ $analyse->id }})"
                                wire:loading.attr="disabled"
                                @class([
                                    'list-group-item list-group-item-action d-flex justify-content-between align-items-center',
                                    'active' => $selectedAnalyse && $selectedAnalyse->id == $analyse->id,
                                ])>
                            <div>
                                <i class="fas fa-flask me-2"></i>
                                <span class="fw-medium">{{ strtoupper($analyse->designation) }}</span>
                                <span class="badge bg-secondary ms-2">{{ $analyse->abr }}</span>
                            </div>
                            @if($analyse->is_validated)
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>Terminé
                                </span>
                            @elseif($this->hasRequiredFields($analyse->id))
                                <span class="badge bg-warning">
                                    <i class="fas fa-clock me-1"></i>En attente
                                </span>
                            @endif
                        </button>
                    @endforeach
                </div>

                @if($selectedAnalyse && !$selectedAnalyse->is_validated)
                    <div class="card-footer bg-light p-3">
                        @if($this->hasRequiredFields($selectedAnalyse->id))
                            <button wire:click="validateAnalyse"
                                    wire:loading.attr="disabled"
                                    class="btn btn-success w-100">
                                <span wire:loading.remove>
                                    <i class="fas fa-check-circle me-2"></i>
                                    Terminer l'analyse
                                </span>
                                <span wire:loading>
                                    <i class="fas fa-spinner fa-spin me-2"></i>
                                    Traitement...
                                </span>
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Formulaire de résultats -->
        <div class="col-md-9">
            @if($showForm && $selectedAnalyse)
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white py-3">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-microscope me-2"></i>
                            {{ strtoupper($selectedAnalyse->designation) }}
                        </h4>
                    </div>

                    <div class="card-body p-4">
                        <form wire:submit.prevent="saveResult({{ $selectedAnalyse->id }})">
                            @include('livewire.forms-input.analyse-recursive', [
                                'analyses' => $selectedAnalyse,
                                'bacteries' => $showBactery,
                                'antibiotics_name' => $antibiotics_name
                            ])
                            <x-conclusion-section
                            wire:model="conclusion"
                            />
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

@include('layouts.style-patient-info')

</div>
@include('layouts.scripts')
