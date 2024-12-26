<div>
    <h1>Liste des Examens avec Analyses</h1>

    @if (session()->has('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Nom</th>
                <th>Analyses</th>
            </tr>
        </thead>
        <tbody>
            @forelse($examens as $examen)
                <tr>
                    <td>{{ $examen->id }}</td>
                    <td>{{ $examen->name }}</td>
                    <td>
                        <ul>
                            @foreach ($examen->analyses as $analyse)
                                <li>
                                    {{ $analyse->designation }}
                                    (ID: {{ $analyse->id }})
                                </li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">Aucun examen trouvé.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
