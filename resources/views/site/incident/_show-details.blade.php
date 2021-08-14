{{-- Show Details --}}
<div class="portlet light" id="show_details">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Incident Details</span>
        </div>
        <div class="actions">
            @if (Auth::user()->allowed2('edit.site.incident', $incident))
                <button class="btn btn-circle green btn-outline btn-sm" onclick="editForm('details')">Edit</button>
            @endif
        </div>
    </div>
    <div class="portlet-body">
        <div class="row">
            <div class="col-md-3">Status:</div>
            <div class="col-xs-9">{!! $incident->status_text !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Risk Potential:</div>
            <div class="col-xs-9">{!! $incident->riskRatingText('risk_potential') !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Risk Actual:</div>
            <div class="col-xs-9">{!! $incident->riskRatingText('risk_actual') !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Summary:</div>
            <div class="col-xs-9">{!! nl2br($incident->exec_summary) !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Description:</div>
            <div class="col-xs-9">{!! nl2br($incident->exec_describe) !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Corrective Actions:</div>
            <div class="col-xs-9">{!! nl2br($incident->exec_actions) !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Notifiable:</div>
            <div class="col-xs-9">@if ($incident->notifiable != null){!! ($incident->notifiable) ? 'Yes' : 'No'!!}@endif</div>
        </div>
        <hr class="field-hr">
    </div>
</div>