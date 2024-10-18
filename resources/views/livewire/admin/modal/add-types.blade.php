 <!-- Modal -->
 <div wire:ignore.self class="modal fade" id="newType" tabindex="-1" role="dialog"
    aria-labelledby="newTypeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title mb-0" id="newTypeLabel">{{ $editingTypeId ? 'Modifier' : 'Ajouter' }} une type</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form wire:submit.prevent="save">
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
                <div class="mb-2">
                    <label class="form-label">Status</label>
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="status" wire:model="status" />
                        <label class="form-check-label" for="status"></label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">{{ $editingTypeId ? 'Mettre à jour' : 'Ajouter' }}</button>
            </form>
        </div>
      </div>
    </div>
</div>
