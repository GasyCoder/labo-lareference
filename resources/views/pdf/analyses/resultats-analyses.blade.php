{{-- resources/views/pdf/resultats-analyse.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Résultats d'analyses</title>
    @include('pdf.analyses.css')
</head>
<body>
    <div class="header-section">
        <img src="{{ public_path('assets/images/logo.png') }}" alt="LABORATOIRE LA REFERENCE" class="header-logo">
    </div>

    <div class="red-line"></div>
    <div class="content-wrapper">
        {{-- QR CODE ICI --}}
        <div class="doctor-info">
            <img
            src="data:image/png;base64,{{ base64_encode($qrcodeImage) }}"
            alt="QR Code-{{ str_pad($prescription->id, 5, '0', STR_PAD_LEFT) }}"
            class="qr-image"
            width="80"
            height="80">
        </div>
        <div class="patient-info">
            Résultats de : <b>{{ $prescription->patient->sexe }} {{ $prescription->patient->nom. ' ' .$prescription->patient->prenom }}</b><br>
            Age: {{ $prescription->age }} {{ $prescription->unite_age }}<br>
            Réf n° {{ $prescription->patient->formatted_ref ?? 'N/A' }} du {{ $prescription->created_at->format('d/m/Y') }}<br>
            Prescripteur: <b>{{ $prescription->prescripteur->nom ?? 'Non assigné' }}</b>
        </div>

        @foreach($examens as $examen)
            @php
                $hasValidResults = $examen->analyses->some(function($analyse) {
                    return $analyse->resultats->isNotEmpty() &&
                        $analyse->resultats->some(function($resultat) {
                            return !empty($resultat->valeur);
                        });
                });
            @endphp
            <table class="main-table">
                <tr>
                    <td class="col-designation section-title">{{ strtoupper($examen->name) }}</td>
                    <td class="col-resultat header-cols">Résultat</td>
                    <td class="col-valref header-cols">Val Réf</td>
                    <td class="col-anteriorite header-cols">Anteriorité</td>
                </tr>
            </table>
            <div class="red-line"></div>
            <div class="result-spacing"></div>

            <table class="main-table">
                @foreach($examen->analyses as $analyse)
                    @if($analyse->level_value === 'PARENT' || !$analyse->parent_code)

                    @include('pdf.analyses.analyse-row', ['analyse' => $analyse, 'level' => +1])

                    @if($analyse->children && $analyse->children->count() > 0)
                        @include('pdf.analyses.analyse-children', ['children' => $analyse->children, 'level' => +2])
                    @endif
                @endif
                @endforeach
            </table>

            {{-- @include('pdf.analyses.description') --}}

            @endforeach
        <!-- Signature en bas à droite -->
        <div style="margin-top: 20px; text-align: right; padding-right: 40px;">
            <img src="{{ public_path('assets/images/signature.png') }}" alt="Signature" style="max-width: 180px;">
        </div>
    </div>
</body>
</html>
