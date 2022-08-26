{{-- Show Login Details --}}
<div class="portlet light" id="show_login">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Login Details</span>
            @if(false && !$user->approved_by && $user->company->reportsTo()->id == Auth::user()->company_id)
                <span class="label label-warning">Pending Approval</span>
            @endif
        </div>
        <div class="actions">
            @if (Auth::user()->allowed2('edit.user', $user) || (Auth::user()->hasRole2('con-construction-manager') && $user->company_id != Auth::user()->company_id))
                <button class="btn btn-circle green btn-outline btn-sm" onclick="editForm('login')">Edit</button>
            @endif
        </div>
    </div>
    <div class="portlet-body">
        <div class="row">
            <div class="col-md-3">Status:</div>
            <div class="col-xs-9">{!! $user->status_text !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Username:</div>
            <div class="col-xs-9">{!! $user->username !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Last Login:</div>
            <div class="col-xs-9">{!! ($user->last_login != '-0001-11-30 00:00:00') ? with(new \Carbon\Carbon($user->last_login))->format('d/m/Y') : 'never' !!}</div>
        </div>
        <hr class="field-hr">
    </div>
</div>