{{-- Show Site Details --}}
<div class="portlet light" id="show_site">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Site Details</span>
        </div>
        <div class="actions">
            @if (Auth::user()->allowed2('edit.site', $site))
                <button class="btn btn-circle green btn-outline btn-sm" onclick="editForm('site')">Edit</button>
            @endif
        </div>
    </div>
    <div class="portlet-body">
        <div class="row">
            <div class="col-md-3">Status:</div>
            <div class="col-xs-9">{!! $site->status_text !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Job:</div>
            <div class="col-xs-9">{!! $site->code !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Name:</div>
            <div class="col-xs-9">{!! $site->name !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Address:</div>
            <div class="col-xs-9">{!! $site->address_formatted !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Supervisor(s):</div>
            <div class="col-xs-9">{!! $site->supervisorsSBC() !!}</div>
        </div>
        <hr class="field-hr">
        @if (Auth::user()->isCompany($site->company_id))
            <div class="row">
                <div class="col-md-3">Notes:</div>
                <div class="col-xs-9">@if($site->notes){!! nl2br($site->notes) !!} </a>@else - @endif
                </div>
            </div>
            <hr class="field-hr">
        @endif
    </div>
</div>