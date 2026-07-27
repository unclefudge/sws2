{{-- Edit Company Leave --}}
<div class="portlet light" style="display: none;" id="add_leave">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Company Leave</span>
        </div>
    </div>
    <div class="portlet-body form">
        <form method="POST" action="{{ action([App\Http\Controllers\Company\CompanyController::class, 'storeLeave'], $company->id) }}" class="horizontal-form">
            @csrf

            {{-- Leave --}}
            <div class="row">
                <label for="from" class="col-md-3 control-label">Leave From:</label>
                <div class="col-md-9">
                    <x-form.date-range from="from" to="to"/>
                    <x-form.error name="start_date"/>
                </div>
            </div>
            <hr class="field-hr">
            <div class="row">
                <label for="notes" class="col-md-3 control-label">Notes:</label>
                <div class="col-md-9">
                    <x-form.textarea name="notes" rows="2" required/>
                </div>
            </div>
            <br>
            <div class="form-actions right">
                <button class="btn default" onclick="cancelForm(event, 'leave')">Cancel</button>
                <button type="submit" class="btn green"> Save</button>
            </div>
        </form>
    </div>
</div>
