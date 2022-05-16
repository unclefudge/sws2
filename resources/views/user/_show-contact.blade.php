{{-- Show Contact Details --}}
<div class="portlet light" id="show_contact">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Contact Details</span>
            @if(false && !$user->approved_by && $user->company->reportsTo()->id == Auth::user()->company_id)
                <span class="label label-warning">Pending Approval</span>
            @endif
        </div>
        <div class="actions">
            @if (false && Auth::user()->allowed2('sig.user', $user) && !$user->approved_by  && $user->status)
                <a href="/company/{{ $company->id }}/approve/com" class="btn btn-circle green btn-outline btn-sm" id="but_approve">Approve</a>
            @endif
            @if (Auth::user()->allowed2('edit.user', $user))
                <button class="btn btn-circle green btn-outline btn-sm" onclick="editForm('contact')">Edit</button>
            @endif
        </div>
    </div>
    <div class="portlet-body">
        <div class="row">
            <div class="col-md-3">Name:</div>
            <div class="col-xs-9">{!! $user->name !!}</div>
        </div>
        <hr class="field-hr">

        <div class="row">
            <div class="col-md-3">Phone:</div>
            <div class="col-xs-9">{!! ($user->phone) ? "<a href='tel:'".preg_replace("/[^0-9]/", "", $user->phone)."> $user->phone </a>" : '-' !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Email:</div>
            <div class="col-xs-9">{!! ($user->email) ? "<a href='mailto:$user->email'> $user->email</a>" : '-' !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Address:</div>
            <div class="col-xs-9">{!! $user->address_formatted !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Company:</div>
            <div class="col-xs-9">
                @if (Auth::user()->allowed2('view.company', $user->company))
                    <a href="/company/{{ $user->company_id }}">{!! $user->company->name !!}</a>
                @else
                    {!!  $user->company->name !!}
                @endif
                @if ($user->id == $user->company->primary_user )
                    <span class='label label-sm label-info'>Primary Contact</span>
                @endif
                @if ($user->id == $user->company->secondary_user )
                    <span class='label label-sm label-info'>Secondary Contact</span>
                @endif
            </div>
        </div>
        @if ($user->jobtitle)
            <hr class="field-hr">
            <div class="row">
                <div class="col-md-3">Job Title:</div>
                <div class="col-xs-9">{!! $user->jobtitle !!}</div>
            </div>
        @endif

        <hr class="field-hr">
        @if (Auth::user()->isCompany($user->company->reportsTo()->id))
            <div class="row">
                <div class="col-md-3">Private Notes:</div>
                <div class="col-xs-9">@if($user->notes){!! nl2br($user->notes) !!} </a>@else - @endif
                </div>
            </div>
            <hr class="field-hr">
        @endif
    </div>
</div>