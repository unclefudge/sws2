{{-- Edit Company Details --}}
<div class="portlet light" style="display: none;" id="edit_client">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Client Details</span>
            @if($site->status == 2)
                <span class="label label-warning">Maintenance</span>
            @endif
        </div>
    </div>
    <div class="portlet-body form">
        {!! Form::model($site, ['method' => 'POST', 'action' => ['Site\SiteController@updateClient', $site->id]]) !!}
        {{-- Primary Contact Desc--}}
        <div class="row">
            <div class="form-group {!! fieldHasError('client_phone_desc', $errors) !!}">
                {!! Form::label('client_phone_desc', 'Primary Name:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    {!! Form::text('client_phone_desc', null, ['class' => 'form-control']) !!}
                    {!! fieldErrorMessage('client_phone_desc', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">
        {{-- Primary Phone --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('client_phone', $errors) !!}">
                {!! Form::label('client_phone', 'Primary Phone:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    {!! Form::text('client_phone', null, ['class' => 'form-control']) !!}
                    {!! fieldErrorMessage('client_phone', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">

        {{-- Primary Email --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('client_email', $errors) !!}">
                {!! Form::label('client_email', 'Primary Email:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    {!! Form::text('client_email', null, ['class' => 'form-control']) !!}
                    {!! fieldErrorMessage('client_email', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">

        {{-- Secondary Contact Desc--}}
        <div class="row">
            <div class="form-group {!! fieldHasError('client_phone2_desc', $errors) !!}">
                {!! Form::label('client_phone2_desc', 'Secondary Name:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    {!! Form::text('client_phone2_desc', null, ['class' => 'form-control']) !!}
                    {!! fieldErrorMessage('client_phone2_desc', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">
        {{-- Secondary Phone --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('client_phone2', $errors) !!}">
                {!! Form::label('client_phone2', 'Secondary Phone:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    {!! Form::text('client_phone2', null, ['class' => 'form-control']) !!}
                    {!! fieldErrorMessage('client_phone2', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">
        {{-- Secondary Email --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('client_email2', $errors) !!}">
                {!! Form::label('client_email2', 'Secondary Email:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    {!! Form::text('client_email2', null, ['class' => 'form-control']) !!}
                    {!! fieldErrorMessage('client_email2', $errors) !!}
                </div>
            </div>
        </div>
        <br>
        <div class="form-actions right">
            <button class="btn default" onclick="cancelForm(event, 'client')">Cancel</button>
            <button type="submit" class="btn green"> Save</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>