<section class="container-fluid p-4">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <!-- Page header -->
            <div class="border-bottom pb-3 mb-3 d-flex flex-md-row flex-column gap-3 align-items-md-center justify-content-between">
                <div>
                    <h1 class="mb-0 h2 fw-bold">Détail de la prescription</h1>
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="#">Secrétaire</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{route('secretaire.patients.index')}}">Liste des prescriptions</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Détail</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('secretaire.prescriptions.edit', ['id' => $prescription->id]) }}"
                        class="btn btn-success me-2"><i class="fas fa-edit me-2"></i>Modifier</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row gy-4">
        <div class="col-lg-8 col-12">
            <div class="d-flex flex-column gap-4">
                <!-- Informations du patient -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center flex-row gap-4">
                            <div class="rounded-circle bg-{{ $prescription->isArchived() ? 'secondary' : 'primary' }} text-white d-flex justify-content-center align-items-center shadow-sm" style="width: 80px; height: 80px; flex-shrink: 0;">
                                <span class="fs-4 fw-bold">
                                    {{ strtoupper(substr($prescription->patient->nom, 0, 1) . substr($prescription->patient->prenom, 0, 1)) }}
                                </span>
                            </div>
                            <div class="d-flex flex-column gap-1">
                                <h3 class="mb-0">{{ $prescription->patient->nom }} {{ $prescription->patient->prenom }}</h3>
                                <div class="d-flex flex-row align-items-center gap-3">
                                    <span class="d-flex flex-row gap-2">
                                        <span><i class="fe fe-calendar"></i></span>
                                        <span>Patient depuis {{ $prescription->patient->created_at->format('d/m/Y') }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body border-top">
                        <div class="hstack gap-2 d-flex flex-column flex-xl-row d-inline align-items-start align-items-xl-center justify-content-between">
                            <div class="d-flex flex-column gap-2 mb-3">
                                <span class="fw-semibold">Âge</span>
                                <div class="">
                                    <h5 class="h3 fw-bold mb-0">{{ $prescription->age }} {{ $prescription->unite_age }}</h5>
                                </div>
                            </div>
                            <div class="d-flex flex-column gap-2 mb-3">
                                <span class="fw-semibold">Poids</span>
                                <div class="">
                                    <h5 class="h3 fw-bold mb-0">{{ $prescription->poids }} kg</h5>
                                </div>
                            </div>
                            <div class="d-flex flex-column gap-2 mb-3">
                                <span class="fw-semibold">Préscripteur</span>
                                <div class="">
                                    <h5 class="h3 fw-bold mb-0">
                                        {{ $prescription->prescripteur ? $prescription->prescripteur->name : ($prescription->nouveau_prescripteur_nom ?? 'Non assigné') }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Renseignement clinique</h4>
                        </div>
                        <div>
                            <p class="mb-0">{!! $prescription->renseignement_clinique !!}</p>
                        </div>
                    </div>
                </div>

                <!-- Analyses prescrites -->
                <div class="col-lg-12 col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Analyses prescrites</h4>
                        </div>
                        <div class="card-body">
                            @foreach($prescription->analyses as $analyse)
                                <span class="badge bg-primary me-2 mb-2">
                                    {{ $analyse->abr }}
                                </span>
                            @endforeach
                        </div>
                        <div class="table-responsive">
                            <table class="table mb-0 text-nowrap table-centered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Désignation</th>
                                        <th>Prix</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($prescription->analyses as $analyse)
                                    <tr>
                                        <td>{{ $analyse->designation }}</td>
                                        <td>{{ $analyse->pivot->prix }} Ar</td>
                                        <td>{{ $analyse->pivot->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $analyse->pivot->status == 'TERMINE' ? 'success' : 'warning' }}-soft">
                                                {{ $analyse->pivot->status }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="1">Total</th>
                                        <th>{{ $prescription->analyses->sum('pivot.prix') }} Ar</th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="col-lg-4">
            <!-- Informations de contact -->
            <div class="card">
                <div class="card-body border-bottom d-flex flex-column gap-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Contact</h4>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex align-items-center">
                            <i class="fe fe-mail fs-4"></i>
                            <a href="mailto:{{ $prescription->patient->email ?? 'Non renseigné' }}" class="ms-2">{{ $prescription->patient->email }}</a>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fe fe-phone fs-4"></i>
                            <span class="ms-2">{{ $prescription->patient->telephone ?? 'Non renseigné' }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body d-flex flex-column gap-4">
                    <livewire:secretaire.generate-invoice :prescription-id="$prescription->id" />
                </div>
            </div>

            <!-- Informations de contact -->
            <div class="card mt-3">
                <div class="card-body border-bottom d-flex flex-column gap-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Service emailing</h4>
                    </div>
                </div>
                <div class="card-body d-flex flex-column gap-4">
                    {{-- avec condition si analyse prescription a été payé  --}}
                    <a href="#" class="btn btn-info">
                        <i class="fe fe-send me-2"></i> Envoyer facture PDF
                    </a>
                    {{-- avec condition si analyse prescription a été validé  --}}
                    <a href="#" class="btn btn-success">
                        <i class="fe fe-send me-2"></i> Envoyer Résultats analyse
                    </a>
                </div>
            </div>
        </div>

    </div>
</section>
