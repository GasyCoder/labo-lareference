<div>
    <section class="container-fluid p-4">
        <div class="row">
          <div class="col-lg-12 col-md-12 col-12">
            <!-- Page header -->
            <div class="border-bottom pb-3 mb-3">
              <div>
                <h1 class="mb-0 h2 fw-bold">Profile utilisateur</h1>
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                      <a href="admin-dashboard.html">Utilisateurs</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Ajouter</li>
                  </ol>
                </nav>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="offset-xl-3 col-xl-6 col-12">
        <!-- card -->
        <form wire:submit="createProfile" class="needs-validation" novalidate>
            <div class="d-flex flex-column gap-4">
              <div class="card">
                <div class="card-body d-flex flex-column gap-4">
                  <h4 class="mb-0">Complétez votre profil</h4>
                  <div class="row gx-3">
                    <div class="mb-3 col-md-12">
                      <label class="form-label" for="sexe">Sexe</label>
                      <select class="form-select" id="sexe" wire:model="sexe" required>
                        <option value="">Choisissez...</option>
                        <option value="homme">Homme</option>
                        <option value="femme">Femme</option>
                        <option value="autre">Autre</option>
                      </select>
                      @error('sexe') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3 col-md-12">
                      <label class="form-label" for="adresse">Adresse</label>
                      <input type="text" class="form-control" placeholder="Votre adresse" id="adresse" wire:model="adresse" required>
                      @error('adresse') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3 col-md-6">
                      <label class="form-label" for="ville">Ville</label>
                      <input type="text" class="form-control" placeholder="Votre ville" id="ville" wire:model="ville" required>
                      @error('ville') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3 col-md-6">
                      <label class="form-label" for="province">Province</label>
                      <input type="text" class="form-control" placeholder="Votre province" id="province" wire:model="province" required>
                      @error('province') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3 col-md-12">
                      <label class="form-label" for="photo">Photo de profil</label>
                      <input type="file" class="form-control" id="photo" wire:model="photo">
                      @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                  </div>
                </div>
              </div>
              <div class="d-flex justify-content-end">
                <button class="btn btn-primary" type="submit">Enregistrer le profil</button>
              </div>
            </div>
          </form>
          </div>
        </div>
      </section>
</div>
