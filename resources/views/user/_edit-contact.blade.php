{{-- Edit Company Details --}}
<div class="portlet light" style="display: none;" id="edit_contact">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Contact Details</span>
            @if(!$user->approved_by && Auth::user()->allowed2('sig.user', $user))
                <span class="label label-warning">Pending Approval</span>
            @endif
        </div>
    </div>
    <div class="portlet-body form">
        {!! Form::model($user, ['method' => 'PATCH', 'action' => ['UserController@update', $user->id]]) !!}

        @if ($user->status)
            {{-- First Name --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('firstname', $errors) !!}">
                    {!! Form::label('firstname', 'First Name:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::text('firstname', null, ['class' => 'form-control', 'required']) !!}
                        {!! fieldErrorMessage('firstname', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Last Name --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('lastname', $errors) !!}">
                    {!! Form::label('lastname', 'Last Name:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::text('lastname', null, ['class' => 'form-control', 'required']) !!}
                        {!! fieldErrorMessage('lastname', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Phone --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('phone', $errors) !!}">
                    {!! Form::label('phone', 'Phone:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::text('phone', null, ['class' => 'form-control']) !!}
                        {!! fieldErrorMessage('phone', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Email --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('email', $errors) !!}">
                    {!! Form::label('email', 'Email:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::text('email', null, ['class' => 'form-control', 'required']) !!}
                        {!! fieldErrorMessage('email', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Adddress --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('address', $errors) !!}">
                    {!! Form::label('address', 'Address:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::text('address', null, ['class' => 'form-control']) !!}
                        {!! fieldErrorMessage('address', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Suburb --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('suburb', $errors) !!}">
                    {!! Form::label('suburb', 'Suburb:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::text('suburb', null, ['class' => 'form-control']) !!}
                        {!! fieldErrorMessage('suburb', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- State --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('state', $errors) !!}">
                    {!! Form::label('state', 'State:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::select('state', $ozstates::all(), 'NSW', ['class' => 'form-control bs-select']) !!}
                        {!! fieldErrorMessage('state', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Postcode --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('postcode', $errors) !!}">
                    {!! Form::label('postcode', 'Postcode:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::text('postcode', null, ['class' => 'form-control']) !!}
                        {!! fieldErrorMessage('postcode', $errors) !!}
                    </div>
                </div>
            </div>
            @if ($user->company_id == 3)
                <hr class="field-hr">
                {{-- Job Title --}}
                <div class="row">
                    <div class="form-group {!! fieldHasError('jobtitle', $errors) !!}">
                        {!! Form::label('jobtitle', 'Job Title:', ['class' => 'col-md-3 control-label']) !!}
                        <div class="col-md-9">
                            {!! Form::text('jobtitle', null, ['class' => 'form-control']) !!}
                            {!! fieldErrorMessage('jobtitle', $errors) !!}
                        </div>
                    </div>
                </div>
            @endif
        @else
            {{-- Pass Required Fields as hidden --}}
            {!! Form::hidden('firstname', null, ['class' => 'form-control']) !!}
            {!! Form::hidden('lastname', null, ['class' => 'form-control']) !!}
            {!! Form::hidden('phone', null, ['class' => 'form-control']) !!}
            {!! Form::hidden('email', null, ['class' => 'form-control']) !!}
            {!! Form::hidden('address', null, ['class' => 'form-control']) !!}
            {!! Form::hidden('suburb', null, ['class' => 'form-control']) !!}
            {!! Form::hidden('state', null, ['class' => 'form-control']) !!}
            {!! Form::hidden('postcode', null, ['class' => 'form-control']) !!}
            {!! Form::hidden('jobtitle', null, ['class' => 'form-control']) !!}
        @endif
        {{-- Notes --}}
        @if ((Auth::user()->isCompany($user->company_id) && Auth::user()->hasPermission2('view.user.security')) || ($user->company->parent_company && Auth::user()->isCompany($user->company->reportsTo()->id)))
            <hr class="field-hr">
            <div class="row">
                <div class="form-group {!! fieldHasError('notes', $errors) !!}">
                    {!! Form::label('notes', 'Private Notes:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::textarea('notes', null, ['rows' => '3', 'class' => 'form-control']) !!}
                        {!! fieldErrorMessage('notes', $errors) !!}
                        <span class="help-block"> Viewable by parent company or user with security access</span>
                    </div>
                </div>
            </div>
        @endif

        <br>
        <div class="form-actions right">
            @if ($user->status == 2)
                <button type="submit" class="btn green"> Continue</button>
            @else
                <button class="btn default" onclick="cancelForm(event, 'contact')">Cancel</button>
                <button type="submit" class="btn green"> Save</button>
            @endif
        </div>
        {!! Form::close() !!}
    </div>
</div>