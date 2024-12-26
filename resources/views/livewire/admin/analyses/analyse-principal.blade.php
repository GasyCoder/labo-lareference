<div>
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page header -->
                <div class="border-bottom pb-3 mb-3 d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between">
                    <div class="d-flex flex-column gap-1">
                        <h1 class="mb-0 h2 fw-bold">Gestions des analyses</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="#">Analyses</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Tous</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#newAnalyse" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Ajouter nouvel analyse
                        </a>
                        <button wire:click="export" class="btn btn-secondary">
                            <i class="fas fa-file-download"></i> Exporter
                        </button>
                    </div>
                </div>

                <!-- Filtres -->
                <div class="card rounded-3 mb-3">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fe fe-search"></i></span>
                                    <input type="search" class="form-control" placeholder="Rechercher..."
                                           wire:model.live.debounce.300ms="search">
                                </div>
                            </div>

                            <div class="col-12 col-md-2">
                                <select wire:model.live="filterLevel" class="form-select">
                                    @foreach($levels as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-2">
                                <select wire:model.live="filterExamen" class="form-select">
                                    <option value="">Tous les examens</option>
                                    @foreach($examens as $examen)
                                        <option value="{{ $examen->id }}">{{ $examen->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-2">
                                <select wire:model.live="filterType" class="form-select">
                                    <option value="">Tous les types</option>
                                    @foreach($analyseTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-2">
                                <select wire:model.live="filterStatus" class="form-select">
                                    <option value="">Tous les statuts</option>
                                    <option value="1">Actif</option>
                                    <option value="0">Inactif</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-1">
                                <button wire:click="resetFilters" class="btn btn-light w-100" title="Réinitialiser les filtres">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table des analyses -->
                <div class="card rounded-3">
                    <div class="tab-content" id="tabContent">
                        <div class="tab-pane fade show active">
                            <div class="table-responsive border-0 overflow-y-hidden">
                                <table class="table mb-0 text-nowrap table-centered table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th wire:click="sortBy('code')" style="cursor: pointer;">
                                                # {!! $sortField === 'code' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' !!}
                                            </th>
                                            <th wire:click="sortBy('level')" style="cursor: pointer;">
                                                Niveau {!! $sortField === 'level' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' !!}
                                            </th>
                                            <th wire:click="sortBy('abr')" style="cursor: pointer;">
                                                Abr {!! $sortField === 'abr' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' !!}
                                            </th>
                                            <th wire:click="sortBy('designation')" style="cursor: pointer;">
                                                Designation {!! $sortField === 'designation' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' !!}
                                            </th>
                                            <th wire:click="sortBy('prix')" style="cursor: pointer;">
                                                Tarif {!! $sortField === 'prix' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' !!}
                                            </th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($analyses as $analyse)
                                            <tr>
                                                <td>{{ $analyse->code }}</td>
                                                <td>
                                                    <span class="badge bg-{{
                                                        $analyse->level === 'PARENT' ? 'primary' :
                                                        ($analyse->level === 'NORMAL' ? 'success' : 'info')
                                                    }}">
                                                        {{ $analyse->level }}
                                                    </span>
                                                </td>
                                                <td>{{ $analyse->abr }}</td>
                                                <td class="{{ $analyse->is_bold ? 'fw-bold' : '' }}">
                                                    {{ $analyse->designation }}
                                                </td>
                                                <td>{{ number_format($analyse->prix, 2, ',', ' ') }} Ar</td>
                                                <td>
                                                    <span class="badge bg-{{ $analyse->status ? 'success' : 'danger' }}">
                                                        {{ $analyse->status ? 'Actif' : 'Inactif' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                            wire:click="viewDetails({{ $analyse->id }})"
                                                            data-bs-toggle="modal" data-bs-target="#viewAnalyseModal"
                                                            title="Détail">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-success"
                                                            wire:click="edit({{ $analyse->id }})"
                                                            data-bs-toggle="modal" data-bs-target="#newAnalyse"
                                                            title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-warning"
                                                            wire:click="duplicate({{ $analyse->id }})"
                                                            title="Dupliquer">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                            wire:click="confirmDelete({{ $analyse->id }})"
                                                            title="Supprimer">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-3">
                                                    <div class="text-muted">Aucune analyse trouvée</div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Pagination -->
                    <div class="card-footer">
                        {{ $analyses->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('livewire.admin.modal.add-analyse')
    @include('livewire.admin.modal.view-analyse')
</div>

@push('scripts')
<script>
    window.addEventListener('close-modal', event => {
        $('#newAnalyse').modal('hide');
        $('#viewAnalyseModal').modal('hide');
    });

    window.addEventListener('open-modal', event => {
        $('#newAnalyse').modal('show');
    });

    window.addEventListener('open-view-modal', event => {
        $('#viewAnalyseModal').modal('show');
    });

    document.addEventListener('livewire:load', function () {
        Livewire.on('analyseDetailsLoaded', function () {
            $('#viewAnalyseModal').modal('show');
        });
    });
</script>
@endpush
