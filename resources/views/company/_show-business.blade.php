<div class="portlet light" id="show_business">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Business Details</span>
            @if(!$company->approved_by && $company->reportsTo()->id == Auth::user()->company_id)
                <span class="label label-warning">Pending Approval</span>
            @endif
        </div>
        <div class="actions">
            @if (Auth::user()->allowed2('sig.company.acc', $company) && Auth::user()->company_id == $company->reportsTo()->id  && !$company->approved_by  && $company->status)
                <a href="/company/{{ $company->id }}/approve/acc" class="btn btn-circle green btn-outline btn-sm" id="but_approve">Approve</a>
            @endif
            @if (Auth::user()->allowed2('edit.company.acc', $company) && $company->status)
                <button class="btn btn-circle green btn-outline btn-sm" onclick="editForm('business')">Edit</button>
            @endif
        </div>
    </div>
    <div class="portlet-body">
        <div class="row">
            <div class="col-md-3">Business Entity:</div>
            <div class="col-xs-9">{{ ($company->business_entity) ? $companyEntityTypes::name($company->business_entity) : '-' }}</div>
        </div>
        <hr class="field-hr">
        @if(Auth::user()->isCompany($company->reportsTo()->id))
            <div class="row">
                <div class="col-md-3">Category:</div>
                <div class="col-xs-9">{{ (is_numeric($company->category)) ? $companyTypes::name($company->category) : '-' }}</div>
            </div>
            <hr class="field-hr">
        @endif
        <div class="row">
            <div class="col-md-3">ABN:</div>
            <div class="col-xs-9">{{ ($company->abn) ? $company->abn : '-' }}</div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">GST:</div>
            <div class="col-xs-9">@if($company->gst)
                    Yes
                @elseif($company->gst == '0')
                    No
                @else
                    -
                @endif</div>
        </div>
        <hr class="field-hr">
        @if (Auth::user()->isCC())
            <div class="row">
                <div class="col-md-3">Payroll Tax:</div>
                <div class="col-xs-9">@if($company->payroll_tax)
                        {{ ($company->payroll_tax > 0 && $company->payroll_tax < 8) ? 'Exempt (' . $company->payroll_tax . ')' : 'Liable' }}
                    @else
                        -
                    @endif</div>
            </div>
            <hr class="field-hr">
            <div class="row">
                <div class="col-md-3">Superannuation:</div>
                <div class="col-xs-9">@if($company->superannuation)
                        {{ $company->superannuation }}
                    @else
                        -
                    @endif</div>
            </div>
            <hr class="field-hr">
            <div class="row">
                <div class="col-md-3">Creditor Code:</div>
                <div class="col-xs-9">{{ ($company->creditor_code) ? $company->creditor_code : '-' }}</div>
            </div>
            <hr class="field-hr">
        @endif
    </div>
</div>