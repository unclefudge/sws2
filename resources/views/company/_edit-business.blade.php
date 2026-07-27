<div class="portlet light" style="display: none;" id="edit_business">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Business Details</span>
        </div>
        <div class="actions">
            @if (Auth::user()->allowed2('sig.company', $company) && !$company->approved_by)
                <a href="/company/{{ $company->id }}/approve" class="btn btn-circle green btn-outline btn-sm" id="but_approve">Approve</a>
            @endif
        </div>
    </div>
    <div class="portlet-body form">
        <form method="POST" action="{{ action([App\Http\Controllers\Company\CompanyController::class, 'updateBusiness'], $company->id) }}">
            @csrf
            {{-- Business Entity --}}
            <div class="row">
                <label for="business_entity" class="col-md-3 control-label">Business Entity:</label>
                <div class="col-md-9">
                    <x-form.select name="business_entity" :options="$companyEntityTypes::all()" :value="$company->business_entity" required/>
                </div>
            </div>
            {{-- Category --}}
            @if(Auth::user()->isCompany($company->reportsTo()->id))
                <hr class="field-hr">
                <div class="row">
                    <label for="category" class="col-md-3 control-label">Category:</label>
                    <div class="col-md-9">
                        <x-form.select name="category" :options="$companyTypes::all()" :value="$company->category" required/>
                        <span class="help-block"> Only viewable by parent company</span>
                    </div>
                </div>
            @endif
            <hr class="field-hr">
            {{-- ABN --}}
            <div class="row">
                <label for="abn" class="col-md-3 control-label">ABN:</label>
                <div class="col-md-9">
                    <x-form.input name="abn" :value="$company->abn" required/>
                </div>
            </div>
            <hr class="field-hr">
            {{-- GST --}}
            <div class="row">
                <label for="gst" class="col-md-3 control-label">GST:</label>
                <div class="col-md-9">
                    <x-form.select name="gst" :options="['1' => 'Yes', '0' => 'No']" :value="$company->gst" required/>
                </div>
            </div>
            @if (Auth::user()->isCC())
                <hr class="field-hr">
                {{-- Payroll Tax --}}
                <div class="row">
                    <label for="payroll_tax" class="col-md-3 control-label">Payroll Tax:</label>
                    <div class="col-md-9">
                        <x-form.select name="payroll_tax" :options="$payrollTaxTypes::all()" :value="$company->payroll_tax"/>
                        <span class="help-block"> Only viewable by parent company</span>
                    </div>
                </div>
                <hr class="field-hr">
                <div class="row">
                    <label for="superannuation" class="col-md-3 control-label">Superannuation:</label>
                    <div class="col-md-9">
                        <x-form.select name="superannuation" :options="['' => 'Select option', 'Liable' => 'Liable', 'Non Liable' => 'Non Liable']" :value="$company->superannuation"/>
                        <span class="help-block"> Only viewable by parent company</span>
                    </div>
                </div>
                <hr class="field-hr">
                <div class="row">
                    <label for="creditor_code" class="col-md-3 control-label">Creditor Code:</label>
                    <div class="col-md-9">
                        <x-form.input name="creditor_code" :value="$company->creditor_code" required/>
                        <span class="help-block"> Only viewable by parent company</span>
                    </div>
                </div>
            @endif

            <br>
            <div class="form-actions right">
                <button class="btn default" onclick="cancelForm(event, 'business')">Cancel</button>
                <button type="submit" class="btn green"> Save</button>
            </div>
        </form>
    </div>
</div>
