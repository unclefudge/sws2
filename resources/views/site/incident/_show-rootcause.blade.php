{{-- Show Root Cause --}}
<div class="portlet light" id="show_rootcause">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Root Cause - Organisation Factors</span>
        </div>
        <div class="actions">
            @if (Auth::user()->allowed2('edit.site.incident', $incident))
                <button class="btn btn-circle green btn-outline btn-sm" onclick="editForm('rootcause')">Edit</button>
            @endif
        </div>
    </div>
    <div class="portlet-body">
        {{-- Root Cause --}}
        @if ($qRootCause->responsesCSV('site_incidents', $incident->id))
            @foreach ($qRootCause->optionsArray() as $id => $label)
                @if ($qRootCause->responseOther('site_incidents', $incident->id, $id))
                    <div class="row">
                        <div class="col-md-12">{{ $label }}:</div>
                        <div class="col-md-12">
                            <ul>
                                <li>{!! $qRootCause->responseOther('site_incidents', $incident->id, $id) !!}</li>
                            </ul>
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <div class="row">
                <div class="col-md-12">No root causes identified</div>
            </div>
        @endif
    </div>
</div>