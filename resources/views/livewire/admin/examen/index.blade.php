<div>
    <section class="container-fluid p-4">
        <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <!-- Page Header -->
            <div class="border-bottom pb-3 mb-3 d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between">
            <div class="d-flex flex-column gap-1">
                <h1 class="mb-0 h2 fw-bold">Gestion des examens</h1>
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                    <a href="#">Examens</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Tous</li>
                </ol>
                </nav>
            </div>
            <div>
                <a href="#" data-bs-toggle="modal" data-bs-target="#newExamen" class="btn btn-primary">Nouvelle examen</a>
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
                <!-- Table -->
                <div class="tab-content" id="tabContent">
                    <!--Tab pane -->
                    <div class="tab-pane fade show active">
                        <div class="table-responsive border-0 overflow-y-hidden">
                        <table class="table mb-0 text-nowrap table-centered table-hover">
                            <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                <th>Nom</th>
                                <th>Abr</th>
                                <th>Crée</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($examens as $key => $examen)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $examen->name }}</td>
                                    <td>{{ $examen->abr }}</td>
                                    <td>{{ $examen->created_at->diffForHumans() }}</td>
                                    <td>
                                        @if($examen->status)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-danger">Inactif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-2">
                                            {{-- Bouton Modifier accessible à tous les rôles autorisés --}}
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    wire:click="edit({{ $examen->id }})"
                                                    data-bs-toggle="modal" data-bs-target="#newExamen"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            {{-- Bouton Supprimer uniquement pour superadmin --}}
                                            @if(auth()->user()->hasRole('superadmin'))
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        wire:click="confirmDelete({{ $examen->id }})"
                                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
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
                <nav>
                <div class="pagination justify-content-center mb-0">
                    {{ $examens->links() }}
                </div>
                </nav>
            </div>
            </div>
        </div>
        </div>
    </section>
@include('livewire.admin.modal.add-examen')
</div>

@push('scripts')
<script>
    window.addEventListener('close-modal', event => {
        $('#newExamen').modal('hide');
    });

    window.addEventListener('open-modal', event => {
        $('#newExamen').modal('show');
    });
</script>
@endpush
