{{-- Edit Company Details --}}
<div class="portlet light" style="display: none;" id="edit_client">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Client Details</span> &nbsp; <span class="font-yellow small">*Fields Imported from Zoho</span>
        </div>
    </div>
    <div class="portlet-body form">
        {!! Form::model($site, ['method' => 'POST', 'action' => ['Site\SiteController@updateClient', $site->id]]) !!}
        {{-- Primary Details --}}
        <div class="row">
            <div class="col-md-12"><b>Primary Contact</b></div>
        </div>
        <hr class="field-hr">
        {{-- Primary Title --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('client1_title', $errors) !!}">
                {!! Form::label('client1_title', 'Title:', ['class' => 'col-md-3 control-label font-yellow']) !!}
                <div class="col-md-9">
                    {!! Form::text('client1_title', null, ['class' => 'form-control']) !!}
                    {!! fieldErrorMessage('client1_title', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">
        {{-- Primary Firstname --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('client1_firstname', $errors) !!}">
                {!! Form::label('client1_firstname', 'First Name:', ['class' => 'col-md-3 control-label font-yellow']) !!}
                <div class="col-md-9">
                    {!! Form::text('client1_firstname', null, ['class' => 'form-control']) !!}
                    {!! fieldErrorMessage('client1_firstname', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">
        {{-- Primary Lastname --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('client1_lastname', $errors) !!}">
                {!! Form::label('client1_lastname', 'Last Name:', ['class' => 'col-md-3 control-label font-yellow']) !!}
                <div class="col-md-9">
                    {!! Form::text('client1_lastname', null, ['class' => 'form-control']) !!}
                    {!! fieldErrorMessage('client1_lastname', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">
        {{-- Primary Phone --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('client1_mobile', $errors) !!}">
                {!! Form::label('client1_mobile', 'Mobile:', ['class' => 'col-md-3 control-label font-yellow']) !!}
                <div class="col-md-9">
                    {!! Form::text('client1_mobile', null, ['class' => 'form-control']) !!}
                    {!! fieldErrorMessage('client1_mobile', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">
        {{-- Primary Email --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('client1_email', $errors) !!}">
                {!! Form::label('client1_email', 'Email:', ['class' => 'col-md-3 control-label font-yellow']) !!}
                <div class="col-md-9">
                    {!! Form::text('client1_email', null, ['class' => 'form-control']) !!}
                    {!! fieldErrorMessage('client1_email', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">

        {{-- Secondary Details --}}
        <div class="row">
            <div class="col-md-12"><br><b>Secondary Contact</b></div>
        </div>
        <hr class="field-hr">
        {{-- Primary Title --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('client2_title', $errors) !!}">
                {!! Form::label('client1_title', 'Title:', ['class' => 'col-md-3 control-label font-yellow']) !!}
                <div class="col-md-9">
                    {!! Form::text('client2_title', null, ['class' => 'form-control']) !!}
                    {!! fieldErrorMessage('client2_title', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">
        {{-- Primary Firstname --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('client2_firstname', $errors) !!}">
                {!! Form::label('client2_firstname', 'First Name:', ['class' => 'col-md-3 control-label font-yellow']) !!}
                <div class="col-md-9">
                    {!! Form::text('client2_firstname', null, ['class' => 'form-control']) !!}
                    {!! fieldErrorMessage('client2_firstname', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">
        {{-- Primary Lastname --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('client2_lastname', $errors) !!}">
                {!! Form::label('client2_lastname', 'Last Name:', ['class' => 'col-md-3 control-label font-yellow']) !!}
                <div class="col-md-9">
                    {!! Form::text('client2_lastname', null, ['class' => 'form-control']) !!}
                    {!! fieldErrorMessage('client2_lastname', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">
        {{-- Primary Phone --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('client2_mobile', $errors) !!}">
                {!! Form::label('client2_mobile', 'Mobile:', ['class' => 'col-md-3 control-label font-yellow']) !!}
                <div class="col-md-9">
                    {!! Form::text('client2_mobile', null, ['class' => 'form-control']) !!}
                    {!! fieldErrorMessage('client2_mobile', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">
        {{-- Primary Email --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('client2_email', $errors) !!}">
                {!! Form::label('client2_email', 'Email:', ['class' => 'col-md-3 control-label font-yellow']) !!}
                <div class="col-md-9">
                    {!! Form::text('client2_email', null, ['class' => 'form-control']) !!}
                    {!! fieldErrorMessage('client2_email', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">
        <br>
        {{-- Client Intro--}}
        <div class="row">
            <div class="form-group {!! fieldHasError('client_intro', $errors) !!}">
                {!! Form::label('client_intro', 'Letter intro:', ['class' => 'col-md-3 control-label font-yellow']) !!}
                <div class="col-md-9">
                    {!! Form::text('client_intro', null, ['class' => 'form-control']) !!}
                    {!! fieldErrorMessage('client_intro', $errors) !!}
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