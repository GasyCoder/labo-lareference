@props([
    'conclusion' => '',  // Valeur par défaut
    'disabled' => false  // État désactivé par défaut
])

<div class="mt-4 border-top pt-4">
    <div class="conclusion-section">
        <h5 class="mb-3 d-flex align-items-center">
            <i class="fas fa-clipboard-check me-2 text-primary"></i>
            {{ __('Conclusion') }}
        </h5>

        <div class="mb-3">
            <textarea
                wire:model="{{ $attributes->get('wire:model', 'conclusion') }}"
                class="form-control {{ $attributes->get('class') }}"
                rows="4"
                placeholder="Commentaires sur le résultat de l'analyse"
                {{ $disabled ? 'disabled' : '' }}
                {{ $attributes->except(['class', 'wire:model']) }}
            ></textarea>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <button
                type="submit"
                class="btn btn-primary"
                wire:loading.attr="disabled"
                {{ $disabled ? 'disabled' : '' }}
            >
                <span wire:loading.remove>
                    <i class="fas fa-save me-2"></i>
                    {{ __('Enregistrer') }}
                </span>
                <span wire:loading>
                    <i class="fas fa-spinner fa-spin me-2"></i>
                    {{ __('Enregistrement...') }}
                </span>
            </button>
        </div>
    </div>
</div>

<style>
.conclusion-section {
    background-color: #fff;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.conclusion-section textarea {
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    resize: vertical;
    min-height: 100px;
    transition: all 0.3s ease;
}

.conclusion-section textarea:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25);
}

.conclusion-section .btn-primary {
    padding: 0.5rem 1.5rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.conclusion-section .btn-primary:hover {
    transform: translateY(-1px);
}

.conclusion-section .btn-primary:active {
    transform: translateY(0);
}

.conclusion-section h5 {
    color: #2c3e50;
    font-weight: 600;
}

.conclusion-section i {
    font-size: 1.1rem;
}

@media (max-width: 768px) {
    .conclusion-section {
        padding: 1rem;
    }
}
</style>
