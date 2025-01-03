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
    {{ $attributes->merge(['class' => 'btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center', 'style' => 'width: 32px; height: 32px;']) }}
    title="{{ $attributes->get('title', 'Aperçu en pdf') }}">
    <template x-if="!downloading">
        <i class="fas fa-file-pdf"></i>
    </template>
    <template x-if="downloading">
        <i class="fas fa-spinner fa-spin"></i>
    </template>
</button>
