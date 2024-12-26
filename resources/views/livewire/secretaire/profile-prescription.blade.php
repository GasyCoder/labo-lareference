<section class="container-fluid p-4">
    <!-- Header avec statut -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-2">
                                    <li class="breadcrumb-item"><a href="#">Secrétaire</a></li>
                                    <li class="breadcrumb-item"><a href="{{route('secretaire.patients.index')}}">Prescriptions</a></li>
                                    <li class="breadcrumb-item active">Détail</li>
                                </ol>
                            </nav>
                            <h1 class="h2 mb-0">Prescription #{{ str_pad($prescription->id, 5, '0', STR_PAD_LEFT) }}</h1>
                        </div>

                        <!-- Statut de la prescription -->
                        <div class="d-flex gap-3 align-items-center">
                            <a href="{{ route('secretaire.patients.index') }}"
                               class="btn btn-warning">
                               <i class="fas fa-arrow-left me-2"></i>Retour au précédent
                            </a>

                            <a href="{{ route('secretaire.prescriptions.edit', ['id' => $prescription->id]) }}"
                                class="btn btn-success">
                                 <i class="fas fa-edit me-2"></i>Modifier
                             </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Colonne principale -->
        <div class="col-lg-8">
            <!-- Infos patient -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="d-flex gap-4">
                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center"
                                style="width: 80px; height: 80px;">
                                <span class="fs-3 text-white fw-bold">
                                    {{ strtoupper(substr($prescription->patient->nom, 0, 1) . substr($prescription->patient->prenom, 0, 1)) }}
                                </span>
                            </div>
                            <div>
                                <h3>{{ $prescription->patient->nom }} {{ $prescription->patient->prenom }}</h3>
                                <div class="text-muted">
                                    <i class="fas fa-calendar me-2"></i>
                                    Patient depuis {{ $prescription->patient->created_at->format('d/m/Y') }}
                                </div>
                            </div>
                        </div>

                        @php
                            $allValidated = !$prescription->analyses->contains(function($analyse) {
                                return !$analyse->resultats->where('validated_by', '!=', null)->count();
                            });
                        @endphp

                        @if($allValidated)
                            <div class="d-flex align-items-start">
                                <span class="d-flex align-items-center gap-2">
                                    <span class="badge bg-{{ $allValidated ? 'success' : 'warning' }} fs-6 px-3 py-2">
                                        <i class="fas fa-{{ $allValidated ? 'check-circle' : 'clock' }} me-2"></i>
                                        {{ $allValidated ? 'Validé' : 'En attente' }}
                                    </span>
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- Informations médicales -->
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center">
                                <div class="text-muted mb-2">Âge</div>
                                <div class="h4 mb-0">{{ $prescription->age }} {{ $prescription->unite_age }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center">
                                <div class="text-muted mb-2">Poids</div>
                                <div class="h4 mb-0">{{ $prescription->poids ?? 'N/A' }} kg</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center">
                                <div class="text-muted mb-2">Préscripteur</div>
                                <div class="h4 mb-0">
                                    {{ $prescription->prescripteur ? $prescription->prescripteur->name : ($prescription->nouveau_prescripteur_nom ?? 'Non assigné') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Analyses -->
        <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h4 class="card-title mb-0">Analyses prescrites</h4>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Analyse</th>
                                <th>Prix</th>
                                <th>Date</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prescription->analyses as $analyse)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-medium">{{ $analyse->designation }}</span>
                                            <span class="badge bg-primary">{{ $analyse->abr }}</span>
                                        </div>
                                    </td>
                                    <td>{{ number_format($analyse->pivot->prix, 0, ',', ' ') }} Ar</td>
                                    <td>{{ $analyse->pivot->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge" style="background-color: #34D399; font-weight: normal;">
                                            {{$prescription->status}}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach

                            <!-- Section des prélèvements -->
                            @if($prescription->prelevements->count() > 0)
                            <tr>
                                <td colspan="4" class="bg-light">
                                    <strong>Prélèvements</strong>
                                </td>
                            </tr>
                            @foreach($prescription->prelevements as $prelevement)
                                @php
                                    $quantite = $prelevement->pivot->quantite;
                                    $isTubeAiguille = $prelevement->nom === 'Tube aiguille';
                                    $prixTotal = $isTubeAiguille && $quantite > 1 ?
                                        3500 :
                                        ($isTubeAiguille ? 2000 : $prelevement->pivot->prix_unitaire * $quantite);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-medium">{{ $prelevement->nom }}</span>
                                            @if($quantite > 1)
                                                <span class="badge bg-info">Qté: {{ $quantite }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        {{ number_format($prixTotal, 0, ',', ' ') }} Ar
                                        @if($quantite > 1 && !$isTubeAiguille)
                                            <small class="text-muted">
                                                ({{ number_format($prelevement->pivot->prix_unitaire, 0, ',', ' ') }} × {{ $quantite }})
                                            </small>
                                        @endif
                                    </td>
                                    <td>{{ $prelevement->pivot->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-success">Effectué</span>
                                    </td>
                                </tr>
                            @endforeach
                            @endif

                            <!-- Pied de tableau -->
                            <tfoot class="table-light">
                            <tr>
                                <th>Total Analyses</th>
                                <th colspan="3">{{ number_format($totalAnalyses, 0, ',', ' ') }} Ar</th>
                            </tr>
                            @if($prescription->prelevements->count() > 0)
                                <tr>
                                    <th>Total Prélèvements</th>
                                    <th colspan="3">{{ number_format($totalPrelevements, 0, ',', ' ') }} Ar</th>
                                </tr>
                                <tr class="table-primary">
                                    <th>Total Général</th>
                                    <th colspan="3">{{ number_format($totalGeneral, 0, ',', ' ') }} Ar</th>
                                </tr>
                            @endif
                            </tfoot>

                    </table>
                </div>
            </div>
        </div>

        <!-- Colonne latérale -->
        <div class="col-lg-4">
            <!-- Contact -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Contact</h5>
                    <div class="d-flex flex-column gap-3">
                        @if($prescription->patient->email)
                            <div class="d-flex align-items-center">
                                <i class="fas fa-envelope text-primary me-3"></i>
                                <a href="mailto:{{ $prescription->patient->email }}">{{ $prescription->patient->email }}</a>
                            </div>
                        @endif
                        @if($prescription->patient->telephone)
                            <div class="d-flex align-items-center">
                                <i class="fas fa-phone text-primary me-3"></i>
                                {{ $prescription->patient->telephone }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-cogs me-2 text-primary"></i>Actions
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Facture -->
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <button wire:click="generateFacturePDF"
                                class="btn btn-warning text-white flex-grow-1 d-flex align-items-center justify-content-center"
                                style="background-color: #ddb215; height: 42px;"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="generateFacturePDF">
                                <i class="fas fa-file-invoice-dollar me-2"></i>Générer la facture
                            </span>
                            <span wire:loading wire:target="generateFacturePDF">
                                <i class="fas fa-spinner fa-spin me-2"></i>Génération en cours...
                            </span>
                        </button>
                        <button wire:click="sendFactureEmail"
                                class="btn btn-success d-flex align-items-center justify-content-center"
                                style="width: 42px; height: 42px; border-radius: 21px;"
                                title="Envoyer la facture par email"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="sendFactureEmail">
                                <i class="fas fa-paper-plane"></i>
                            </span>
                            <span wire:loading wire:target="sendFactureEmail">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </button>
                    </div>

                    {{-- <!-- Résultats d'analyses -->
                    @if($prescription->resultats->where('validated_by', '!=', null)->count() > 0)
                        <div class="d-flex align-items-center gap-2">
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
                                class="btn flex-grow-1 d-flex align-items-center justify-content-center"
                                style="background-color: #8B5CF6; color: white; height: 42px;">
                                <template x-if="!downloading">
                                    <span class="d-flex align-items-center">
                                        <i class="fas fa-file-medical me-2"></i>
                                        Résultats d'analyses
                                    </span>
                                </template>
                                <template x-if="downloading">
                                    <span class="d-flex align-items-center">
                                        <i class="fas fa-spinner fa-spin me-2"></i>
                                        Génération en cours...
                                    </span>
                                </template>
                            </button>
                            <button
                                class="btn btn-success d-flex align-items-center justify-content-center"
                                style="width: 42px; height: 42px; border-radius: 21px;"
                                title="Envoyer les résultats par email">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    @endif --}}

                </div>
            </div>

        </div>
    </div>
</section>
@include('layouts.scripts')
