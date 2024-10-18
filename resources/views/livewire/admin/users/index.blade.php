<div>
<section class="container-fluid p-4">
    <div class="row">
    <div class="col-lg-12 col-md-12 col-12">
        <!-- Page Header -->
        <div class="border-bottom pb-3 mb-3 d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between">
        <div class="d-flex flex-column gap-1">
            <h1 class="mb-0 h2 fw-bold">Gestion des utilisateurs</h1>
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a href="#">Administration</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Tous</li>
            </ol>
            </nav>
        </div>
        <div>
            <a href="{{route('admin.users.create')}}" class="btn btn-primary">Nouvelle utilisateur</a>
        </div>
        </div>
    </div>
    </div>
    <div class="row">
    <div class="col-lg-12 col-md-12 col-12">
        <!-- Card -->
        <div class="card rounded-3">
        <!-- Card header -->
        <div class="card-header p-0">
            <div>
            <!-- Nav -->
            <ul class="nav nav-lb-tab border-bottom-0" id="tab" role="tablist">
                <li class="nav-item" role="presentation">
                <a class="nav-link active" id="courses-tab" data-bs-toggle="pill" href="#courses"
                role="tab" aria-controls="courses" aria-selected="true">Tous</a>
                </li>
                <li class="nav-item" role="presentation">
                <a class="nav-link" id="biologiste-tab" data-bs-toggle="pill" href="#biologiste"
                role="tab" aria-controls="biologiste" aria-selected="false" tabindex="-1">Biologistes</a>
                </li>
                <li class="nav-item" role="presentation">
                <a class="nav-link" id="secretaire-tab" data-bs-toggle="pill" href="#secretaire"
                role="tab" aria-controls="secretaire" aria-selected="false" tabindex="-1">Secrétaires</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="technicien-tab" data-bs-toggle="pill" href="#technicien"
                    role="tab" aria-controls="technicien" aria-selected="false" tabindex="-1">Techniciens</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="prescripteur-tab" data-bs-toggle="pill" href="#prescripteur"
                    role="tab" aria-controls="prescripteur" aria-selected="false" tabindex="-1">Prescripteurs</a>
                </li>
            </ul>
            </div>
        </div>
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
            <div class="tab-pane fade show active" id="courses" role="tabpanel" aria-labelledby="courses-tab">
                <div class="table-responsive border-0 overflow-y-hidden">
                <table class="table mb-0 text-nowrap table-centered table-hover">
                    <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>Utilisateur</th>
                        <th>Email</th>
                        <th>Crée</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($allusers as $key => $user)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                <a href="#" class="text-inherit">
                                    <div class="d-flex align-items-center gap-3">
                                        <div>
                                            @if($user->avatar)
                                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="rounded-circle" width="40" height="40">
                                            @else
                                                <div class="avatar avatar-md bg-primary text-white rounded-circle d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
                                                    <span class="fs-6 fw-bold">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="d-flex flex-column gap-1">
                                            <h4 class="mb-0 text-primary-hover">{{ $user->name }}</h4>
                                            <span>{{ $user->roles->first()->label ?? 'Aucun rôle' }}</span>
                                        </div>
                                    </div>
                                </a>
                            </td>
                            <td>
                                <div class="d-flex align-items-center flex-row gap-2">
                                    <h5 class="mb-0">{{ $user->email }}</h5>
                                </div>
                            </td>
                            <td>{{ $user->created_at->diffForHumans() }}</td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-danger">Inactif</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary"
                                       data-bs-toggle="tooltip" data-bs-placement="top" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <a href="#" class="btn btn-sm btn-outline-info"
                                       data-bs-toggle="tooltip" data-bs-placement="top" title="Profil">
                                        <i class="fas fa-user"></i>
                                    </a>

                                    @if($user->is_active)
                                        <button type="button" class="btn btn-sm btn-outline-warning"
                                                wire:click="toggleUserStatus({{ $user->id }})"
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Désactiver">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-success"
                                                wire:click="toggleUserStatus({{ $user->id }})"
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Activer">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    @endif

                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            wire:click="confirmDelete({{ $user->id }})"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
            @include('livewire.admin.users.roles.biologiste')
            @include('livewire.admin.users.roles.secretaire')
            @include('livewire.admin.users.roles.technicien')
            @include('livewire.admin.users.roles.prescripteur')
            </div>
        </div>
        <!-- Card Footer -->
        <div class="card-footer">
            <nav>
            <div class="pagination justify-content-center mb-0">
                {{ $biologistes->links() }}
            </div>
            </nav>
        </div>
        </div>
    </div>
    </div>
</section>
</div>
