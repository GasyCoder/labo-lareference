 <!-- Modal -->
 <div wire:ignore.self class="modal fade" id="newGerme" tabindex="-1" role="dialog"
    aria-labelledby="newGermeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title mb-0" id="newGermeLabel">{{ $editingFamilyId ? 'Modifier' : 'Ajouter' }} une famille de bactéries</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form wire:submit.prevent="save">
                <div class="mb-3">
                    <label for="name" class="form-label">Nom de la famille</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model="name">
                    @error('name') <div class="text-danger">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="antibiotics" class="form-label">Antibiotiques (séparés par des virgules)</label>
                    <textarea class="form-control @error('antibiotics') is-invalid @enderror" id="antibiotics"
                    wire:model="antibiotics" cols="5" rows="3"></textarea>
                    @error('antibiotics') <div class="text-danger">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="bacteries" class="form-label">Bactéries (séparées par des virgules)</label>
                    <textarea class="form-control @error('bacteries') is-invalid @enderror" id="bacteries"
                    wire:model="bacteries" cols="5" rows="3"></textarea>
                    @error('bacteries') <div class="text-danger">{{ $message }}</div> @enderror
                </div>

                <div class="mb-2">
                    <label class="form-label">Status</label>
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="status" wire:model="status" />
                        <label class="form-check-label" for="status"></label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    {{ $editingFamilyId ? 'Mettre à jour' : 'Ajouter' }} la famille
                </button>
            </form>
        </div>
      </div>
    </div>
</div>
