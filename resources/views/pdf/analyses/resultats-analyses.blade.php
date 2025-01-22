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
        {{-- En-tête reste identique --}}
        @include('pdf.analyses.header')
        @foreach($examens as $examen)
            @php
                $hasValidResults = $examen->analyses->some(function($analyse) {
                    return $analyse->resultats->isNotEmpty() &&
                        $analyse->resultats->some(function($resultat) {
                            return !empty($resultat->valeur);
                        });
                });
            @endphp

            {{-- En-tête du tableau --}}
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

            {{-- Contenu principal --}}
            <table class="main-table">
                @foreach($examen->analyses as $analyse)
                    @if($analyse->level_value === 'PARENT' || !$analyse->parent_code)
                        {{-- Afficher l'analyse parent --}}
                        @include('pdf.analyses.analyse-row', ['analyse' => $analyse, 'level' => +1])

                        {{-- Afficher ses enfants --}}
                        @if($analyse->children && $analyse->children->count() > 0)
                            @include('pdf.analyses.analyse-children', ['children' => $analyse->children, 'level' => +2])
                        @endif
                    @endif
                    {{-- @include('pdf.analyses.description') --}}
                @endforeach

            </table>
                  @include('pdf.analyses.conclusion')
        @endforeach

        <!-- Signature -->
        <div style="margin-top: 20px; text-align: right; padding-right: 40px;">
            <img src="{{ public_path('assets/images/signature.png') }}" alt="Signature" style="max-width: 180px;">
        </div>
    </div>
</body>
</html>
