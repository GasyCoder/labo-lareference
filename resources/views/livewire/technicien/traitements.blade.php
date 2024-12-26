<div class="container-fluid py-4">
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
            <!-- Page header -->
            <div class="border-bottom pb-3 mb-3 d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between">
                <div class="d-flex flex-column gap-1">
                <h1 class="mb-0 h2 fw-bold">Toutes les analyses</h1>
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="#">Technicien</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Tous</li>
                    </ol>
                </nav>
                </div>
                <!-- button -->
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0 rounded-lg">
                        <div class="card-body">
                            <!-- Recherche -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <form class="d-flex align-items-center">
                                        <span class="position-absolute ps-3 search-icon">
                                            <i class="fe fe-search"></i>
                                        </span>
                                        <input type="search" class="form-control ps-6" placeholder="Rechercher..."
                                            wire:model.live="search">
                                    </form>
                                </div>
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
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#termine" type="button">
                                        <i class="fas fa-check-circle me-2"></i>Terminés
                                        <span class="badge bg-success ms-1">{{$analyseTermines->total()}}</span>
                                    </button>
                                </li>
                            </ul>
                            <!-- Contenu des onglets -->
                            <div class="tab-content">
                                @foreach(['actifs' => $activePrescriptions, 'termine' => $analyseTermines] as $tab => $prescriptions)
                                    <div class="tab-pane fade {{ $tab === 'actifs' ? 'show active' : '' }}" id="{{ $tab }}">
                                        @include('livewire.technicien.partials.prescription-card', ['prescriptions' => $prescriptions])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            </div>
        </div>
    </section>
</div>
