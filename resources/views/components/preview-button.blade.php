@props(['target'])

<button
    x-data="{ downloading: false }"
    @click="
        downloading = true;
        $wire.generateResultatsPDF().then(url => {
            if (url) {
                window.open(url, '_blank', 'noopener,noreferrer');
            }
            downloading = false;
        }).catch(() => {
            downloading = false;
        });
    "
    :disabled="downloading"
    class="btn flex-grow-1 text-white"
    style="background-color: #198f37"
>
    <div class="d-flex align-items-center justify-content-center">
        <template x-if="!downloading">
            <span>
                <i class="fas fa-file-pdf me-2"></i>
                Aperçu analyse
            </span>
        </template>
        <template x-if="downloading">
            <span>
                <i class="fas fa-spinner fa-spin me-2"></i>
                Génération...
            </span>
        </template>
    </div>
</button>
