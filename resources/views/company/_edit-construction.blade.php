{{-- Edit construction --}}
<div class="portlet light" style="display: none;" id="edit_construction">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Construction</span>
        </div>
    </div>
    <div class="portlet-body form">
        <form method="POST" action="{{ action([App\Http\Controllers\Company\CompanyController::class, 'updateConstruction'], $company->id) }}">
            @csrf
        {{-- Trades --}}
        <div class="row">
            <label for="trades" class="col-md-3 control-label">Trades:</label>
            <div class="col-md-9">
                <x-form.select name="trades[]" :options="Auth::user()->company->tradeListSelect()" :value="$company->tradesSkilledIn->pluck('id')->toArray()" plugin="select2" title="Select one or more trades" multiple/>
                <x-form.error name="planned_trades"/>
            </div>
        </div>
        @if(Auth::user()->isCC())
            <hr class="field-hr">
            {{-- Planner Name --}}
            <div class="row">
                <label for="nickname" class="col-md-3 control-label">Planner Name:</label>
                <div class="col-md-9"><x-form.input name="nickname" :value="$company->nickname"/></div>
            </div>
            <hr class="field-hr">
            {{-- Max Jobs --}}
            <div class="row">
                <label for="maxjobs" class="col-md-3 control-label">Max Jobs:</label>
                <div class="col-md-9"><x-form.input name="maxjobs" :value="$company->maxjobs" required/></div>
            </div>
            <hr class="field-hr">

            {{-- Transient --}}
            <div class="row">
                <label for="transient" class="col-md-3 control-label">Transient:</label>
                <div class="col-md-9"><x-form.select name="transient" :options="['0' => 'No', '1' => 'Yes']" :value="$company->transient"/></div>
            </div>

            <div id="super-div" @if (!$company->transient) style="display: none" @endif>
                <hr class="field-hr">
                <div class="row">
                    <label for="supervisors" class="col-md-3 control-label">Supervisor:</label>
                    <div class="col-md-9">
                        <x-form.select name="supervisors[]" :options="Auth::user()->company->supervisorsSelect()" :value="$company->supervisedBy->pluck('id')->toArray()" plugin="select2" title="Select one or more trades" multiple/>
                    </div>
                </div>
            </div>
        @endif

        <br>
        <div class="form-actions right">
            <button class="btn default" onclick="cancelForm(event, 'construction')">Cancel</button>
            <button type="submit" class="btn green"> Save</button>
        </div>
        </form>
    </div>
</div>
