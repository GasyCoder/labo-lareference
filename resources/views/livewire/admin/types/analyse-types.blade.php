{{-- <form wire:submit.prevent="save">
    <div class="mb-3">
        <label for="name" class="form-label">Nom</label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model="name">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="mb-3">
        <label for="libelle" class="form-label">Libellé</label>
        <input type="text" class="form-control @error('libelle') is-invalid @enderror" id="libelle" wire:model="libelle">
        @error('libelle') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <button type="submit" class="btn btn-primary">{{ $editingTypeId ? 'Mettre à jour' : 'Ajouter' }}</button>
</form> --}}

<div>
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3 d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between">
                <div class="d-flex flex-column gap-1">
                    <h1 class="mb-0 h2 fw-bold">Gestion des Types d'Analyse</h1>
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                        <a href="#">Types d'analyse</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Tous</li>
                    </ol>
                    </nav>
                </div>
                <div>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#newType" class="btn btn-primary">Nouvelle Type</a>
                </div>
                </div>
            </div>
            </div>
            <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Card -->
                <div class="card rounded-3">
                    <div class="p-4 row">
                        <!-- Form -->
                        <form class="d-flex align-items-center col-12 col-md-12 col-lg-12">
                        <span class="position-absolute ps-3 search-icon"><i class="fe fe-search"></i></span>
                        <input type="search" class="form-control ps-6" placeholder="Rechercher...">
                        </form>
                    </div>
                <div>
                    <!-- Table -->
                    <div class="tab-content" id="tabContent">
                    <!--Tab pane -->
                    <div class="tab-pane fade show active">
                        <div class="table-responsive border-0 overflow-y-hidden">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-center" style="width: 5%;">#</th>
                                        <th>Nom</th>
                                        <th>Libellé</th>
                                        <th>Crée</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($types as $key => $type)
                                        <tr>
                                            <td>{{$key+1}}</td>
                                            <td>{{ $type->name }}</td>
                                            <td>{{ $type->libelle }}</td>
                                            <td>{{ $type->created_at->diffForHumans() }}</td>
                                            <td>
                                                @if($type->status)
                                                    <span class="badge bg-success">Actif</span>
                                                @else
                                                    <span class="badge bg-danger">Inactif</span>
                                                @endif
                                            </td>
                                            <td class="text-end align-middle">
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newType"
                                                        wire:click="edit({{ $type->id }})"
                                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        wire:click="confirmDelete({{ $type->id }})"
                                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    </div>
                </div>
                <!-- Card Footer -->
                <div class="card-footer">
                    {{ $types->links() }}
                </div>
                </div>
            </div>
            </div>
    </section>
    @include('livewire.admin.modal.add-types')
    </div>