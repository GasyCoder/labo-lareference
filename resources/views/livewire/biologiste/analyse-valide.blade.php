<div class="container-fluid py-4">
    <section class="container-fluid p-4">
        <div class="card shadow-sm border-0 rounded-lg">
            <div class="card-body">
                <!-- Barre de recherche -->
                <div class="mb-4">
                    <input type="text" wire:model.debounce.300ms="search" class="form-control"
                        placeholder="Rechercher par nom, prescripteur ou renseignement clinique...">
                </div>

                <!-- Onglets -->
                <ul class="nav nav-tabs mb-4">
                    <li class="nav-item">
                        <button class="nav-link @if($tab === 'termine') active @endif"
                                wire:click="$set('tab', 'termine')">
                            <i class="fas fa-list-ul me-2"></i> Terminé
                            <span class="badge bg-primary">{{ $analyseTermines->total() }}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link @if($tab === 'valide') active @endif"
                                wire:click="$set('tab', 'valide')">
                            <i class="fas fa-check-circle me-2"></i> Validé
                            <span class="badge bg-success">{{ $analyseValides->total() }}</span>
                        </button>
                    </li>
                </ul>

                <!-- Inclusion des tableaux -->
                @if($tab === 'termine')
                    @include('livewire.biologiste.partials.analyse-card', [
                        'prescriptions' => $analyseTermines,
                        'statusLabel' => 'Terminé',
                        'statusColor' => 'rgb(234, 88, 12)'
                    ])
                @elseif($tab === 'valide')
                    @include('livewire.biologiste.partials.analyse-card', [
                        'prescriptions' => $analyseValides,
                        'statusLabel' => 'Validé',
                        'statusColor' => 'rgb(22, 163, 74)'
                    ])
                @endif
            </div>
        </div>
    </section>
</div>
