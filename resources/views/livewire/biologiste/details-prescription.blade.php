{{-- resources/views/livewire/technicien/details-prescription.blade.php --}}
<div class="container-fluid py-4">
    <div class="row">
        <!-- En-tête avec information patient -->
        <div class="col-lg-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        {{-- Informations du patient --}}
                        <div class="col-lg-8">
                            @include('livewire.forms-input.patient-info', ['patient' => $prescription->patient])
                        </div>
                        <!-- Actions -->
                        <div class="col-lg-4">
                            <div class="d-flex gap-2 justify-content-end align-items-center">
                                <button wire:click="redoPrescription({{ $prescription->id }})"
                                    wire:loading.attr="disabled"
                                    class="btn btn-warning me-2"
                                    style="background: white; color: #FFA500; border-color: #FFA500;">
                                    <div class="d-flex align-items-center">
                                        <div wire:loading wire:target="redoPrescription({{ $prescription->id }})">
                                            <i class="fas fa-spinner fa-spin me-2"></i>
                                        </div>
                                        <div wire:loading.remove wire:target="redoPrescription({{ $prescription->id }})">
                                            <i class="fa fa-refresh me-1"></i>
                                        </div>
                                        <span>À refaire</span>
                                    </div>
                                </button>
                                <x-preview-button wire:ignore.self/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des analyses -->
        <div class="col-md-3 mt-3">
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
                                'active-transparent' => $selectedAnalyse && $selectedAnalyse->id == $analyse->id,
                            ])>
                            <div>
                                <span @class([
                                    'badge',
                                    'bg-secondary' => !$selectedAnalyse || $selectedAnalyse->id != $analyse->id,
                                    'bg-success text-white' => $selectedAnalyse && $selectedAnalyse->id == $analyse->id
                                ])>
                                {{ $analyse->abr }}</span>
                            </div>

                            @php
                                $resultat = \App\Models\Resultat::where([
                                    'prescription_id' => $prescription->id,
                                    'analyse_id' => $analyse->id
                                ])->first();
                            @endphp

                            @if($resultat && $resultat->validated_by)
                                <span class="badge bg-success-soft text-success">
                                    <i class="fas fa-check-double me-1"></i>Validé
                                </span>
                            @else
                                <span class="badge bg-warning-soft text-primary">
                                    <i class="fas fa-check me-1"></i>Terminé
                                </sup>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Nouveau bouton de validation globale -->
            @php
                $hasUnvalidatedResults = $prescription->resultats()
                    ->whereNull('validated_by')
                    ->exists();

                $allAnalysesCompleted = $prescription->analyses()
                    ->wherePivot('status', 'TERMINE')
                    ->count() === $prescription->analyses()->count();
            @endphp

            @if($hasUnvalidatedResults)
                <div class="mt-3">
                    <button wire:click="validateAnalyse"
                            wire:loading.attr="disabled"
                            class="btn btn-success w-100">
                        <span wire:loading.remove>
                            <i class="fas fa-check-circle me-2"></i>
                            VALIDER
                        </span>
                        <span wire:loading>
                            <i class="fas fa-spinner fa-spin me-2"></i>
                            Validation en cours...
                        </span>
                    </button>
                </div>
            @endif

        </div>

        <!-- Formulaire de résultats -->
        <div class="col-md-9 mt-3">
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

    <style>
        .active-transparent {
            background-color: rgba(48, 238, 156, 0.405); /* Bleu clair transparent */
            border-color: rgba(58, 210, 96, 0.629); /* Bordure plus opaque */
            color: #000; /* Couleur du texte */
        }

        .active-transparent:hover {
            background-color: rgba(100, 149, 237, 0.3); /* Accentuation au survol */
        }

    </style>
</div>
@include('layouts.scripts')


