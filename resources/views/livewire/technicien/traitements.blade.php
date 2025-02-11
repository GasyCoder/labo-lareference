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
                                    <a class="nav-link {{ $tab === 'actifs' ? 'active' : '' }}" href="#actifs"
                                       wire:click.prevent="switchTab('actifs')">
                                        <i class="fas fa-list-ul me-2"></i>Actives
                                        <span class="badge bg-primary ms-1">{{ $activePrescriptions->total() }}</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $tab === 'termine' ? 'active' : '' }}" href="#termine"
                                       wire:click.prevent="switchTab('termine')">
                                        <i class="fas fa-check-circle me-2"></i>Terminés
                                        <span class="badge bg-success ms-1">{{ $analyseTermines->total() }}</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $tab === 'refaire' ? 'active' : '' }}" href="#refaire"
                                       wire:click.prevent="switchTab('refaire')">
                                        <i class="fa fa-refresh me-2"></i>A réfaire
                                        <span class="badge bg-warning ms-1">{{ $prescriptionsARefaire->total() }}</span>
                                    </a>
                                </li>
                            </ul>

                            <!-- Contenu des onglets -->
                            <div class="tab-content">
                                <div class="tab-pane fade {{ $tab === 'actifs' ? 'show active' : '' }}" id="actifs">
                                    @include('livewire.technicien.partials.prescription-card', ['prescriptions' => $activePrescriptions])
                                </div>
                                <div class="tab-pane fade {{ $tab === 'termine' ? 'show active' : '' }}" id="termine">
                                    @include('livewire.technicien.partials.prescription-card', ['prescriptions' => $analyseTermines])
                                </div>
                                <div class="tab-pane fade {{ $tab === 'refaire' ? 'show active' : '' }}" id="refaire">
                                    @include('livewire.technicien.partials.prescription-card', ['prescriptions' => $prescriptionsARefaire])
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
