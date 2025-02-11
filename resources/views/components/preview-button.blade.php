<!-- Component Preview Button -->
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
    class="btn btn-success d-inline-flex align-items-center gap-2 px-3"
>
    <template x-if="!downloading">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-file-pdf"></i>
            <span>Aperçu analyse</span>
        </div>
    </template>
    <template x-if="downloading">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Génération...</span>
        </div>
    </template>
</button>


{{-- <button
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
    class="btn text-white"
    style="background-color: #47B173"
>
    <template x-if="!downloading">
        <span>Aperçu analyse</span>
    </template>
    <template x-if="downloading">
        <span>Génération...</span>
    </template>
</button> --}}
