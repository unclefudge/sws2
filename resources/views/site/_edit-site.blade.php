{{-- Edit Company Details --}}
<div class="portlet light" style="display: none;" id="edit_site">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Site Details</span> &nbsp; <span class="font-yellow small">*Fields Imported from Zoho</span>
        </div>
    </div>
    <div class="portlet-body form">
        {!! Form::model($site, ['method' => 'PATCH', 'action' => ['Site\SiteController@update', $site->id]]) !!}
        {{-- Status --}}
        <div class="row">
            @if(Auth::user()->allowed2('del.site', $site))
                <div class="form-group {!! fieldHasError('status', $errors) !!}">
                    {!! Form::label('status', 'Status:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::select('status', ['-1' => 'Upcoming', '1' => 'Active', '2' => 'Maintenance', '0' => 'Completed', '-2' => 'Cancelled'], $site->status, ['class' => 'form-control bs-select']) !!}
                        {!! fieldErrorMessage('status', $errors) !!}
                    </div>
                </div>
            @else
                <div class="col-md-3">Status:</div>
                <div class="col-xs-9">{!! $site->status_text !!}</div>
            @endif
        </div>
        <hr class="field-hr">
        @if ($site->status != 0)
            @if (Auth::user()->allowed2('edit.site.zoho.fields', $site))
                {{-- Job --}}
                <div class="row">
                    <div class="form-group {!! fieldHasError('name', $errors) !!}">
                        {!! Form::label('code', 'Job:', ['class' => 'col-md-3 control-label font-yellow']) !!}
                        <div class="col-md-9">
                            {!! Form::text('code', null, ['class' => 'form-control', 'required']) !!}
                            {!! fieldErrorMessage('code', $errors) !!}
                        </div>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Name --}}
                <div class="row">
                    <div class="form-group {!! fieldHasError('name', $errors) !!}">
                        {!! Form::label('name', 'Name:', ['class' => 'col-md-3 control-label font-yellow']) !!}
                        <div class="col-md-9">
                            {!! Form::text('name', null, ['class' => 'form-control', 'required']) !!}
                            {!! fieldErrorMessage('name', $errors) !!}
                        </div>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Adddress --}}
                <div class="row">
                    <div class="form-group {!! fieldHasError('address', $errors) !!}">
                        {!! Form::label('address', 'Address:', ['class' => 'col-md-3 control-label font-yellow']) !!}
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
                        {!! Form::label('suburb', 'Suburb:', ['class' => 'col-md-3 control-label font-yellow']) !!}
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
                        {!! Form::label('state', 'State:', ['class' => 'col-md-3 control-label font-yellow']) !!}
                        <div class="col-md-9">
                            {!! Form::select('state', $ozstates::all(), null, ['class' => 'form-control bs-select', 'required']) !!}
                            {!! fieldErrorMessage('state', $errors) !!}
                        </div>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Postcode --}}
                <div class="row">
                    <div class="form-group {!! fieldHasError('postcode', $errors) !!}">
                        {!! Form::label('postcode', 'Postcode:', ['class' => 'col-md-3 control-label font-yellow']) !!}
                        <div class="col-md-9">
                            {!! Form::text('postcode', null, ['class' => 'form-control', 'required']) !!}
                            {!! fieldErrorMessage('postcode', $errors) !!}
                        </div>
                    </div>
                </div>
                <hr class="field-hr">
            @endif
        @else
            {{-- Pass Required Fields as hidden --}}
            {!! Form::hidden('code', null, ['class' => 'form-control']) !!}
            {!! Form::hidden('name', null, ['class' => 'form-control']) !!}
            {!! Form::hidden('address', null, ['class' => 'form-control']) !!}
            {!! Form::hidden('suburb', null, ['class' => 'form-control']) !!}
            {!! Form::hidden('state', null, ['class' => 'form-control']) !!}
            {!! Form::hidden('postcode', null, ['class' => 'form-control']) !!}
        @endif
        @if($site->status != 0 || Auth::user()->allowed2('del.site', $site))
            {{-- Primary Supervisor--}}
            <div class="row">
                <div class="form-group {!! fieldHasError('supervisor_id', $errors) !!}">
                    {!! Form::label('supervisor_id', 'Supervisor', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        @if($site->status != 0)
                            {!! Form::select('supervisor_id', Auth::user()->company->supervisorsSelect(), $site->supervisor_id, ['class' => 'form-control bs-select', 'name' => 'supervisor_id', 'title' => 'Select supervisor']) !!}
                        @else
                            {!! Form::select('supervisor_id', Auth::user()->company->supervisorsSelect(), $site->supervisor_id, ['class' => 'form-control bs-select', 'name' => 'supervisor_id', 'title' => 'Select supervisor']) !!}
                        @endif
                        {!! fieldErrorMessage('supervisor_id', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Secondary Supervisor--}}
            <div class="row">
                <div class="form-group {!! fieldHasError('supervisors', $errors) !!}">
                    {!! Form::label('supervisors', 'Secondary Supervisor(s)', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::select('supervisors', Auth::user()->company->supervisorsSelect(), $site->supervisors->pluck('id')->toArray(), ['class' => 'form-control bs-select', 'name' => 'supervisors[]', 'title' => 'Select one or more supervisors', 'multiple']) !!}
                        {!! fieldErrorMessage('supervisors', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
        @endif
        {{-- Notes --}}
        @if (Auth::user()->company_id == $site->company_id)
            <div class="row">
                <div class="form-group {!! fieldHasError('notes', $errors) !!}">
                    {!! Form::label('notes', 'Notes:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::textarea('notes', null, ['rows' => '3', 'class' => 'form-control']) !!}
                        {!! fieldErrorMessage('notes', $errors) !!}
                        <span class="help-block"> For internal use only</span>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
        @endif
        <br>
        <div class="form-actions right">
            <button class="btn default" onclick="cancelForm(event, 'site')">Cancel</button>
            <button type="submit" class="btn green"> Save</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>