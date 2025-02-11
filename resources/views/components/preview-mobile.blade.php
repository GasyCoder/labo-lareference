
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
    {{ $attributes->merge(['class' => 'btn btn-danger w-100 d-flex align-items-center justify-content-center', 'style' => 'font-size: 13px; padding: 8px 0; background-color: #dc3545; border-radius: 6px;']) }}
    title="{{ $attributes->get('title', 'Aperçu') }}">
    <div class="">
        <template x-if="!downloading">
            <span>
                <i class="fas fa-file-pdf me-2"></i>
                Aperçu
            </span>
        </template>
        <template x-if="downloading">
            <span>
                <i class="fas fa-spinner fa-spin me-2"></i>
            </span>
        </template>
    </div>
</button>
