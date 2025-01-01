{{-- livewire.secretaire.add-prescription --}}
<div>
    @push('styles')
        @include('livewire.secretaire.steps.style')
        @livewireStyles
    @endpush

    <section class="container-fluid p-4"
        x-data="{
            isLoading: false,
            init() {
                this.isLoading = false;
                Livewire.hook('message.sent', () => { this.isLoading = true });
                Livewire.hook('message.processed', () => { this.isLoading = false });
            }
        }">
        {{-- En-tête --}}
        <div class="row">
            <div class="col-lg-10 col-md-12 col-12 mx-auto">
                <div class="border-bottom pb-3 mb-3">
                    <h3 class="mb-0 h3 fw-bold">Ajouter un patient et une prescription</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('secretaire.patients.index') }}" wire:navigate>
                                    Liste des prescriptions
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Ajouter</li>
                        </ol>
                    </nav>
                </div>
                {{-- Barre de progression --}}
                <div wire:key="progress-bar">
                    @include('livewire.secretaire.steps.progress-bar')
                </div>

                {{-- Formulaire principal --}}
                <form wire:submit.prevent="nextStep">
                    <div class="card shadow-sm mb-4" wire:key="step-{{ $step }}">
                        <div class="card-body">
                            @if ($step === 1)
                                <div wire:key="patient-info">
                                    @include('livewire.secretaire.steps.patient-info')
                                </div>
                            @elseif ($step === 2)
                                <div wire:key="medical-info">
                                    @include('livewire.secretaire.steps.medical-info')
                                </div>
                            @elseif ($step === 3)
                                <div wire:key="analyses">
                                    @include('livewire.secretaire.steps.analyses')
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Boutons de navigation --}}
                    <div class="d-flex justify-content-center"
                         wire:loading.class="opacity-50"
                         wire:target="nextStep,previousStep">
                        @if ($step > 1)
                            <button type="button"
                                class="btn btn-secondary me-4"
                                wire:click="previousStep"
                                wire:loading.attr="disabled"
                                x-bind:disabled="isLoading">
                                <i class="fas fa-arrow-left me-2"></i>Précédent
                            </button>
                        @endif

                        <button type="submit"
                            class="btn {{ $step < 3 ? 'btn-primary' : 'btn-success' }}"
                            wire:loading.attr="disabled"
                            x-bind:disabled="isLoading">
                            @if ($step < 3)
                                <span>Suivant<i class="fas fa-arrow-right ms-2"></i></span>
                            @else
                                <span>Enregistrer<i class="fas fa-check ms-2"></i></span>
                            @endif
                        </button>
                    </div>

                    {{-- Indicateur de chargement --}}
                    <div x-show="isLoading"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         class="text-center mt-3">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <span class="ms-2">Traitement en cours...</span>
                    </div>
                </form>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            document.addEventListener('livewire:load', function () {
                Livewire.on('stepUpdated', () => {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            });
        </script>
    @endpush
</div>
