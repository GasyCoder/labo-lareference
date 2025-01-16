{{-- conclusion --}}
@if($analyse->resultats->isNotEmpty() && !empty($analyse->resultats->first()->conclusion))
<div style="margin-top: 4px; margin-bottom: 4px;">
    <table class="main-table">
        <tr>
            <td colspan="4" style="padding: 5px 0;">
                <div style="border-bottom: 2px dotted #8f8a8a; margin-bottom: 10px; font-weight: normal; font-size: 10pt;">
                    Commentaire : <b>{!! nl2br(e($analyse->resultats->first()->conclusion)) !!}</b>
                </div>
            </td>
        </tr>
    </table>
</div>
@endif
