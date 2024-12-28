<div>
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
            <!-- Page header -->
            <div class="border-bottom pb-3 mb-3 d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between">
                <div class="d-flex flex-column gap-1">
                <h1 class="mb-0 h2 fw-bold">Gestions des elements d'analyses</h1>
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="#">Element d'analyses</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Tous</li>
                    </ol>
                </nav>
                </div>
                <!-- button -->
                <div>
                <a href="#" data-bs-toggle="modal" data-bs-target="#newElement" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Ajouter nouvel element</a>
                <a href="#" class="btn btn-secondary me-2"><i class="fas fa-file-download me-0"></i> Exporter</a>
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
                        <!-- Table -->
                        <div class="tab-content" id="tabContent">
                            <!--Tab pane -->
                            <div class="tab-pane fade show active">
                                <div class="table-responsive border-0 overflow-y-hidden">
                                <table class="table mb-0 text-nowrap table-centered table-hover">
                                    <thead class="table-primary">
                                    <tr>
                                        <th>#</th>
                                        <th>Code</th>
                                        <th>Niveau</th>
                                        <th>Designations</th>
                                        <th>Crée</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($analyseElements as $key => $element)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $element->code }}</td>
                                            <td>{{ $element->level }}</td>
                                            <td>{{ $element->designation }}</td>
                                            <td>{{ $element->created_at->diffForHumans() }}</td>
                                            <td>
                                                @if($element->status)
                                                    <span class="badge bg-success">Actif</span>
                                                @else
                                                    <span class="badge bg-danger">Inactif</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-end gap-2">
                                                    @if(auth()->user()->hasRole('superadmin'))
                                                        {{-- Boutons complets pour superadmin --}}
                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                                wire:click="edit({{ $element->id }})"
                                                                data-bs-toggle="modal" data-bs-target="#newElement"
                                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                                wire:click="confirmDelete({{ $element->id }})"
                                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Supprimer">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @else
                                                        {{-- Juste le bouton modifier pour les autres rôles --}}
                                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                                wire:click="edit({{ $element->id }})"
                                                                data-bs-toggle="modal" data-bs-target="#newElement"
                                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Voir détails">
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
                        <!-- Card Footer -->
                        <div class="card-footer">
                            {{ $analyseElements->links() }}
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </section>

    @include('livewire.admin.modal.add-analyse-element')
    </div>

    @push('scripts')
    <script>
        window.addEventListener('close-modal', event => {
            $('#newElement').modal('hide');
        });

        window.addEventListener('open-modal', event => {
            $('#newElement').modal('show');
        });
    </script>
    @endpush
