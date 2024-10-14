{{-- Edit construction --}}
<div class="portlet light" style="display: none;" id="edit_construction">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Construction</span>
        </div>
    </div>
    <div class="portlet-body form">
        {!! Form::model('company', ['method' => 'POST', 'action' => ['Company\CompanyController@updateConstruction', $company->id]]) !!}
        {{-- Trades --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('trades', $errors) !!} {!! fieldHasError('planned_trades', $errors) !!}">
                {!! Form::label('trades', 'Trades:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    {!! Form::select('trades', Auth::user()->company->tradeListSelect(), $company->tradesSkilledIn->pluck('id')->toArray(), ['class' => 'form-control select2', 'name' => 'trades[]', 'title' => 'Select one or more trades', 'multiple', 'id' => 'trades']) !!}
                    {!! fieldErrorMessage('trades', $errors) !!}
                    {!! fieldErrorMessage('planned_trades', $errors) !!}
                </div>
            </div>
        </div>
        @if(Auth::user()->isCC())
            <hr class="field-hr">
            {{-- Planner Name --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('nickname', $errors) !!}">
                    {!! Form::label('nickname', 'Planner Name:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::text('nickname', $company->nickname, ['class' => 'form-control']) !!}
                        {!! fieldErrorMessage('nickname', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Max Jobs --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('maxjobs', $errors) !!}">
                    {!! Form::label('maxjobs', 'Max Jobs:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::text('maxjobs', $company->maxjobs, ['class' => 'form-control', 'required']) !!}
                        {!! fieldErrorMessage('maxjobs', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">

            {{-- Transient --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('transient', $errors) !!}">
                    {!! Form::label('transient', 'Transient:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::select('transient', ['0' => 'No', '1' => 'Yes'], $company->transient, ['class' => 'form-control bs-select']) !!}
                        {!! fieldErrorMessage('transient', $errors) !!}
                    </div>
                </div>
            </div>

            <div id="super-div" @if (!$company->transient) style="display: none" @endif>
                <hr class="field-hr">
                <div class="row">
                    <div class="form-group {!! fieldHasError('supervisors', $errors) !!}">
                        {!! Form::label('supervisors', 'Supervisor:', ['class' => 'col-md-3 control-label']) !!}
                        <div class="col-md-9">
                            {!! Form::select('supervisors', Auth::user()->company->supervisorsSelect(),  $company->supervisedBy->pluck('id')->toArray(), ['class' => 'form-control select2', 'name' => 'supervisors[]', 'title' => 'Select one or more trades', 'multiple', 'id' => 'supervisors']) !!}
                            {!! fieldErrorMessage('supervisors', $errors) !!}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <br>
        <div class="form-actions right">
            <button class="btn default" onclick="cancelForm(event, 'construction')">Cancel</button>
            <button type="submit" class="btn green"> Save</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>