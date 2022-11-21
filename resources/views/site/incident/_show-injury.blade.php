{{-- Show Injury Details --}}
<div class="portlet light" id="show_injury">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Injury Details</span>
        </div>
        <div class="actions">
            @if ($pEdit && $incident->status)
                <button class="btn btn-circle green btn-outline btn-sm" onclick="editForm('injury')">Edit</button>
            @endif
        </div>
    </div>
    <div class="portlet-body">
        <div class="row">
            <div class="col-md-3">Treatment:</div>
            <div class="col-xs-9">{!! $qTreatment->responsesCSV('site_incidents', $incident->id) !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Part(s) Injured:</div>
            <div class="col-xs-9">{!! $qInjuredPart->responsesCSV('site_incidents', $incident->id) !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Nature:</div>
            <div class="col-xs-9">{!! $qInjuredNature->responsesBullet('site_incidents', $incident->id) !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Mechanism:</div>
            <div class="col-xs-9">{!! $qInjuredMechanism->responsesBullet('site_incidents', $incident->id) !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Agency:</div>
            <div class="col-xs-9">{!! $qInjuredAgency->responsesBullet('site_incidents', $incident->id) !!}</div>
        </div>
        <hr class="field-hr">
    </div>
</div>