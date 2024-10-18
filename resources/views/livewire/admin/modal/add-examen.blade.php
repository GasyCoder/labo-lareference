<div wire:ignore.self class="modal fade" id="newExamen" tabindex="-1" role="dialog" aria-labelledby="newExamenLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title mb-0" id="newExamenLabel">{{ $editingExamenId ? 'Modifier' : 'Ajouter' }} un examen</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form wire:submit.prevent="save">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model.defer="name" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="abr" class="form-label">Abréviation</label>
                        <input type="text" class="form-control @error('abr') is-invalid @enderror" id="abr" wire:model.defer="abr" required>
                        @error('abr') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="status" wire:model.defer="status">
                            <label class="form-check-label" for="status">Actif</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        {{ $editingExamenId ? 'Mettre à jour' : 'Ajouter' }} l'examen
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
