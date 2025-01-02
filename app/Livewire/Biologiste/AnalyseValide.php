<?php

namespace App\Livewire\Biologiste;

use Livewire\Component;
use App\Models\Prescription;
use Livewire\WithPagination;

class AnalyseValide extends Component
{
   use WithPagination;

   protected $paginationTheme = 'bootstrap';
   public $tab = 'termine';
   public $search = '';

   protected $queryString = [
       'search',
       'tab' => ['except' => 'termine'],
   ];

   public function updatingSearch()
   {
       $this->resetPage();
   }

   public function updatedTab()
   {
       $this->resetPage();
   }

   public function openAnalyse($prescriptionId)
   {
       $prescription = Prescription::findOrFail($prescriptionId);
       return $this->redirect(route('biologiste.valide.show', ['prescription' => $prescription]));
   }

   public function render()
   {
       $search = '%' . $this->search . '%';

       $baseQuery = Prescription::with([
           'patient',
           'prescripteur:id,nom,is_active',
           'analyses'
       ])
       ->whereHas('patient', fn($q) => $q->whereNull('deleted_at'));

       $searchCondition = function($query) use ($search) {
           $query->where('renseignement_clinique', 'like', $search)
               ->orWhere('status', 'like', $search)
               ->orWhereHas('patient', function($q) use ($search) {
                   $q->where('nom', 'like', $search)
                       ->orWhere('prenom', 'like', $search)
                       ->orWhere('telephone', 'like', $search);
               })
               ->orWhereHas('prescripteur', function($q) use ($search) {
                   $q->where('nom', 'like', $search)
                       ->where('is_active', true);
               });
       };

       $analyseValides = (clone $baseQuery)
           ->where('status', Prescription::STATUS_VALIDE)
           ->where($searchCondition)
           ->oldest()
           ->paginate(15);

       $analyseTermines = (clone $baseQuery)
           ->where('status', Prescription::STATUS_TERMINE)
           ->where($searchCondition)
           ->oldest()
           ->paginate(15);

       return view('livewire.biologiste.analyse-valide', compact('analyseValides', 'analyseTermines'));
   }
}
