{{-- Show Regulator --}}
@if ($incident->notifiable)
    <div class="portlet light" id="show_regulator">
        <div class="portlet-title">
            <div class="caption">
                <span class="caption-subject font-dark bold uppercase">Regulator Action Details</span>
            </div>
            <div class="actions">
                @if (Auth::user()->allowed2('edit.site.incident', $incident))
                    <button class="btn btn-circle green btn-outline btn-sm" onclick="editForm('regulator')">Edit</button>
                @endif
            </div>
        </div>
        <div class="portlet-body">
            <div class="row">
                <div class="col-md-3">Context:</div>
                <div class="col-xs-9">{!! nl2br($incident->notifiable_reason) !!}</div>
            </div>
            <hr class="field-hr">
            <div class="row">
                <div class="col-md-3">Regulator:</div>
                <div class="col-xs-9">{!! $incident->regulator !!}</div>
            </div>
            <hr class="field-hr">
            <div class="row">
                <div class="col-md-3">Regulator Ref:</div>
                <div class="col-xs-9">{!! $incident->regulator_ref !!}</div>
            </div>
            <hr class="field-hr">
            <div class="row">
                <div class="col-md-3">Notified Date:</div>
                <div class="col-xs-9">{!! ($incident->regulator_date) ? $incident->regulator_date->format('d/m/Y') : '' !!}</div>
            </div>
            <hr class="field-hr">
            <div class="row">
                <div class="col-md-3">Inspector:</div>
                <div class="col-xs-9">{!! $incident->inspector !!}</div>
            </div>
            <hr class="field-hr">
            <div class="row">
                <div class="col-md-3">Notes:</div>
                <div class="col-xs-9">{!! nl2br($incident->notes) !!}</div>
            </div>
            <hr class="field-hr">
        </div>
    </div>
@endif