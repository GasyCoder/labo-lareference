{{-- resources/views/livewire/forms/germe-input.blade.php --}}
<div class="mb-3"
     x-data="germeHandler()"
     x-init="init()">

    {{-- Select pour le choix des bactéries --}}
    <div class="form-group">
        <select wire:model.live="selectedOption"
                class="form-select form-select-lg mb-3"
                multiple
                x-init="$nextTick(() => {
                    if ($wire.selectedOption && $wire.selectedOption.length > 0) {
                        $wire.bacteries($wire.selectedOption[0]);
                    }
                })">
            {{-- Options standards --}}
            <optgroup label="{{ __('Options standards') }}">
                @php
                    $standardOptions = [
                        'non-rechercher' => __('Non recherché'),
                        'en-cours' => __('En cours'),
                        'culture-stérile' => __('Culture stérile'),
                        'absence de germe pathogène' => __('Absence de germe pathogène')
                    ];
                @endphp

                @foreach($standardOptions as $value => $label)
                    <option value="{{ $value }}"
                            @if(in_array($value, (array)$selectedOption)) selected @endif>
                        {{ $label }}
                    </option>
                @endforeach
            </optgroup>

            {{-- Liste des bactéries par famille --}}
            @foreach($bacteries as $bactery)
                @if(!empty($bactery->bacteries))
                    <optgroup label="{{ $bactery->name }}">
                        @foreach((array)$bactery->bacteries as $bacteri)
                            <option value="{{ $bacteri }}"
                                    @if(in_array($bacteri, (array)$selectedOption)) selected @endif
                                    wire:click="bacteries('{{ $bacteri }}')">
                                {{ $bacteri }}
                            </option>
                        @endforeach
                    </optgroup>
                @endif
            @endforeach

            {{-- Option "Autre" --}}
            <optgroup label="{{ __('Autres options') }}">
                <option value="autre" @if(in_array('autre', (array)$selectedOption)) selected @endif>
                    {{ __('Autre') }}
                </option>
            </optgroup>
        </select>
    </div>

    {{-- Input pour "Autre" --}}
    @if($showOtherInput)
        <div class="form-group mt-3">
            <label for="other-bacteria">{{ __('Précisez la bactérie') }}</label>
            <input type="text"
                   id="other-bacteria"
                   class="form-control"
                   wire:model="otherBacteriaValue"
                   placeholder="Entrez une bactérie non listée">
        </div>
    @endif

    {{-- Section Antibiogrammes --}}
    @if($showAntibiotics && $antibiotics_name && $currentBacteria && !in_array($currentBacteria, ['culture-sterile', 'non-rechercher', 'en-cours', 'absence de germe pathogène', 'autre']))
        <div class="mt-4 antibiogram-section">
            {{-- En-tête --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 d-flex align-items-center">
                        <i class="fas fa-bacteria text-primary me-2"></i>
                        <span>{{ __('ANTIBIOGRAMMES') }}</span>
                    </h6>
                    <small class="text-muted">
                        {{ __('Sélectionnez la sensibilité pour chaque antibiotique') }}
                    </small>
                </div>

                {{-- Bouton réinitialisation --}}
                <div class="text-end">
                    <button type="button"
                            class="btn btn-outline-danger"
                            wire:click="resetAllAntibiotics"
                            wire:loading.attr="disabled"
                            title="{{ __('Réinitialiser tous les choix') }}">
                        <i class="fas fa-refresh me-2"></i>
                        {{ __('Réinitialiser tout') }}
                    </button>
                </div>
            </div>

            {{-- Info bactérie --}}
            <div class="card bg-light border-0 mb-4">
                <div class="card-body py-2">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-bug text-primary me-2"></i>
                        <span>{{ __('Bactérie :') }}</span>
                        <strong class="ms-2">{{ $currentBacteria }}</strong>
                    </div>
                </div>
            </div>

            {{-- Tableau des antibiotiques --}}
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-bottom-0 text-start" style="width: 40%;">
                                        {{ __('Antibiotique') }}
                                    </th>
                                    <th class="border-bottom-0 text-center" style="width: 60%;">
                                        {{ __('Sensibilité') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($antibiotics_name as $antibiotic)
                                    @php
                                        $displayAntibiotic = $antibiotic;
                                        if (strpos($antibiotic, 'C1G (Cefalotine') !== false) {
                                            $displayAntibiotic = 'C1G (Cefalotine)';
                                        }
                                    @endphp
                                    <tr wire:key="antibiotic-{{ $loop->index }}">
                                        <td class="border-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>{{ $antibiotic }}</span>
                                                @if(isset($selectedBacteriaResults[$currentBacteria]['antibiotics'][$antibiotic]))
                                                    <button type="button"
                                                            class="btn btn-link btn-sm text-muted p-0 ms-2"
                                                            wire:click="resetAntibiotic('{{ $antibiotic }}')"
                                                            wire:loading.attr="disabled"
                                                            title="{{ __('Réinitialiser') }}">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="border-0">
                                            <div class="btn-group w-100" role="group">
                                                @php
                                                    $sensitivities = [
                                                        'resistant' => ['label' => __('Résistant'), 'color' => 'danger'],
                                                        'intermediaire' => ['label' => __('Intermédiaire'), 'color' => 'warning'],
                                                        'sensible' => ['label' => __('Sensible'), 'color' => 'success']
                                                    ];
                                                @endphp

                                                @foreach($sensitivities as $val => $info)
                                                    <input type="radio"
                                                           class="btn-check"
                                                           name="antibiotic_{{ $loop->parent->index }}"
                                                           id="antibiotic_{{ $loop->parent->index }}_{{ $val }}"
                                                           wire:model.live="selectedBacteriaResults.{{ $currentBacteria }}.antibiotics.{{ $antibiotic }}"
                                                           value="{{ $val }}">
                                                    <label class="btn btn-outline-{{ $info['color'] }} btn-sm"
                                                           for="antibiotic_{{ $loop->parent->index }}_{{ $val }}">
                                                        {{ $info['label'] }}
                                                    </label>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function germeHandler() {
    return {
        init() {
            // État initial
            this.initializeState();

            // Écouteurs d'événements
            this.setupEventListeners();
        },

        initializeState() {
            if (this.$wire.showAntibiotics) {
                this.$wire.dispatch('resultsLoaded', {
                    selectedBacteriaResults: this.$wire.selectedBacteriaResults
                });
            }
        },

        setupEventListeners() {
            Livewire.on('resultsLoaded', data => {
                if (data.selectedBacteriaResults) {
                    this.updateAntibiogramSelections(data.selectedBacteriaResults);
                }
            });
        },

        updateAntibiogramSelections(bacteriaResults) {
            setTimeout(() => {
                Object.entries(bacteriaResults).forEach(([bacteria, results]) => {
                    if (results.antibiotics) {
                        Object.entries(results.antibiotics).forEach(([antibiotic, sensitivity]) => {
                            this.updateRadioSelection(bacteria, antibiotic, sensitivity);
                        });
                    }
                });
            }, 100);
        },

        updateRadioSelection(bacteria, antibiotic, sensitivity) {
            const selector = `input[type="radio"][wire\\:model\\.live="selectedBacteriaResults.${bacteria}.antibiotics.${antibiotic}"][value="${sensitivity}"]`;
            const input = document.querySelector(selector);
            if (input) {
                input.checked = true;
            }
        }
    }
}
</script>
@endpush

<style>
/* Variables CSS personnalisées */
:root {
    --transition-speed: 0.2s;
    --opacity-default: 0.7;
    --opacity-hover: 1;
    --font-weight-selected: 600;
}

/* Styles des boutons radio */
.btn-check:checked + .btn-outline-danger,
.btn-check:checked + .btn-outline-warning,
.btn-check:checked + .btn-outline-success {
    opacity: var(--opacity-hover);
    font-weight: var(--font-weight-selected);
}

/* Styles des boutons d'options */
.btn-outline-danger,
.btn-outline-warning,
.btn-outline-success {
    opacity: var(--opacity-default);
    transition: all var(--transition-speed);
}

.btn-outline-danger:hover,
.btn-outline-warning:hover,
.btn-outline-success:hover {
    opacity: var(--opacity-hover);
}

/* Styles du tableau */
.table > :not(caption) > * > * {
    padding: 1rem;
}

/* Styles responsifs */
@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
        width: 100%;
    }

    .btn-group .btn {
        width: 100%;
        margin-bottom: 0.25rem;
        border-radius: 0.25rem !important;
    }
}

/* Animations */
.antibiogram-section {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
