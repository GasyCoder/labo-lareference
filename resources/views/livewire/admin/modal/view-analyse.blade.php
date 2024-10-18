<div wire:ignore.self class="modal fade" id="viewAnalyseModal" tabindex="-1" role="dialog" aria-labelledby="viewAnalyseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title mb-0" id="viewAnalyseModalLabel">Détail de l'analyse</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($viewingAnalyse)
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Informations générales</h6>
                            <p><strong>Code:</strong> {{ $viewingAnalyse->code }}</p>
                            <p><strong>Abréviation:</strong> {{ $viewingAnalyse->abr }}</p>
                            <p><strong>Désignation:</strong> {{ $viewingAnalyse->designation }}</p>
                            <p><strong>Prix:</strong> {{ number_format($viewingAnalyse->prix, 2) }} Ar</p>
                            <p><strong>Niveau:</strong> {{ $viewingAnalyse->level }}</p>
                            <p><strong>Statut:</strong>
                                @if($viewingAnalyse->status)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-danger">Inactif</span>
                                @endif
                            </p>
                            <p><strong>En gras:</strong> {{ $viewingAnalyse->is_bold ? 'Oui' : 'Non' }}</p>
                            <p><strong>Ordre:</strong> {{ $viewingAnalyse->ordre ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Relations</h6>
                            <p><strong>Examen:</strong> {{ $viewingAnalyse->examen->name ?? 'N/A' }}</p>
                            <p><strong>Type d'analyse:</strong> {{ $viewingAnalyse->analyseType->name ?? 'N/A' }}</p>
                            <p><strong>Créé le:</strong> {{ $viewingAnalyse->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Mis à jour le:</strong> {{ $viewingAnalyse->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @if($viewingAnalyse->description)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6>Description:</h6>
                                <p>{{ $viewingAnalyse->description }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="row mt-2">
                        <div class="col-12">
                            <h5 class="mb-3">
                                <i class="fas fa-sitemap me-2"></i>Hiérarchie des analyses
                            </h5>
                            @if(count($analysesHierarchy) > 0)
                                @include('livewire.admin.analyses.analyse-hierarchy', ['analyses' => $analysesHierarchy, 'level' => 0])
                            @else
                                <div class="alert alert-info" role="alert">
                                    <i class="fas fa-info-circle me-2"></i>Aucune analyse subordonnée.
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <p>Aucune analyse sélectionnée.</p>
                @endif
            </div>
        </div>
    </div>
</div>
