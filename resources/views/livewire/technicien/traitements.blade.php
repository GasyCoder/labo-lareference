{{-- <div>
    <h2>Traitements des analyses</h2>

    <div class="mb-3">
        <input type="text" wire:model.live="search" class="form-control" placeholder="Rechercher une prescription, un patient ou une analyse...">
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Patient</th>
                <th>Analyses</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($prescriptions as $prescription)
                <tr>
                    <td>{{ $prescription->id }}</td>
                    <td>{{ $prescription->patient->nom }} {{ $prescription->patient->prenom }}</td>
                    <td>
                        {{ $prescription->analyses->pluck('abr')->implode(', ') }}
                    </td>
                    <td>{{ $prescription->created_at->format('Y-m-d H:i:s') }}</td>
                    <td>
                        <a href="{{ route('technicien.traitement.show', $prescription) }}" class="btn btn-primary">
                            Ouvrir
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $prescriptions->links() }}
</div> --}}
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
                            <!-- Onglets -->
                            <ul class="nav nav-tabs mb-4" role="tablist">
                                <li class="nav-item" role="analyse">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#actifs" type="button" role="tab" aria-selected="true">
                                        <i class="fas fa-list-ul me-2"></i>Analyses En enttente
                                        <span class="badge bg-warning rounded-pill ms-1">{{$analyseEntentes->count()}}</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#termine" type="button" role="tab" aria-selected="false">
                                        <i class="fas fa-check-circle me-2"></i>Analyse Terminé
                                        <span class="badge bg-success rounded-pill ms-1">{{$analyseTermines->count()}}</span>
                                    </button>
                                </li>
                            </ul>
                            <!-- Contenu des onglets -->
                            <div class="tab-content">
                                <!-- Liste Active -->
                                <div class="tab-pane fade show active" id="actifs" role="tabpanel">
                                    @if($analyseEntentes->isNotEmpty())
                                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                                            @foreach($analyseEntentes as $prescription)
                                                <div class="col">
                                                    @include('livewire.technicien.partials.prescription-card', ['prescription' => $prescription, 'isArchived' => false])
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="mt-4">
                                            {{ $analyseEntentes->links() }}
                                        </div>
                                    @else
                                        <div class="alert alert-info" role="alert">
                                            <i class="fas fa-info-circle me-2"></i>Aucune analyse en enttente trouvée.
                                        </div>
                                    @endif
                                </div>

                                <!-- Liste Archivée -->
                                <div class="tab-pane fade" id="termine" role="tabpanel">
                                    @if($analyseTermines->isNotEmpty())
                                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                                            @foreach($analyseTermines as $prescription)
                                                <div class="col">
                                                    @include('livewire.technicien.partials.prescription-card', ['prescription' => $prescription, 'isArchived' => true])
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="mt-4">
                                            {{ $analyseTermines->links() }}
                                        </div>
                                    @else
                                        <div class="alert alert-info" role="alert">
                                            <i class="fas fa-info-circle me-2"></i>Aucune analyse terminé trouvée.
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
