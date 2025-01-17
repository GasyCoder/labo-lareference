{{-- conclusion --}}
@php
$conclusionsExamen = $examen->analyses->map(function($analyse) {
return $analyse->resultats->first()->conclusion ?? null;
})->filter()->unique()->values();
@endphp
@if($conclusionsExamen->isNotEmpty())
<div style="margin-top: 4px; margin-bottom: 4px;">
    <table class="main-table">
        <tr>
            <td colspan="4" style="padding: 5px 0;">
                <div style="border-bottom: 2px dotted #8f8a8a; margin-bottom: 10px; font-weight: normal; font-size: 10pt;">
                    Commentaire :
                    @foreach($conclusionsExamen as $conclusion)
                        <b>{!! nl2br(e($conclusion)) !!}</b>
                        @if(!$loop->last)<br>@endif
                    @endforeach
                </div>
            </td>
        </tr>
    </table>
</div>
@else
<div style="margin-top: 4px; margin-bottom: 4px;">
</div>
@endif

{{-- @if($analyse->resultats->isNotEmpty() && !empty($analyse->resultats->first()->conclusion))
<div style="margin-top: 2px; margin-bottom: 2px;">
    <table class="main-table">
        <tr>
            <td colspan="4" style="padding: 2px 0;">
                <div style="border-bottom: 2px dotted #8f8a8a; margin-bottom: 5px; font-weight: normal; font-size: 9pt;">
                    Commentaire : <b>{!! nl2br(e($analyse->resultats->first()->conclusion)) !!}</b>
                </div>
            </td>
        </tr>
    </table>
</div>
@endif --}}
