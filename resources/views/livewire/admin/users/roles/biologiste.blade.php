<!--Tab pane -->
<div class="tab-pane fade" id="biologiste" role="tabpanel" aria-labelledby="biologiste-tab">
    <div class="table-responsive border-0 overflow-y-hidden">
        <table class="table mb-0 text-nowrap table-centered table-hover">
            <thead class="table-light">
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
                @foreach($biologistes as $key => $biologiste)
                <tr>
                    <td>{{ $biologistes->firstItem() + $key }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            @if($biologiste->avatar)
                                <img src="{{ asset('storage/' . $biologiste->avatar) }}" alt="{{ $biologiste->name }}" class="rounded-circle" width="40" height="40">
                            @else
                                <div class="avatar avatar-md bg-primary text-white rounded-circle d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
                                    <span class="fs-6 fw-bold">{{ strtoupper(substr($biologiste->name, 0, 2)) }}</span>
                                </div>
                            @endif
                            <div>
                                <h6 class="mb-0">{{ $biologiste->name }}</h6>
                                <small class="text-muted">Biologiste</small>
                            </div>
                        </div>
                    </td>
                    <td>{{ $biologiste->email }}</td>
                    <td>{{ $biologiste->created_at->diffForHumans() }}</td>
                    <td>
                        @if($biologiste->is_active)
                            <span class="badge bg-success">Actif</span>
                        @else
                            <span class="badge bg-danger">Inactif</span>
                        @endif
                    </td>
                    {{-- <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton{{ $biologiste->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                Actions
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $biologiste->id }}">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i>Modifier</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profil</a></li>
                                @if($biologiste->is_active)
                                    <li><a class="dropdown-item text-warning" href="#"><i class="fas fa-ban me-2"></i>Désactiver</a></li>
                                @else
                                    <li><a class="dropdown-item text-success" href="#"><i class="fas fa-check-circle me-2"></i>Activer</a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash me-2"></i>Supprimer</a></li>
                            </ul>
                        </div>
                    </td> --}}
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-end mt-3">
            {{ $biologistes->links() }}
        </div>
    </div>
</div>