{{-- Edit construction --}}
<div class="portlet light" style="display: none;" id="edit_construction">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Construction</span>
        </div>
    </div>
    <div class="portlet-body form">
        <form method="POST" action="{{ action([App\Http\Controllers\UserController::class, 'updateConstruction'], $user->id) }}">
            @csrf
        {{--Licence Required --}}
        <div class="row">
            <div class="form-group">
                <label for="onsite" class="col-md-3 control-label">Attends Sites:</label>
                <div class="col-md-9">
                    <x-form.select name="onsite" :options="['0' => 'No', '1' => 'Yes']" :value="$user->onsite ? 1 : 0"/>
                </div>
            </div>
        </div>
        <hr class="field-hr">
        {{-- Trades --}}
        <div class="row">
            <div class="form-group">
                <label for="trades" class="col-md-3 control-label">Trades:</label>
                <div class="col-md-9">
                    <x-form.select name="trades[]" :options="Auth::user()->company->tradeListSelect()" :value="$user->tradesSkilledIn->pluck('id')->toArray()" plugin="select2" title="Select one or more trades" multiple/>
                    <x-form.error name="planned_trades"/>
                </div>
            </div>
        </div>
        <hr class="field-hr">
        {{-- Apprentice --}}
        <div class="row">
            <div class="form-group">
                <label for="apprentice" class="col-md-3 control-label">Apprentice:</label>
                <div class="col-md-9">
                    <x-form.select name="apprentice" :options="['0' => 'No', '1' => 'Yes']" :value="$user->apprentice ? 1 : 0"/>
                </div>
            </div>
        </div>
        {{-- Apprentice Start --}}
        <div class="row" style="display: none" id="apprentice-div">
            <hr class="field-hr">
            <div class="form-group">
                <label for="apprentice_start" class="col-md-3 control-label">Apprenticeship Start Date:</label>
                <div class="col-md-9">
                    <x-form.datepicker name="apprentice_start" :value="$user->apprentice_start ? $user->apprentice_start->format('d/m/Y') : ''" readonly/>
                </div>
            </div>
        </div>

        <br>
        <div class="form-actions right">
            <button class="btn default" onclick="cancelForm(event, 'construction')">Cancel</button>
            <button type="submit" class="btn green"> Save</button>
        </div>
        </form>
    </div>
</div>