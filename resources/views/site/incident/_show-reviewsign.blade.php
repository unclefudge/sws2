{{-- Show Review Sign --}}
<?php
$show_signoff = 0;
if (isset($reviewsBy[Auth::user()->id])) {
    $todo = Auth::user()->todoType('incident review')->where('type_id', $incident->id)->first();
    if ($todo) {
        $todo->markOpenedBy(Auth::user());
        if (!$todo->done_by)
            $show_signoff = 1;
    }
}
?>
{!! Form::hidden('show_signoff', $show_signoff, ['id' => 'show_signoff']) !!}
<div class="portlet light" id="show_reviewsign">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Incident Reviewed</span>
        </div>
        <div class="actions">
        </div>
    </div>
    <div class="portlet-body form">
        @if (isset($reviewsBy[Auth::user()->id]) && $reviewsBy[Auth::user()->id])
            <div class="row">
                <div class="col-md-3">Signed:</div>
                <div class="col-md-9">{{ Auth::user()->name }} ({{ $reviewsBy[Auth::user()->id] }})</div>
            </div>
            <hr class="field-hr">
            <div class="row">
                <div class="col-md-3">Comments:</div>
                <div class="col-md-9">{{  ($todo) ? $todo->comments : '' }}</div>
            </div>
        @else
            {!! Form::model('action', ['method' => 'POST', 'action' => ['Site\Incident\SiteIncidentController@signoff', $incident->id], 'class' => 'horizontal-form', 'id' => 'form_reviewed']) !!}
            {!! Form::hidden('done_at', '0', ['id' => 'done_at']) !!}

            <div class="row">
                <div class="col-md-12">Please review and sign off your acceptance of this incident report.<br><br></div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group {!! fieldHasError('comments', $errors) !!}">
                        {!! Form::textarea('comments', ($todo) ? $todo->comments : '', ['rows' => '3', 'class' => 'form-control', 'placeholder' => 'If you have any comments please add here']) !!}
                        {!! fieldErrorMessage('comments', $errors) !!}
                    </div>
                </div>
            </div>
            <br>
            <div class="form-actions right">
                <button type="submit" class="btn green"> Save Comments</button>
                <button id="signoff_review" type="submit" class="btn red"> Sign Off Acceptance</button>
            </div>
            {!! Form::close() !!}
        @endif
    </div>
</div>