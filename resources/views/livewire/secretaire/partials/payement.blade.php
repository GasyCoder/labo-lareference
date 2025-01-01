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
                        <option value="Mvola">Mvola</option>
                        <option value="OrangeMoney">OrangeMoney</option>
                        <option value="AirtelMoeny">AirtelMoeny</option>
                        <option value="CHEQUE">Chèque</option>
                    </select>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox"
                               class="form-check-input"
                               id="applyRemise"
                               wire:model.live="showRemise">
                        <label class="form-check-label" for="applyRemise">
                            Appliquer une remise
                        </label>
                    </div>
                </div>

                @if($showRemise)
                    <div class="mb-3">
                        <label for="remisePercent" class="form-label">Pourcentage de remise</label>
                        <select wire:model.live="remisePercent" id="remisePercent" class="form-select">
                            <option value="">Sélectionner une remise</option>
                            <option value="10">10%</option>
                            <option value="20">20%</option>
                            <option value="50">50%</option>
                        </select>
                    </div>

                    @if($remisePercent)
                        <div class="alert alert-info">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Montant avant remise:</span>
                                <strong>{{ number_format($montantAvantRemise, 0, ',', ' ') }} Ar</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2 text-danger">
                                <span>Remise ({{ $remisePercent }}%):</span>
                                <strong>-{{ number_format($montantRemise, 0, ',', ' ') }} Ar</strong>
                            </div>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Montant après remise:</span>
                                <span>{{ number_format($montantApresRemise, 0, ',', ' ') }} Ar</span>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="alert alert-info">
                        <strong>Montant Total à Payer:</strong>
                        {{ number_format($totalGeneral, 0, ',', ' ') }} Ar
                    </div>
                @endif
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Annuler
                </button>
                <button type="button"
                        class="btn btn-primary"
                        wire:click="processPaiement"
                        @if($showRemise && !$remisePercent) disabled @endif>
                    Confirmer le paiement
                </button>
            </div>
        </div>
    </div>
</div>
