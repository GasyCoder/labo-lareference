@script
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('openPdfInNewWindow', ({ url }) => {
            if (!url) {
                console.error('URL manquante pour le PDF');
                return;
            }

            console.log('Tentative d\'ouverture du PDF:', url);

            try {
                const pdfWindow = window.open(url, '_blank', 'noopener,noreferrer');

                if (!pdfWindow || pdfWindow.closed || typeof pdfWindow.closed === 'undefined') {
                    console.warn('Popup bloqué, tentative d\'ouverture dans le même onglet');
                    window.location.href = url;
                }
            } catch (error) {
                console.error('Erreur lors de l\'ouverture du PDF:', error);
                window.location.href = url;
            }
        });

        Livewire.on('error', (message) => {
            alert(message);
        });
    });
</script>
@endscript
