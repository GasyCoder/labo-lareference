<div>
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3 d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between">
                    <div class="d-flex flex-column gap-1">
                        <h1 class="mb-0 h2 fw-bold">Liste des familles de bactéries</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="#">Germes</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Tous</li>
                            </ol>
                        </nav>
                    </div>
                    @if(auth()->user()->hasRole('superadmin'))
                    <div>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#newGerme" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Nouvelle Famille
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <div class="card rounded-3">
                    <div class="p-4 row">
                        <form class="d-flex align-items-center col-12 col-md-12 col-lg-12">
                            <span class="position-absolute ps-3 search-icon">
                                <i class="fe fe-search"></i>
                            </span>
                            <input type="search" class="form-control ps-6" placeholder="Rechercher...">
                        </form>
                    </div>
                    <div>
                        <div class="tab-content" id="tabContent">
                            <div class="tab-pane fade show active">
                                <div class="table-responsive border-0 overflow-y-hidden">
                                    <table class="table table-hover table-striped align-middle">
                                        <thead class="table-dark">
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Familles</th>
                                                <th>Antibiotiques</th>
                                                <th>Bactéries</th>
                                                <th>Status</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($families as $key => $family)
                                            <tr>
                                                <td class="text-center">{{ $key+1 }}</td>
                                                <td>
                                                    <strong>{{ $family->name }}</strong>
                                                </td>
                                                <td>
                                                    @foreach($family->antibiotics as $antibiotic)
                                                        <span class="badge bg-primary me-1 mb-1">{{ $antibiotic }}</span>
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @foreach($family->bacteries as $bacterie)
                                                        <span class="badge bg-info text-dark me-1 mb-1">{{ $bacterie }}</span>
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @if($family->status)
                                                        <span class="badge bg-success">Actif</span>
                                                    @else
                                                        <span class="badge bg-danger">Inactif</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group" role="group">
                                                        @if(auth()->user()->hasRole('superadmin'))
                                                            {{-- Actions complètes pour superadmin --}}
                                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                                    data-bs-toggle="modal" data-bs-target="#newGerme"
                                                                    wire:click="edit({{ $family->id }})"
                                                                    title="Modifier">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                    wire:click="confirmDelete({{ $family->id }})"
                                                                    title="Supprimer">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        @else
                                                            {{-- Bouton de consultation pour les autres rôles --}}
                                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                                    wire:click="view({{ $family->id }})"
                                                                    title="Voir détails">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        @endif
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
                    <div class="card-footer">
                        {{ $families->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
    @include('livewire.admin.modal.add-germes')
    </div>
