{{-- resources/views/components/prescription-actions.blade.php --}}
@props(['prescription', 'hasValidatedResults'])

<div class="d-flex gap-2 justify-content-end">
    @if($hasValidatedResults)
        <x-pdf-download-button :prescription="$prescription" title="Aperçu en pdf" />
    @endif

    <a href="{{ route('secretaire.prescriptions.profil', ['id' => $prescription->id]) }}"
       class="btn btn-sm btn-info" title="Voir détails">
        <i class="fas fa-user"></i>
    </a>

    @if(!$prescription->trashed())
        <a href="{{ route('secretaire.prescriptions.edit', ['id' => $prescription->id]) }}"
           class="btn btn-sm btn-success" title="Modifier">
            <i class="fas fa-edit"></i>
        </a>

        <button wire:click="confirmDelete({{ $prescription->id }})"
                class="btn btn-sm btn-danger" title="Corbeille">
            <i class="fas fa-trash"></i>
        </button>
    @else
        <button wire:click="confirmRestore({{ $prescription->id }})"
                class="btn btn-sm btn-warning" title="Restaurer">
            <i class="fas fa-undo-alt"></i>
        </button>

        <button wire:click="confirmPermanentDelete({{ $prescription->id }})"
                class="btn btn-sm btn-danger" title="Supprimer définitivement">
            <i class="fas fa-trash-alt"></i>
        </button>
    @endif
</div>