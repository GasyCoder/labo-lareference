{{-- Modal de paiement --}}
<div wire:ignore.self
     class="modal fade"
     id="paymentModal"
     tabindex="-1"
     aria-labelledby="paymentModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Effectuer le paiement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label for="modePaiement" class="form-label">Mode de paiement</label>
                    <select wire:model.live="modePaiement" id="modePaiement" class="form-select">
                        <option value="ESPECES">Espèces</option>
                        <option value="CARTE">Carte</option>
                        <option value="CHEQUE">Chèque</option>
                    </select>
                </div>

                <div class="alert alert-info">
                    <strong>Montant total à payer:</strong>
                    {{ number_format($totalGeneral, 0, ',', ' ') }} Ar
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Annuler
                </button>
                <button type="button" class="btn btn-primary" wire:click="processPaiement">
                    Confirmer le paiement
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));

        // Fermer la modal après un paiement réussi
        Livewire.on('payment-processed', () => {
            paymentModal.hide();
        });
    });
</script>
@endpush
