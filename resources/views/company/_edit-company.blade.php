{{-- Edit Company Details --}}
<div class="portlet light" style="display: none;" id="edit_company">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Company Details</span>
            @if(!$company->approved_by && $company->reportsTo()->id == Auth::user()->company_id)
                <span class="label label-warning">Pending Approval</span>
            @endif
        </div>
    </div>
    <div class="portlet-body form">
        {!! Form::model($company, ['method' => 'PATCH', 'action' => ['Company\CompanyController@update', $company->id]]) !!}
        {{-- Status --}}
        <div class="row">
            @if(Auth::user()->allowed2('del.company', $company))
                <div class="form-group {!! fieldHasError('status', $errors) !!}">
                    {!! Form::label('status', 'Status:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::select('status', ['1' => 'Active', '0' => 'Inactive'], null, ['class' => 'form-control bs-select']) !!}
                        {!! fieldErrorMessage('status', $errors) !!}
                        <span class="help-block"> Only editable by parent company</span>
                    </div>
                </div>
            @else
                <div class="col-md-3">Status:</div>
                <div class="col-xs-9">{!! $company->status_text !!}</div>
            @endif
        </div>
        <hr class="field-hr">
        @if ($company->status)
            {{-- Name --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('name', $errors) !!}">
                    {!! Form::label('name', 'Name:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::text('name', null, ['class' => 'form-control', 'required']) !!}
                        {!! fieldErrorMessage('name', $errors) !!}
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
                        {!! Form::text('address', null, ['class' => 'form-control', 'required']) !!}
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
                        {!! Form::text('suburb', null, ['class' => 'form-control', 'required']) !!}
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
                        {!! Form::select('state', $ozstates::all(), 'NSW', ['class' => 'form-control bs-select', 'required']) !!}
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
                        {!! Form::text('postcode', null, ['class' => 'form-control', 'required']) !!}
                        {!! fieldErrorMessage('postcode', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Primary Contact --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('primary_user', $errors) !!}">
                    {!! Form::label('primary_user', 'Primary Contact:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::select('primary_user', $company->usersSelect('prompt'),null, ['class' => 'form-control bs-select']) !!}
                        {!! fieldErrorMessage('primary_user', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Seconday Contact --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('secondary_user', $errors) !!}">
                    {!! Form::label('secondary_user', 'Secondary Contact:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::select('secondary_user',  ['0' => 'None'] + $company->usersSelect(), null, ['class' => 'form-control bs-select', 'required']) !!}
                        {!! fieldErrorMessage('secondary_user', $errors) !!}
                    </div>
                </div>
            </div>
        @else
            {{-- Pass Required Fields as hidden --}}
            {!! Form::hidden('name', null, ['class' => 'form-control']) !!}
            {!! Form::hidden('phone', null, ['class' => 'form-control']) !!}
            {!! Form::hidden('email', null, ['class' => 'form-control']) !!}
            {!! Form::hidden('address', null, ['class' => 'form-control']) !!}
            {!! Form::hidden('suburb', null, ['class' => 'form-control']) !!}
            {!! Form::hidden('state', null, ['class' => 'form-control']) !!}
            {!! Form::hidden('postcode', null, ['class' => 'form-control']) !!}
            {!! Form::hidden('primary_user', null, ['class' => 'form-control']) !!}
        @endif
        {{-- Notes --}}
        @if (Auth::user()->isCompany($company->reportsTo()))
            <hr class="field-hr">
            {{-- Planner Abbr --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('nickname', $errors) !!}">
                    {!! Form::label('nickname', 'Planner Abbreviation:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::text('nickname', null, ['class' => 'form-control']) !!}
                        {!! fieldErrorMessage('nickname', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Reports Abbr --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('abbr', $errors) !!}">
                    {!! Form::label('abbr', 'Report Abbreviation:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::text('abbr', null, ['class' => 'form-control']) !!}
                        {!! fieldErrorMessage('abbr', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            <div class="row">
                <div class="form-group {!! fieldHasError('notes', $errors) !!}">
                    {!! Form::label('notes', 'Private Notes:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::textarea('notes', null, ['rows' => '3', 'class' => 'form-control']) !!}
                        {!! fieldErrorMessage('notes', $errors) !!}
                        <span class="help-block"> Only viewable by parent company</span>
                    </div>
                </div>
            </div>
        @endif

        <br>
        <div class="form-actions right">
            @if ($company->status == 2)
                <button type="submit" class="btn green"> Continue</button>
            @else
                <button class="btn default" onclick="cancelForm(event, 'company')">Cancel</button>
                <button type="submit" class="btn green"> Save</button>
            @endif
        </div>
        {!! Form::close() !!}
    </div>
</div>