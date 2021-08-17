{{-- Overview Details --}}
<div class="col-lg-6 col-xs-12 col-sm-12 pull-right">
    <div class="portlet light">
        <div class="portlet-title">
            <div class="caption">
                <span class="caption-subject font-dark bold uppercase">Incident Lodgement</span>
            </div>
            <div class="actions">
            </div>
        </div>
        <div class="portlet-body">
            <div class="row">
                <div class="col-md-3">Status:</div>
                <div class="col-xs-9">{!! $incident->status_text !!} @if (!$incident->status) {!! ($incident->resolved_at) ? "<span class='font-red'>".$incident->resolved_at->format('d/m/Y')."</span>" : '' !!} @endif</div>
            </div>
            <hr class="field-hr">
            <div class="row">
                <div class="col-md-3">Reported by:</div>
                <div class="col-xs-9">{{ $incident->createdBy->fullname }} ({{ $incident->created_at->format('d/m/Y') }})</div>
            </div>
            <hr class="field-hr">
            {{--}}<div class="row">
                <div class="col-md-3">Reported date:</div>
                <div class="col-xs-9">{{ $incident->created_at->format('d/m/Y') }}</div>
            </div>
            <hr class="field-hr">--}}
        </div>
    </div>
</div>