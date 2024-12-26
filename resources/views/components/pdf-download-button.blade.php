@props(['prescription'])

<button
    x-data="{ downloading: false }"
    @click="
        downloading = true;
        $wire.generateResultatsPDF({{ $prescription->id }})
            .then(url => {
                if (url) {
                    window.open(url, '_blank');
                } else {
                    alert('Erreur lors de la génération du PDF');
                }
                downloading = false;
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de la génération du PDF');
                downloading = false;
            });
    "
    :disabled="downloading"
    class="btn btn-sm btn-primary"
    title="{{ $attributes->get('title', 'Aperçu en pdf') }}">
    <div class="d-flex align-items-center justify-content-center">
        <template x-if="!downloading">
            <i class="fas fa-file-pdf"></i>
        </template>
        <template x-if="downloading">
            <i class="fas fa-spinner fa-spin"></i>
        </template>
    </div>
</button>
