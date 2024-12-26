{{-- views/livewire/secretaire/patient-prescription.blade.php --}}
<div class="container-fluid py-4">
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Header -->
                <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="mb-0 h2 fw-bold">Toutes les prescriptions</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">Secrétaire</a></li>
                                <li class="breadcrumb-item active">Prescriptions</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="{{route('secretaire.prescriptions.add')}}" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-2"></i>Nouvelle prescription
                        </a>
                    </div>
                </div>

                <!-- Search -->
                <div class="mb-4">
                    <input type="search" class="form-control" placeholder="Rechercher..." wire:model.live="search">
                </div>

                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#actifs" type="button">
                            <i class="fas fa-list-ul me-2"></i>Actives
                            <span class="badge bg-primary ms-1">{{$activePrescriptions->total()}}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#valide" type="button">
                            <i class="fas fa-check-circle me-2"></i>Validées
                            <span class="badge bg-success ms-1">{{$analyseValides->total()}}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#deleted" type="button">
                            <i class="fas fa-trash me-2"></i>Corbeille
                            <span class="badge bg-danger ms-1">{{$deletedPrescriptions->total()}}</span>
                        </button>
                    </li>
                </ul>

                <!-- Content -->
                <div class="tab-content">
                    @foreach(['actifs' => $activePrescriptions, 'valide' => $analyseValides, 'deleted' => $deletedPrescriptions] as $tab => $prescriptions)
                        <div class="tab-pane fade {{ $tab === 'actifs' ? 'show active' : '' }}" id="{{ $tab }}">
                            @include('livewire.secretaire.partials.prescription-card', ['prescriptions' => $prescriptions])
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</div>

