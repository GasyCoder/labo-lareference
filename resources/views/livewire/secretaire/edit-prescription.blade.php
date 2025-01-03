<div>
    @push('styles')
        @include('livewire.secretaire.steps.style')
    @endpush
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-10 col-md-12 col-12 mx-auto">
                <div class="border-bottom pb-3 mb-3">
                    <h1 class="mb-0 h2 fw-bold">Modifier la prescription</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('secretaire.patients.index') }}">Liste des prescriptions</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Modifier</li>
                        </ol>
                    </nav>
                </div>

                <!-- Barre de progression -->
                @include('livewire.secretaire.steps.progress-bar')

                <form wire:submit.prevent="nextStep">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            @if ($step === 1)
                                @include('livewire.secretaire.steps.patient-info')
                            @elseif ($step === 2)
                                @include('livewire.secretaire.steps.medical-info')
                            @elseif ($step === 3)
                                @include('livewire.secretaire.steps.analyses')
                            @endif
                        </div>
                    </div>

                    <div class="d-flex justify-content-center">
                        @if ($step > 1)
                            <button type="button" class="btn btn-secondary me-4" wire:click="previousStep">
                                <i class="fas fa-arrow-left me-2"></i>Précédent
                            </button>
                        @else
                            <div></div>
                        @endif

                        <button type="submit" class="btn {{ $step < 3 ? 'btn-primary' : 'btn-success' }}">
                            @if ($step < 3)
                                Suivant<i class="fas fa-arrow-right ms-2"></i>
                            @else
                                Mettre à jour<i class="fas fa-check ms-2"></i>
                            @endif
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
