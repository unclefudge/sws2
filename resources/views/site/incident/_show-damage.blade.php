{{-- Show Damage Details --}}
<div class="portlet light" id="show_damage">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Damage Details</span>
        </div>
        <div class="actions">
            @if (Auth::user()->allowed2('edit.site.incident', $incident))
                <button class="btn btn-circle green btn-outline btn-sm" onclick="editForm('damage')">Edit</button>
            @endif
        </div>
    </div>
    <div class="portlet-body">
        <div class="row">
            <div class="col-md-3">Damage Details:</div>
            <div class="col-xs-9">{{  $incident->damage }}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Repair Cost:</div>
            <div class="col-xs-9">{!! $incident->damage_cost !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Repair Details:</div>
            <div class="col-xs-9">{!! nl2br($incident->damage_repair) !!}</div>
        </div>
    </div>
</div>