<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture #{{ str_pad($prescription->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            line-height: 1.6;
        }
        .header {
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #4a5568;
            color: white;
        }
        .section-header {
            background-color: #e2e8f0;
            font-weight: bold;
        }
        .price-column {
            text-align: right;
            width: 150px;
        }
        .quantity-column {
            text-align: center;
            width: 100px;
        }
        .total-row {
            background-color: #f8fafc;
            font-weight: bold;
        }
        .grand-total {
            background-color: #2b6cb0;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Facture #{{ str_pad($prescription->id, 5, '0', STR_PAD_LEFT) }}</h1>
        <p>
            <strong>Patient:</strong> {{ $prescription->patient->nom }} {{ $prescription->patient->prenom }}<br>
            <strong>Date:</strong> {{ now()->format('d/m/Y') }}
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Désignation</th>
                <th>Quantité</th>
                <th>Prix</th>
            </tr>
        </thead>
        <tbody>
            <!-- Analyses -->
            <tr class="section-header">
                <td colspan="3">Analyses</td>
            </tr>
            @foreach($prescription->analyses as $analyse)
            <tr>
                <td>{{ $analyse->designation }}</td>
                <td class="quantity-column">1</td>
                <td class="price-column">{{ number_format($analyse->pivot->prix, 0, ',', ' ') }} Ar</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2">Sous-total Analyses</td>
                <td class="price-column">{{ number_format($totalAnalyses, 0, ',', ' ') }} Ar</td>
            </tr>

            <!-- Prélèvements -->
            @if($prescription->prelevements->count() > 0)
                <tr class="section-header">
                    <td colspan="3">Prélèvements</td>
                </tr>
                @foreach($prescription->prelevements as $prelevement)
                <tr>
                    <td>{{ $prelevement->nom }}</td>
                    <td class="quantity-column">{{ $prelevement->pivot->quantite }}</td>
                    <td class="price-column">
                        @if($prelevement->nom === $TUBE_AIGUILLE_NOM)
                            @if($prelevement->pivot->quantite > 1)
                                {{ number_format($elevatedPrelevementPrice, 0, ',', ' ') }} Ar
                            @else
                                {{ number_format($basePrelevementPrice, 0, ',', ' ') }} Ar
                            @endif
                        @else
                            {{ number_format($prelevement->pivot->prix_unitaire * $prelevement->pivot->quantite, 0, ',', ' ') }} Ar
                        @endif
                    </td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="2">Sous-total Prélèvements</td>
                    <td class="price-column">{{ number_format($totalPrelevements, 0, ',', ' ') }} Ar</td>
                </tr>
            @endif

            <!-- Total Général -->
            <tr class="grand-total">
                <td colspan="2">Total Général</td>
                <td class="price-column">{{ number_format($totalGeneral, 0, ',', ' ') }} Ar</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p><small>Facture générée le {{ now()->format('d/m/Y à H:i') }}</small></p>
        @if($prescription->prescripteur)
            <p><small>Prescrit par: Dr. {{ $prescription->prescripteur->nom }}</small></p>
        @endif
    </div>
</body>
</html>
