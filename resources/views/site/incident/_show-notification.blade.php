{{-- Show Notification Details --}}
<div class="portlet light" id="show_notification">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Notification Details</span>
        </div>
        <div class="actions">
            @if ($pEdit && $incident->status)
                <button class="btn btn-circle green btn-outline btn-sm" onclick="editForm('notification')">Edit</button>
            @endif
        </div>
    </div>
    <div class="portlet-body">
        <div class="row">
            <div class="col-md-3">Incident Date:</div>
            <div class="col-xs-9">{{  $incident->date->format('d/m/Y G:i a') }}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">{{ ($incident->site_id) ? 'Site:' : 'Place of incident:'}}</div>
            <div class="col-xs-9">
                @if ($incident->site)
                    <b>{!! $incident->site_name !!}</b><br>
                    {!! $incident->site->address_formatted !!}
                @else
                    {!! $incident->site_name !!}
                @endif</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Location:</div>
            <div class="col-xs-9">{!! $incident->location !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Incident Type:</div>
            <div class="col-xs-9">{!! $qType->responsesCSV('site_incidents', $incident->id) !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">What occured:</div>
            <div class="col-xs-9">{!! nl2br($incident->describe) !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Actions taken:</div>
            <div class="col-xs-9">{!! nl2br($incident->actions_taken) !!}</div>
        </div>
        <hr class="field-hr">
        @if ($incident->site)
            <div class="row">
                <div class="col-md-3">Site Supervisor:</div>
                <div class="col-xs-9">{!! $incident->site_supervisor !!}</div>
            </div>
            <hr class="field-hr">
        @endif
    </div>
</div>