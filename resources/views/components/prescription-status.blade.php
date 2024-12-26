{{-- components/prescription-status.blade.php --}}
@props(['status'])

@php
$statusConfig = [
    'EN_ATTENTE' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'En attente'],
    'EN_COURS' => ['class' => 'info', 'icon' => 'spinner', 'text' => 'En cours'],
    'TERMINE' => ['class' => 'primary', 'icon' => 'check', 'text' => 'Terminé'],
    'VALIDE' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Validé'],
    'ARCHIVE' => ['class' => 'secondary', 'icon' => 'archive', 'text' => 'Archivé'],
][$status] ?? ['class' => 'secondary', 'icon' => 'question', 'text' => 'Inconnu'];
@endphp

<span class="badge bg-{{ $statusConfig['class'] }}">
    <i class="fas fa-{{ $statusConfig['icon'] }} me-1"></i>
    {{ $statusConfig['text'] }}
</span>
