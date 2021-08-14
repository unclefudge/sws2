{{-- Show Conditions --}}
<div class="portlet light" id="show_conditions">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Incident Conditions</span>
        </div>
        <div class="actions">
            @if (Auth::user()->allowed2('edit.site.incident', $incident))
                <button class="btn btn-circle green btn-outline btn-sm" onclick="editForm('conditions')">Edit</button>
            @endif
        </div>
    </div>
    <div class="portlet-body">
        @if ($qConditions->responsesCSV('site_incidents', $incident->id))
            @foreach ($qConditions->optionsArray() as $id => $label)
                @if ($qConditions->responseOther('site_incidents', $incident->id, $id))
                    <div class="row">
                        <div class="col-md-12">{{ $label }}:</div>
                        <div class="col-md-12">
                            <ul>
                                <li>{!! $qConditions->responseOther('site_incidents', $incident->id, $id) !!}</li>
                            </ul>
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <div class="row">
                <div class="col-md-12">No conditions specified</div>
            </div>
        @endif
        <hr class="field-hr">
    </div>
</div>