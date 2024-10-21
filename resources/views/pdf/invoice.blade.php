<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Facture</h1>
    <p>
        <strong>Patient:</strong> {{ $prescription->patient->nom }} {{ $prescription->patient->prenom }}<br>
        <strong>Date:</strong> {{ now()->format('d/m/Y') }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Analyse</th>
                <th>Prix</th>
            </tr>
        </thead>
        <tbody>
            @foreach($prescription->analyses as $analyse)
            <tr>
                <td>{{ $analyse->designation }}</td>
                <td>{{ $analyse->pivot->prix }} €</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th>Total</th>
                <th>{{ $prescription->analyses->sum('pivot.prix') }} €</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
