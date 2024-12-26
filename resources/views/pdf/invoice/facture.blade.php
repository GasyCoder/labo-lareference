<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture #{{ str_pad($prescription->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            size: B5;
            margin: 1.5cm 2cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        .header {
            position: relative;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
        }

        .logo-container {
            width: 150%;
            display: flex;
            align-items: flex-start;
        }

        .logo {
            max-width: 500px;
            height: auto;
            object-fit: contain;
            object-position: left top;
            margin: 0;
            padding: 0;
            display: block;
        }

        .contact-info {
            width: 40%;
            text-align: right;
            font-size: 9px;
            line-height: 1.4;
            color: #444;
        }

        .invoice-details {
            margin: 20px 0;
        }

        .invoice-number {
            font-size: 13px;
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
        }

        .status-paid {
            position: absolute;
            top: 70px;
            right: 0;
            color: #059669;
            font-size: 24px;
            font-weight: bold;
            transform: rotate(-10deg);
        }

        .patient-info {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #fff;
            border: 1px solid #eee;
        }

        .patient-info h3 {
            margin: 0 0 8px 0;
            font-size: 12px;
            color: #444;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        th, td {
            border: 1px solid #eee;
            padding: 5px 8px;
            font-size: 10px;
        }

        th {
            background-color: #fff;
            font-weight: bold;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .subtotal {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .total {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 12px;
        }

        .footer {
            margin-top: 20px;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        .designation {
            font-size: 10px;
        }

        .abr {
            font-size: 9px;
            color: #666;
        }

        .amount {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <img src="{{ public_path('assets/images/logo.png') }}" alt="LABORATOIRE LA REFERENCE" class="logo">
        </div>
        {{-- <div class="contact-info">
            Tél Bureau : 261 34 53 211 41<br>
            Tél Urgences : 261 34 76 637 92<br>
            Manjavaivola<br>
            MAHAJANGA 401
        </div> --}}
    </div>

    <div class="invoice-details">
        <div class="invoice-number">
            N° {{ str_pad($prescription->id, 5, '0', STR_PAD_LEFT) }}
        </div>
        <div>Date: {{ now()->format('d/m/Y') }}</div>
    </div>

    @if($allPaid)
        <div class="status-paid">PAYÉ</div>
    @endif

    <div class="patient-info">
        <h3>Informations du patient</h3>
        <table style="border: none; margin: 0;">
            <tr>
                <td style="border: none; padding: 2px;"><strong>Réf:</strong> {{ $prescription->patient->formatted_ref }}</td>
                <td style="border: none; padding: 2px;"><strong>Nom:</strong> {{ $prescription->patient->nom }}</td>
            </tr>
            <tr>
                <td style="border: none; padding: 2px;"><strong>Prénom:</strong> {{ $prescription->patient->prenom }}</td>
                <td style="border: none; padding: 2px;">
                    @if($prescription->patient->telephone)
                        <strong>Tél:</strong> {{ $prescription->patient->telephone }}
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th width="45%">Désignation</th>
                <th width="10%">Qté</th>
                <th width="20%">P.U</th>
                <th width="25%">Total</th>
            </tr>
        </thead>
        <tbody>
                <!-- Analyses -->
                @foreach($prescription->analyses as $analyse)
                <tr>
                    <td>
                        {{ $analyse->designation }}
                        <small>({{ $analyse->abr }})</small>
                    </td>
                    <td class="text-right">1</td>
                    <td class="text-right">{{ number_format($analyse->pivot->prix, 0, ',', ' ') }} Ar</td>
                    <td class="text-right">{{ number_format($analyse->pivot->prix, 0, ',', ' ') }} Ar</td>
                </tr>
                @endforeach
                <tr class="subtotal">
                    <td colspan="3">Sous-total Analyses</td>
                    <td class="text-right">{{ number_format($totalAnalyses, 0, ',', ' ') }} Ar</td>
                </tr>

                <!-- Prélèvements -->
                @if($prescription->prelevements->count() > 0)
                    @foreach($prescription->prelevements as $prelevement)
                        @php
                            $quantite = $prelevement->pivot->quantite;
                            $isTubeAiguille = $prelevement->nom === $TUBE_AIGUILLE_NOM;
                            $prixUnitaire = $isTubeAiguille ?
                                ($quantite > 1 ? $elevatedPrelevementPrice : $basePrelevementPrice) :
                                $prelevement->pivot->prix_unitaire;
                            $prixTotal = $isTubeAiguille ?
                                ($quantite > 1 ? $elevatedPrelevementPrice : $basePrelevementPrice) :
                                ($prelevement->pivot->prix_unitaire * $quantite);
                        @endphp
                        <tr>
                            <td>{{ $prelevement->nom }}</td>
                            <td class="text-right">{{ $quantite }}</td>
                            <td class="text-right">{{ number_format($prixUnitaire, 0, ',', ' ') }} Ar</td>
                            <td class="text-right">{{ number_format($prixTotal, 0, ',', ' ') }} Ar</td>
                        </tr>
                    @endforeach
                    <tr class="subtotal">
                        <td colspan="3">Sous-total Prélèvements</td>
                        <td class="text-right">{{ number_format($totalPrelevements, 0, ',', ' ') }} Ar</td>
                    </tr>
                @endif

                <tr class="total">
                    <td colspan="3"><strong>TOTAL GÉNÉRAL</strong></td>
                    <td class="text-right"><strong>{{ number_format($totalGeneral, 0, ',', ' ') }} Ar</strong></td>
                </tr>
            </tbody>
    </table>

    <div class="footer">
        <table style="border: none;">
            <tr>
                <td style="border: none; text-align: left;">
                    Facture générée le {{ now()->format('d/m/Y à H:i') }}
                    @if($prescription->prescripteur)
                        <br>Prescrit par: Dr. {{ $prescription->prescripteur->nom }}
                    @endif
                </td>
                <td style="border: none; text-align: right;">
                    Mode de paiement: {{ strtoupper($modePaiement) }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
