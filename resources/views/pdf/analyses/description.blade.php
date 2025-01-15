{{-- Affichage de la description pour le parent --}}
@if(!empty($analyse->description))
<tr>
        <td colspan="4" style="padding:2px 0;">
            <div style="margin-top: 4px; font-size: 9pt; color: #333537;">
                <b>Description :</b> {!! nl2br(e($analyse->description)) !!}
            </div>
        </td>
    </tr>
@endif
{{-- Affichage des commentaires pour le parent --}}
@php
    $commentaires = $analyse->resultats->pluck('conclusion')->filter()->unique();
@endphp
@if($commentaires->isNotEmpty())
    <tr>
        <td colspan="4" style="padding:2px 0;">
            <div style="margin-top: 4px; font-size: 9pt; color: #333537;">
                <b>Commentaire :</b>
                @foreach($commentaires as $commentaire)
                    {!! nl2br(e($commentaire)) !!}
                    @if(!$loop->last)<br>@endif
                @endforeach
            </div>
        </td>
    </tr>
@endif
