{{-- Show Admin Details --}}
<div class="portlet light" id="show_admin">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Admin Details</span>
        </div>
        <div class="actions">
            @if (Auth::user()->allowed2('edit.site.admin', $site))
                <button class="btn btn-circle green btn-outline btn-sm" onclick="editForm('admin')">Edit</button>
            @endif
        </div>
    </div>
    <div class="portlet-body">
        <div class="row">
            <div class="col-md-6">Council Approval:</div>
            <div class="col-xs-6">{!! ($site->council_approval) ? $site->council_approval->format('d/m/Y') : '-' !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-6">Contract Sent:</div>
            <div class="col-xs-6">{!! ($site->contract_sent) ? $site->contract_sent->format('d/m/Y') : '-' !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-6">Contract Signed:</div>
            <div class="col-xs-6">{!! ($site->contract_signed) ? $site->contract_signed->format('d/m/Y') : '-' !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-6">Deposit Paid:</div>
            <div class="col-xs-6">{!! ($site->deposit_paid) ? $site->deposit_paid->format('d/m/Y') : '-' !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-6">Prac Papers Signed:</div>
            <div class="col-xs-6">{!! ($site->completion_signed) ? $site->completion_signed->format('d/m/Y') : '-' !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-6">Construction Certificate:</div>
            <div class="col-xs-6">{!! ($site->construction_rcvd) ? $site->construction_rcvd->format('d/m/Y') : '-' !!}</div>
            {{--}}<div class="col-xs-6">{!! ($site->construction) ? 'Yes' : 'No' !!}</div>--}}
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-6">Home Builder Compensation Fund:</div>
            <div class="col-xs-6">{!! ($site->hbcf_start) ? $site->hbcf_start->format('d/m/Y') : '-' !!}</div>
            {{--}}<div class="col-xs-6">{!! ($site->hbcf) ? 'Yes' : 'No' !!}</div>--}}
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-6">Engineering Certificate:</div>
            {{--}}<div class="col-xs-6">{!! ($site->engineering_cert) ? $site->engineering_cert->format('d/m/Y') : '-' !!}</div>--}}
            <div class="col-xs-6">{!! ($site->engineering) ? 'Yes' : 'No' !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-6">Consultant:</div>
            <div class="col-xs-6">{!! $site->consultant_name !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-6">Project Coordinator:</div>
            <div class="col-xs-6">{!! $site->project_mgr_name !!}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-6">Start Estimate:</div>
            <div class="col-xs-6">{!! ($site->jobstart_estimate) ? $site->jobstart_estimate->format('d/m/Y') : '-' !!}</div>
        </div>
    </div>
</div>