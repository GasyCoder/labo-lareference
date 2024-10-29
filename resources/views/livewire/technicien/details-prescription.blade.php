<div>
    <div class="flex">
        <div class="w-1/4 pr-4">
            <h3 class="text-lg font-semibold mb-2">Toutes les analyses</h3>
            <ul>
                @foreach($topLevelAnalyses as $analyse)
                    <li>
                        <button wire:click="selectParentAnalyse({{ $analyse->id }})"
                                class="block w-full text-left py-2 px-4 hover:bg-gray-100
                                       {{ $selectedParentAnalyse && $selectedParentAnalyse->id == $analyse->id ? 'bg-blue-100' : '' }}">
                            {{ $analyse->designation }}
                        </button>
                    </li>
                @endforeach

                @if($validation)
                    <button wire:click="validateAnalyse({{ $analyse->id }})"
                                class="btn btn-success">
                        Valider <i class="fas fa-check ms-2"></i>
                    </button>
                @endif
            </ul>
        </div>
        <div class="w-3/4">
            @if($showForm)
                @if($selectedParentAnalyse)
                    <h2 class="text-2xl font-bold mb-4">{{ $selectedParentAnalyse->designation }}</h2>
                    
                    @include('livewire.technicien.partials.analyse-recursive', ['analyses' => $selectedParentAnalyse->children,'bacteries' => $showBactery])
                    
                    <button wire:click="saveResult({{ $selectedParentAnalyse->id }})"
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 mt-4">
                        Enregistrer
                    </button>
                @else
                    <p>Veuillez sélectionner une analyse dans la liste de gauche.</p>
                @endif
            @endif
        </div>
    </div>
</div>
