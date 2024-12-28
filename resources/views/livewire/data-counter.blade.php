<div>
    <div class="row gy-4 mb-4">
        <!-- Card Analyses -->
        <div class="col-xl-3 col-lg-6 col-md-12 col-12">
            <div class="card h-100">
                <div class="card-body d-flex flex-column gap-3">
                    <!-- Analyses Header -->
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="fs-6 text-uppercase fw-semibold">Analyses</span>
                        </div>
                        <div>
                            <i class="fa fa-flask fs-3 text-primary"></i>
                        </div>
                    </div>
                    <!-- Analyses Stats -->
                    <div class="d-flex flex-column gap-2">
                        <h2 class="fw-bold mb-0 text-primary">{{ $analysesStats['validees'] }}</h2>
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex gap-2">
                                <span class="badge bg-warning d-flex align-items-center">
                                    <i class="fe fe-clock me-1"></i>
                                    {{ $analysesStats['en_attente'] }} en attente
                                </span>
                                <span class="badge bg-success d-flex align-items-center">
                                    <i class="fe fe-check-circle me-1"></i>
                                    {{ $analysesStats['termine'] }} terminé
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Patients -->
        <div class="col-xl-3 col-lg-6 col-md-12 col-12">
            <div class="card h-100">
                <div class="card-body d-flex flex-column gap-3">
                    <!-- Patients Header -->
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="fs-6 text-uppercase fw-semibold">Patients</span>
                        </div>
                        <div>
                            <i class="fa fa-users fs-3 text-primary"></i>
                        </div>
                    </div>
                    <!-- Patients Stats -->
                    <div class="d-flex flex-column gap-2">
                        <h2 class="fw-bold mb-0">{{ $totalPatients }}</h2>
                        <div class="d-flex align-items-center gap-2">
                            @if($newPatients > 0)
                                <div class="badge bg-success d-flex align-items-center">
                                    <i class="fe fe-user-plus me-1"></i>
                                    {{ $newPatients }} nouveau(x)
                                </div>
                            @endif
                            <small class="text-muted">(30 derniers jours)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Biologistes -->
        <div class="col-xl-3 col-lg-6 col-md-12 col-12">
            <div class="card h-100">
                <div class="card-body d-flex flex-column gap-3">
                    <!-- Biologistes Header -->
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="fs-6 text-uppercase fw-semibold">Biologistes</span>
                        </div>
                        <div>
                            <i class="fa fa-user-md fs-3 text-primary"></i>
                        </div>
                    </div>
                    <!-- Biologistes Stats -->
                    <div class="d-flex flex-column gap-2">
                        <h2 class="fw-bold mb-0">{{ $prescripteurs }}</h2>
                        <span class="badge bg-info">Biologistes actifs</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Revenus (visible uniquement pour superadmin et secrétaire) -->
        @if(auth()->user()->hasRole(['superadmin', 'secretaire']))
        <div class="col-xl-3 col-lg-6 col-md-12 col-12">
            <div class="card h-100">
                <div class="card-body d-flex flex-column gap-3">
                    <!-- Revenus Header -->
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="fs-6 text-uppercase fw-semibold">Revenus hebdo</span>
                        </div>
                        <div>
                            <i class="fe fe-dollar-sign fs-3 text-primary"></i>
                        </div>
                    </div>
                    <!-- Revenus Stats -->
                    <div class="d-flex flex-column gap-2">
                        <h2 class="fw-bold mb-0">{{ number_format($revenusHebdo, 0, ',', ' ') }} Ar</h2>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-{{ $croissanceRevenu >= 0 ? 'success' : 'danger' }} d-flex align-items-center">
                                <i class="fe fe-trending-{{ $tendanceRevenu }} me-1"></i>
                                {{ abs($croissanceRevenu) }}%
                            </span>
                            <small class="text-muted">vs semaine précédente</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Refresh Indicator -->
    <div wire:loading class="position-fixed bottom-0 end-0 p-3">
        <div class="alert alert-info d-flex align-items-center m-0">
            <span class="spinner-border spinner-border-sm me-2"></span>
            Actualisation...
        </div>
    </div>
</div>
