<?php

namespace App\Livewire\Biologiste;

use Livewire\Component;
use App\Models\Resultat;
use App\Models\Prescription;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\AnalysePrescription;
use Illuminate\Support\Facades\Log;
use App\Services\ResultatPdfService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class AnalyseValide extends Component
{
   use WithPagination;
   use LivewireAlert;

   protected $paginationTheme = 'bootstrap';
   public $tab = 'termine';
   public $search = '';

   protected $queryString = [
       'search',
       'tab' => ['except' => 'termine'],
   ];

   protected $pdfService;

   public function boot(ResultatPdfService $pdfService)
   {
       $this->pdfService = $pdfService;
   }

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

   public function generateResultatsPDF($prescriptionId)
   {
       try {
           $prescription = Prescription::findOrFail($prescriptionId);

           // Le service retourne directement l'URL
           return $this->pdfService->generatePDF($prescription);

       } catch (\Exception $e) {
           Log::error('Erreur génération PDF:', [
               'message' => $e->getMessage(),
               'prescription_id' => $prescriptionId,
               'trace' => $e->getTraceAsString()
           ]);

           $this->alert('error', "Erreur lors de la génération du PDF : {$e->getMessage()}");
           return null;
       }
   }

   private function collectChildAnalyseIds($analyse, &$allAnalyseIds)
   {
       if ($analyse->children) {
           foreach ($analyse->children as $child) {
               $allAnalyseIds->push($child->id);
               $this->collectChildAnalyseIds($child, $allAnalyseIds);
           }
       }
   }

   public function validateAnalyse($prescriptionId)
   {
       try {
           DB::beginTransaction();

           $prescription = Prescription::findOrFail($prescriptionId);

           // Récupérer toutes les analyses (parents et enfants)
           $parentAnalyses = $prescription->analyses()
               ->with(['children'])
               ->get();

           $allAnalyseIds = collect();

           // Collecter tous les IDs des analyses (parents et enfants)
           foreach ($parentAnalyses as $parentAnalyse) {
               $allAnalyseIds->push($parentAnalyse->id);
               $this->collectChildAnalyseIds($parentAnalyse, $allAnalyseIds);
           }

           // Mettre à jour les résultats
           Resultat::where('prescription_id', $prescriptionId)
               ->whereIn('analyse_id', $allAnalyseIds)
               ->update([
                   'validated_by' => Auth::id(),
                   'validated_at' => now(),
                   'status' => Resultat::STATUS_VALIDE
               ]);

           // Mettre à jour les statuts des analyses pivot
           // Les analyses déjà TERMINE restent TERMINE, seules les analyses parentes sont VALIDE
           foreach ($parentAnalyses as $parentAnalyse) {
               // Mettre à jour l'analyse parent en VALIDE
               AnalysePrescription::where([
                   'prescription_id' => $prescriptionId,
                   'analyse_id' => $parentAnalyse->id
               ])->update([
                   'status' => AnalysePrescription::STATUS_VALIDE,
                   'updated_at' => now()
               ]);

               // Les analyses enfants restent en TERMINE si elles étaient déjà TERMINE
               if ($parentAnalyse->children->isNotEmpty()) {
                   AnalysePrescription::where('prescription_id', $prescriptionId)
                       ->whereIn('analyse_id', $parentAnalyse->children->pluck('id'))
                       ->where('status', AnalysePrescription::STATUS_TERMINE)
                       ->update([
                           'status' => AnalysePrescription::STATUS_TERMINE,
                           'updated_at' => now()
                       ]);
               }
           }

           // Vérifier si toutes les analyses principales sont validées
           $totalParentAnalyses = $parentAnalyses->count();
           $validatedParentAnalyses = AnalysePrescription::where([
               'prescription_id' => $prescriptionId,
               'status' => AnalysePrescription::STATUS_VALIDE
           ])->whereIn('analyse_id', $parentAnalyses->pluck('id'))->count();

           // Mise à jour du statut de la prescription
           if ($totalParentAnalyses === $validatedParentAnalyses) {
               $prescription->update([
                   'status' => Prescription::STATUS_VALIDE
               ]);
           } else {
               $prescription->update([
                   'status' => Prescription::STATUS_TERMINE
               ]);
           }

           DB::commit();

           $this->dispatch('$refresh');
           $this->alert('success', 'Les analyses ont été validées avec succès');

           return redirect()->route('biologiste.analyse.index', ['tab' => 'valide']);

       } catch (\Exception $e) {
           DB::rollback();
           Log::error('Erreur validation analyses:', [
               'message' => $e->getMessage(),
               'trace' => $e->getTraceAsString(),
               'prescription_id' => $prescriptionId,
               'user_id' => Auth::id()
           ]);

           $this->alert('error', "Une erreur s'est produite lors de la validation");
           return false;
       }
   }
}
