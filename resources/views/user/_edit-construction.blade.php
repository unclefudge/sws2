{{-- Edit construction --}}
<div class="portlet light" style="display: none;" id="edit_construction">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Construction</span>
        </div>
    </div>
    <div class="portlet-body form">
        {!! Form::model('user', ['method' => 'POST', 'action' => ['UserController@updateConstruction', $user->id]]) !!}
        {{--Licence Required --}}
        <div class="row">
            <div class="form-group">
                {!! Form::label('onsite', 'Attends Sites:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    {!! Form::select('onsite',['0' => 'No', '1' => 'Yes'], ($user->onsite) ? 1 : 0, ['class' => 'form-control bs-select', 'id' => 'onsite']) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">
        {{-- Trades --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('trades', $errors) !!} {!! fieldHasError('planned_trades', $errors) !!}">
                {!! Form::label('trades', 'Trades:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    {!! Form::select('trades', Auth::user()->company->tradeListSelect(), $user->tradesSkilledIn->pluck('id')->toArray(),
                    ['class' => 'form-control select2', 'name' => 'trades[]', 'title' => 'Select one or more trades', 'multiple', 'id' => 'trades']) !!}
                    {!! fieldErrorMessage('trades', $errors) !!}
                    {!! fieldErrorMessage('planned_trades', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">
        {{-- Apprentice --}}
        <div class="row">
            <div class="form-group">
                {!! Form::label('apprentice', 'Apprentice:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    {!! Form::select('apprentice',['0' => 'No', '1' => 'Yes'], ($user->apprentice) ? 1 : 0, ['class' => 'form-control bs-select', 'id' => 'apprentice']) !!}
                </div>
            </div>
        </div>
        {{-- Apprentice Start --}}
        <div class="row" style="display: none" id="apprentice-div">
            <hr class="field-hr">
            <div class="form-group {!! fieldHasError('apprentice_start', $errors) !!}">
                {!! Form::label('apprentice_start', 'Apprenticeship Start Date:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    <div class="input-group date date-picker">
                        {!! Form::text('apprentice_start', ($user->apprentice_start) ? $user->apprentice_start->format('d/m/Y') : '', ['class' => 'form-control form-control-inline', 'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy", 'readonly']) !!}
                        <span class="input-group-btn"><button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button></span>
                    </div>
                    {!! fieldErrorMessage('apprentice_start', $errors) !!}
                </div>
            </div>
        </div>

        <br>
        <div class="form-actions right">
            <button class="btn default" onclick="cancelForm(event, 'construction')">Cancel</button>
            <button type="submit" class="btn green"> Save</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>