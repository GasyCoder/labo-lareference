{{-- Affichage de la description --}}
@if(!empty($analyse->description))
<tr>
    <td colspan="4" style="padding:2px 0;">
        <div style="margin-top: 4px; font-size: 9pt; color: #333537;">
            {!! nl2br(e($analyse->description)) !!}
        </div>
    </td>
</tr>
@endif
