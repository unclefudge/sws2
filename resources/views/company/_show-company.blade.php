{{-- Show Company Details --}}
<div class="portlet light" id="show_company">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Company Details</span>
            @if(!$company->approved_by && $company->reportsTo()->id == Auth::user()->company_id)
                <span class="label label-warning">Pending Approval</span>
            @endif
        </div>
        <div class="actions">
            @if (Auth::user()->allowed2('sig.company', $company) && !$company->approved_by  && $company->status)
                <a href="/company/{{ $company->id }}/approve/com" class="btn btn-circle green btn-outline btn-sm" id="but_approve">Approve</a>
            @endif
            @if (Auth::user()->allowed2('edit.company', $company))
                <button class="btn btn-circle green btn-outline btn-sm" onclick="editForm('company')">Edit</button>
            @endif
        </div>
    </div>
    <div class="portlet-body">
        <div class="row">
            <div class="col-md-3">Status:</div>
            <div class="col-xs-9">{!! $company->status_text !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Name:</div>
            <div class="col-xs-9">{{ $company->name }}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Phone:</div>
            <div class="col-xs-9">{!! ($company->phone) ? "<a href='tel:'".preg_replace("/[^0-9]/", "", $company->phone)."> $company->phone </a>" : '-' !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Email:</div>
            <div class="col-xs-9">{!! ($company->email) ? "<a href='mailto:$company->email'> $company->email</a>" : '-' !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Address:</div>
            <div class="col-xs-9">{!! $company->address_formatted !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Primary Contact:</div>
            <div class="col-xs-9">@if($company->primary_user)<a href="/user/{{ $company->primary_contact()->id }}">{{ $company->primary_contact()->fullname }}</a>@else - @endif</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Secondary Contact:</div>
            <div class="col-xs-9">@if($company->secondary_user)<a href="/user/{{ $company->secondary_contact()->id }}">{{ $company->secondary_contact()->fullname }}</a>@else - @endif
            </div>
        </div>
        <hr class="field-hr">
        @if (Auth::user()->isCompany($company->reportsTo()))
            <div class="row">
                <div class="col-md-3">Planner Abbr.:</div>
                <div class="col-xs-9">{!! $company->nickname !!}</div>
            </div>
            <hr class="field-hr">
            <div class="row">
                <div class="col-md-3">Report Abbr.:</div>
                <div class="col-xs-9">{!! $company->abbr !!}</div>
            </div>
            <hr class="field-hr">
            <div class="row">
                <div class="col-md-3">Private Notes:</div>
                <div class="col-xs-9">@if($company->notes){!! nl2br($company->notes) !!} </a>@else - @endif
                </div>
            </div>
            <hr class="field-hr">
        @endif
    </div>
</div>