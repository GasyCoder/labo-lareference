<div class="container-fluid py-4">
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
            <!-- Page header -->
            <div class="border-bottom pb-3 mb-3 d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between">
                <div class="d-flex flex-column gap-1">
                <h1 class="mb-0 h2 fw-bold">Toutes les prescriptions</h1>
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="#">Sécretaire</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Tous</li>
                    </ol>
                </nav>
                </div>
                <!-- button -->
                <div>
                <a href="{{route('admin.prescriptions.add')}}" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Ajouter une prescription</a>
                <a href="#" class="btn btn-secondary me-2"><i class="fas fa-file-download me-0"></i> Exporter</a>
                </div>
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
                            <!-- Onglets -->
                            <ul class="nav nav-tabs mb-4" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#actifs" type="button" role="tab" aria-selected="true">
                                        <i class="fas fa-list-ul me-2"></i>Prescriptions Actives
                                        <span class="badge bg-success rounded-pill ms-1">{{$activePrescriptions->count()}}</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#archives" type="button" role="tab" aria-selected="false">
                                        <i class="fas fa-trash me-2"></i>Prescriptions Corbeille
                                        <span class="badge bg-danger rounded-pill ms-1">{{$archivedPrescriptions->count()}}</span>
                                    </button>
                                </li>
                            </ul>
                            <!-- Contenu des onglets -->
                            <div class="tab-content">
                                <!-- Liste Active -->
                                <div class="tab-pane fade show active" id="actifs" role="tabpanel">
                                    @if($activePrescriptions->isNotEmpty())
                                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                                            @foreach($activePrescriptions as $prescription)
                                                <div class="col">
                                                    @include('livewire.secretaire.partials.prescription-card', ['prescription' => $prescription, 'isArchived' => false])
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="mt-4">
                                            {{ $activePrescriptions->links() }}
                                        </div>
                                    @else
                                        <div class="alert alert-info" role="alert">
                                            <i class="fas fa-info-circle me-2"></i>Aucune prescription active trouvée.
                                        </div>
                                    @endif
                                </div>

                                <!-- Liste Archivée -->
                                <div class="tab-pane fade" id="archives" role="tabpanel">
                                    @if($archivedPrescriptions->isNotEmpty())
                                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                                            @foreach($archivedPrescriptions as $prescription)
                                                <div class="col">
                                                    @include('livewire.secretaire.partials.prescription-card', ['prescription' => $prescription, 'isArchived' => true])
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="mt-4">
                                            {{ $archivedPrescriptions->links() }}
                                        </div>
                                    @else
                                        <div class="alert alert-info" role="alert">
                                            <i class="fas fa-info-circle me-2"></i>Aucune prescription archivée trouvée.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            </div>
        </div>
    </section>
</div>

