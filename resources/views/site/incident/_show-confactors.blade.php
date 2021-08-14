{{-- Show Con Factors --}}
<div class="portlet light" id="show_confactors">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Contributing Factors</span>
        </div>
        <div class="actions">
            @if (Auth::user()->allowed2('edit.site.incident', $incident))
                <button class="btn btn-circle green btn-outline btn-sm" onclick="editForm('confactors')">Edit</button>
            @endif
        </div>
    </div>
    <div class="portlet-body">
        {{-- Contributing Factors --}}
        @if ($qConFactorDefences->responsesCSV('site_incidents', $incident->id))
            <div class="row">
                <div class="col-md-12">Absent / Failed Defences:</div>
                <div class="col-md-12" style="margin-left: 40px">{!! $qConFactorDefences->responsesBullet('site_incidents', $incident->id) !!}</div>
            </div>
        @endif
        @if ($qConFactorITactions->responsesCSV('site_incidents', $incident->id))
            <div class="row">
                <div class="col-md-12">Individual / Team Actions:</div>
                <div class="col-md-12" style="margin-left: 40px">{!! $qConFactorITactions->responsesBullet('site_incidents', $incident->id) !!}</div>
            </div>
        @endif
        @if ($qConFactorWorkplace->responsesCSV('site_incidents', $incident->id))
            <div class="row">
                <div class="col-md-12">Workplace Conditions:</div>
                <div class="col-md-12" style="margin-left: 40px">{!! $qConFactorWorkplace->responsesBullet('site_incidents', $incident->id) !!}</div>
            </div>
        @endif
        @if ($qConFactorHuman->responsesCSV('site_incidents', $incident->id))
            <div class="row">
                <div class="col-md-12">Human Factors:</div>
                <div class="col-md-12" style="margin-left: 40px">{!! $qConFactorHuman->responsesBullet('site_incidents', $incident->id) !!}</div>
            </div>
        @endif
        <hr class="field-hr">
    </div>
</div>