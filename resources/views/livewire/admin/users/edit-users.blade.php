<div>
    <section class="container-fluid p-4">
        <div class="row">
          <div class="col-lg-12 col-md-12 col-12">
            <!-- Page header -->
            <div class="border-bottom pb-3 mb-3">
              <div>
                <h1 class="mb-0 h2 fw-bold">Modification utilisateur</h1>
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                      <a href="{{ route('admin.users.list')}}">Utilisateurs</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Modifier</li>
                  </ol>
                </nav>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="offset-xl-3 col-xl-6 col-12">
            <form wire:submit="updateUser" class="needs-validation" novalidate>
                <div class="d-flex flex-column gap-4">
                    <div class="card">
                        <div class="card-body d-flex flex-column gap-4">
                            <h4 class="mb-0">Modifier l'utilisateur</h4>
                            <div class="row gx-3">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="name">Nom complet</label>
                                    <input type="text" class="form-control" placeholder="Nom complet"
                                           id="name" wire:model="name">
                                    @error('name') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" class="form-control" placeholder="Enter email address" id="email"
                                           wire:model="email">
                                    @error('email') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="password">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                                    <input type="password" class="form-control" placeholder="Entrez le nouveau mot de passe"
                                           id="password" wire:model="password">
                                    @error('password') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="password_confirmation">Confirmez le nouveau mot de passe</label>
                                    <input type="password" class="form-control" placeholder="Confirmez le nouveau mot de passe"
                                           id="password_confirmation" wire:model="password_confirmation">
                                </div>
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="role">Rôle</label>
                                    <select class="form-select" id="role" wire:model="role" required>
                                        <option value="">Choisissez un rôle...</option>
                                        <option value="biologiste">Biologiste</option>
                                        <option value="secretaire">Secrétaire</option>
                                        <option value="technicien">Technicien</option>
                                        <option value="prescripteur">Prescripteur</option>
                                    </select>
                                    @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-success" type="submit">Mettre à jour</button>
                    </div>
                </div>
            </form>
          </div>
        </div>
      </section>
</div>
