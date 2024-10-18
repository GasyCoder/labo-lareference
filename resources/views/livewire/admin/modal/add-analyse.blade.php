<div wire:ignore.self class="modal fade" id="newAnalyse" tabindex="-1" role="dialog" aria-labelledby="newAnalyseLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title mb-0" id="newAnalyseLabel">{{ $editingAnalyseId ? 'Modifier' : 'Ajouter' }} un analyse</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form wire:submit="save" class="row gx-3 needs-validation" novalidate>
                    <div class="mb-3 col-md-6">
                        <label for="abr" class="form-label">Abreviation <span class="text-danger">*</span></label>
                        <input wire:model="abr" type="text" class="form-control" id="abr" required>
                        @error('abr') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="level" class="form-label">Niveau <span class="text-danger">*</span></label>
                        <select wire:model="level" class="form-select" id="level" required>
                            @foreach(App\Enums\AnalyseLevel::cases() as $level)
                                <option value="{{ $level->value }}">{{ $level->name }}</option>
                            @endforeach
                        </select>
                        @error('level') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    @if($level !== App\Enums\AnalyseLevel::PARENT)
                    <div class="mb-3 col-md-12">
                        <label for="parent_id" class="form-label">Parent</label>
                        <select wire:model="parent_id" class="form-select" id="parent_id">
                            <option value="">Sélectionner un parent</option>
                            @foreach(App\Models\Analyse::where('level', App\Enums\AnalyseLevel::PARENT)->get() as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->designation }}</option>
                            @endforeach
                        </select>
                        @error('parent_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    @endif

                    <div class="mb-3 col-md-6">
                        <label for="examen_id" class="form-label">Examen <span class="text-danger">*</span></label>
                        <select wire:model="examen_id" class="form-select" id="examen_id" required>
                            <option value="">Sélectionner un examen</option>
                            @foreach($examens as $examen)
                                <option value="{{ $examen->id }}">{{ $examen->name }}</option>
                            @endforeach
                        </select>
                        @error('examen_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="analyse_type_id" class="form-label">Type d'analyse <span class="text-danger">*</span></label>
                        <select wire:model="analyse_type_id" class="form-select" id="analyse_type_id" required>
                            <option value="">Sélectionner un type d'analyse</option>
                            @foreach($analyseTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                        @error('analyse_type_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3 col-12">
                        <label for="designation" class="form-label">Désignation <span class="text-danger">*</span></label>
                        <input wire:model="designation" type="text" class="form-control" id="designation" required>
                        @error('designation') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3 col-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea wire:model="description" class="form-control" id="description" rows="3"></textarea>
                        @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="prix" class="form-label">Prix</label>
                        <input wire:model="prix" type="number" step="0.01" class="form-control" id="prix">
                        @error('prix') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="ordre" class="form-label">Ordre</label>
                        <input wire:model="ordre" type="number" class="form-control" id="ordre">
                        <small class="form-text text-muted">Laissez vide pour l'ordre par défaut (99)</small>
                        @error('ordre') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3 col-md-12">
                        <label for="result_disponible" class="form-label">Résultats disponibles</label>
                        <textarea wire:model="result_disponible" class="form-control" id="result_disponible" rows="3"></textarea>
                        <small class="form-text text-muted">Entrez les résultats disponibles au format JSON</small>
                        @error('result_disponible') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label">Statut</label>
                        <div class="form-check form-switch">
                            <input wire:model="status" class="form-check-input" type="checkbox" id="status">
                            <label class="form-check-label" for="status">
                                {{ $status ? 'Actif' : 'Inactif' }}
                            </label>
                        </div>
                        @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label">Mise en gras</label>
                        <div class="form-check form-switch">
                            <input wire:model="is_bold" class="form-check-input" type="checkbox" id="is_bold">
                            <label class="form-check-label" for="is_bold">
                                {{ $is_bold ? 'En gras' : 'Normal' }}
                            </label>
                        </div>
                        @error('is_bold') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary">
                            {{ $editingAnalyseId ? 'Mettre à jour' : 'Ajouter' }} l'analyse
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
