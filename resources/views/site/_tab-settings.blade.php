@inject('ozstates', 'App\Http\Utilities\OzStates')

<div class="tab-pane {{ $tabs['0'] == 'settings' ? 'active' : '' }}" id="tab_settings">
    <div class="row profile-account">
        <div class="col-md-3">
            <ul class="ver-inline-menu tabbable margin-bottom-10">
                <li class="{{ $tabs['1'] == 'info' ? 'active' : '' }}">
                    <a data-toggle="tab" href="#tab_settings_info"><i class="fa fa-building"></i> Site Info </a>
                </li>
                @if (Auth::user()->allowed2('edit.site', $site) && Auth::user()->allowed2('edit.site.admin', $site))
                    <li class="{{ $tabs['1'] == 'admin' ? 'active' : '' }}">
                        <a data-toggle="tab" href="#tab_settings_admin"><i class="fa fa-briefcase"></i> Admin Info </a>
                    </li>
                @endif
                <li class="{{ $tabs['1'] == 'logo' ? 'active' : '' }}">
                    <a data-toggle="tab" href="#tab_settings_logo"><i class="fa fa-picture-o"></i> Change Photo </a>
                </li>
            </ul>
        </div>
        <div class="col-md-9">
            <div class="tab-content">
                <!-- Info Tab -->
                <div id="tab_settings_info" class="tab-pane {{ $tabs['1'] == 'info' ? 'active' : '' }}">
                    {!! Form::model($site, ['method' => 'PATCH', 'action' => ['Site\SiteController@update', $site->slug]]) !!}
                    {!! Form::hidden('tabs', 'settings:info') !!}
                    {!! Form::hidden('id', $site->id) !!}
                    {!! Form::hidden('slug', $site->slug) !!}
                    {!! Form::hidden('client_id', $site->client_id) !!}
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h3 class="font-green sbold uppercase" style="margin:0 0 10px;">{{ $site->name }}</h3>
                            </div>
                            <div class="col-md-6">
                                <!-- Upcoming / Completed -->
                                @if($site->status == '-1')
                                    <h3 class="pull-right font-blue uppercase" style="margin:0 0 10px;">Upcoming Site</h3>
                                @elseif($site->status == '0')
                                    <h3 class="pull-right font-red uppercase" style="margin:0 0 10px;">Completed Site</h3>
                                @elseif($site->status == '2')
                                    <h3 class="pull-right font-red uppercase" style="margin:0 0 10px;">Maintenance</h3>
                                @endif
                            </div>
                        </div>

                        @include('form-error')
                                <!-- name -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                    {!! Form::label('name', 'Name', ['class' => 'control-label']) !!}
                                    {!! Form::text('name', null, ['class' => 'form-control']) !!}
                                    {!! fieldErrorMessage('name', $errors) !!}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group {!! fieldHasError('code', $errors) !!}">
                                    {!! Form::label('code', 'Job #', ['class' => 'control-label']) !!}
                                    {!! Form::text('code', null, ['class' => 'form-control']) !!}
                                    {!! fieldErrorMessage('code', $errors) !!}
                                </div>
                            </div>
                            <div class="col-md-3 pull-right">
                                <div class="form-group {!! fieldHasError('status', $errors) !!}">
                                    {!! Form::label('status', 'Status', ['class' => 'control-label']) !!}
                                    {!! Form::select('status', ['-1' => 'Upcoming', '1' => 'Active', '2' => 'Maintenance', '0' => 'Completed'],
                                     $site->status, ['class' => 'form-control bs-select']) !!}
                                    {!! fieldErrorMessage('status', $errors) !!}
                                </div>
                            </div>
                        </div>

                        <!-- address -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group {!! fieldHasError('address', $errors) !!}">
                                    {!! Form::label('address', 'Address', ['class' => 'control-label']) !!}
                                    {!! Form::text('address', null, ['class' => 'form-control']) !!}
                                    {!! fieldErrorMessage('address', $errors) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {!! fieldHasError('suburb', $errors) !!}">
                                    {!! Form::label('suburb', 'Suburb', ['class' => 'control-label']) !!}
                                    {!! Form::text('suburb', null, ['class' => 'form-control']) !!}
                                    {!! fieldErrorMessage('suburb', $errors) !!}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group {!! fieldHasError('state', $errors) !!}">
                                    {!! Form::label('state', 'State', ['class' => 'control-label']) !!}
                                    {!! Form::select('state', $ozstates::all(),
                                     'NSW', ['class' => 'form-control bs-select']) !!}
                                    {!! fieldErrorMessage('state', $errors) !!}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group {!! fieldHasError('postcode', $errors) !!}">
                                    {!! Form::label('postcode', 'Postcode', ['class' => 'control-label']) !!}
                                    {!! Form::text('postcode', null, ['class' => 'form-control']) !!}
                                    {!! fieldErrorMessage('postcode', $errors) !!}
                                </div>
                            </div>
                        </div>

                        <hr>
                        <!-- Client + Supervisor(s) -->
                        <div class="row">
                            <!--<div class="col-md-6">
                                <div class="form-group {!! fieldHasError('client_id', $errors) !!}">
                                    {!! Form::label('client_id', 'Client', ['class' => 'control-label']) !!}
                            {!! Form::select('client_id', Auth::user()->company->clientSelect(),
                             $site->client_id, ['class' => 'form-control bs-select']) !!}
                            {!! fieldErrorMessage('client_id', $errors) !!}
                                    </div>
                                </div>-->
                            <div class="col-md-6">
                                <div class="form-group {!! fieldHasError('client_phone', $errors) !!}">
                                    {!! Form::label('client_phone', 'Client Phone No.', ['class' => 'control-label']) !!}
                                    {!! Form::text('client_phone', $site->client_phone, ['class' => 'form-control']) !!}
                                    {!! fieldErrorMessage('client_phone', $errors) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {!! fieldHasError('client_phone_desc', $errors) !!}">
                                    {!! Form::label('client_phone_desc', 'Phone Description', ['class' => 'control-label']) !!}
                                    {!! Form::text('client_phone_desc', $site->client_phone_desc, ['class' => 'form-control']) !!}
                                    {!! fieldErrorMessage('client_phone_desc', $errors) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {!! fieldHasError('client_phone2', $errors) !!}">
                                    {!! Form::label('client_phone2', 'Client Second Phone No.', ['class' => 'control-label']) !!}
                                    {!! Form::text('client_phone2', $site->client_phone2, ['class' => 'form-control']) !!}
                                    {!! fieldErrorMessage('client_phone2', $errors) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {!! fieldHasError('client_phone2_desc', $errors) !!}">
                                    {!! Form::label('client_phone2_desc', 'Second Phone Description', ['class' => 'control-label']) !!}
                                    {!! Form::text('client_phone2_desc', $site->client_phone2_desc, ['class' => 'form-control']) !!}
                                    {!! fieldErrorMessage('client_phone2_desc', $errors) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {!! fieldHasError('supervisors', $errors) !!}" id="super-div">
                                    {!! Form::label('supervisors', 'Supervisor(s)', ['class' => 'control-label']) !!}
                                    {!! Form::select('supervisors',
                                    Auth::user()->company->supervisorsSelect(),
                                     $site->supervisors->pluck('id')->toArray(), ['class' => 'form-control bs-select', 'name' => 'supervisors[]', 'title' => 'Select one or more supervisors', 'multiple']) !!}
                                    {!! fieldErrorMessage('supervisors', $errors) !!}
                                </div>
                            </div>
                        </div>

                        <hr>
                        <!-- Notes -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group {!! fieldHasError('notes', $errors) !!}">
                                    {!! Form::label('notes', 'Notes', ['class' => 'control-label']) !!}
                                    {!! Form::textarea('notes', null, ['rows' => '2', 'class' => 'form-control']) !!}
                                    {!! fieldErrorMessage('notes', $errors) !!}
                                    <span class="help-block"> For internal use only </span>
                                </div>
                            </div>
                        </div>

                        <div class="margiv-top-10">
                            <button type="submit" class="btn green"> Save Changes</button>
                            <a href="/site/{{ $site->slug }}/settings/info">
                                <button type="button" class="btn default"> Cancel</button>
                            </a>

                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>

                <!-- Admin Tab -->
                <div id="tab_settings_admin" class="tab-pane {{ $tabs['1'] == 'admin' ? 'active' : '' }}">
                    {!! Form::model($site, ['method' => 'POST', 'action' => ['Site\SiteController@updateAdmin', $site->slug], 'files' => true]) !!}
                    {!! Form::hidden('tabs', 'settings:admin') !!}
                    {!! Form::hidden('id', $site->id) !!}
                    {!! Form::hidden('slug', $site->slug) !!}

                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="font-green sbold uppercase" style="margin:0 0 10px;">{{ $site->name }}
                                <small class="font-grey-silver">ID: {{ $site->id }}</small>
                            </h3>
                        </div>
                        <div class="col-md-6">
                            <!-- Upcoming / Completed -->
                            @if($site->status == '-1')
                                <h3 class="pull-right font-blue uppercase" style="margin:0 0 10px;">Upcoming Site</h3>
                            @elseif($site->status == '0')
                                <h3 class="pull-right font-red uppercase" style="margin:0 0 10px;">Completed Site</h3>
                            @endif
                        </div>
                    </div>

                    @include('form-error')

                    <form action="#" role="form">
                        <div class="row">
                            <div class="col-md-4" style="padding-left:0px">
                                <!-- Contract Dates -->
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('contract_sent', $errors) !!}">
                                        {!! Form::label('contract_sent', 'Contract Sent', ['class' => 'control-label']) !!}
                                        <div class="input-group date date-picker">
                                            {!! Form::text('contract_sent', ($site->contract_sent) ? $site->contract_sent->format('d/m/Y') : '', ['class' => 'form-control form-control-inline',
                                            'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                                            <span class="input-group-btn">
                                            <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                        </span>
                                        </div>
                                        {!! fieldErrorMessage('contract_sent', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('contract_signed', $errors) !!}">
                                        {!! Form::label('contract_signed', 'Contract Signed', ['class' => 'control-label']) !!}
                                        <div class="input-group date date-picker">
                                            {!! Form::text('contract_signed', ($site->contract_signed) ? $site->contract_signed->format('d/m/Y') : '', ['class' => 'form-control form-control-inline', 'readonly',
                                            'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                                            <span class="input-group-btn">
                                            <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                        </span>
                                        </div>
                                        {!! fieldErrorMessage('contract_signed', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('deposit_paid', $errors) !!}">
                                        {!! Form::label('deposit_paid', 'Deposit Paid', ['class' => 'control-label']) !!}
                                        <div class="input-group date date-picker">
                                            {!! Form::text('deposit_paid', ($site->deposit_paid) ? $site->deposit_paid->format('d/m/Y') : '', ['class' => 'form-control form-control-inline', 'readonly',
                                            'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                                            <span class="input-group-btn">
                                            <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                        </span>
                                        </div>
                                        {!! fieldErrorMessage('deposit_paid', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('completion_signed', $errors) !!}">
                                        {!! Form::label('completion_signed', 'Prac Papers Signed', ['class' => 'control-label']) !!}
                                        <div class="input-group date date-picker">
                                            {!! Form::text('completion_signed', ($site->completion_signed) ? $site->completion_signed->format('d/m/Y') : '', ['class' => 'form-control form-control-inline', 'readonly',
                                            'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                                            <span class="input-group-btn">
                                            <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                        </span>
                                        </div>
                                        {!! fieldErrorMessage('completion_signed', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2"></div>

                            <!-- Toggles -->
                            <div class="col-md-6" style="padding-left:0px">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('engineering', $errors) !!}">
                                        <p class="myswitch-label" style="font-size: 14px">&nbsp; Engineering Certificate</p>
                                        {!! Form::label('engineering', "&nbsp;", ['class' => 'control-label']) !!}
                                        {!! Form::checkbox('engineering', '1', $site->engineering ? true : false,
                                         ['class' => 'make-switch',
                                         'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                         'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        {!! fieldErrorMessage('engineering', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <p class="myswitch-label" style="font-size: 14px">&nbsp; Construction Certificate</p>
                                        {!! Form::label('construction', "&nbsp;", ['class' => 'control-label']) !!}
                                        {!! Form::checkbox('construction', '1', $site->construction ? true : false,
                                         ['class' => 'make-switch',
                                         'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                         'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        {!! fieldErrorMessage('construction', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('hbcf', $errors) !!}">
                                        <p class="myswitch-label" style="font-size: 14px">&nbsp; Home Builder Compensation Fund</p>
                                        {!! Form::label('hbcf', "&nbsp;", ['class' => 'control-label']) !!}
                                        {!! Form::checkbox('hbcf', '1', $site->hbcf ? true : false,
                                         ['class' => 'make-switch',
                                         'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                         'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        {!! fieldErrorMessage('hbcf', $errors) !!}
                                    </div>

                                    {{--
                                    <div class="form-group {!! fieldHasError('transient', $errors) !!}">
                                        <p class="myswitch-label" style="font-size: 14px">&nbsp; Transient</p>
                                        {!! Form::label('transient', "&nbsp;", ['class' => 'control-label']) !!}
                                        {!! Form::checkbox('transient', '1', $company->transient ? true : false,
                                         ['class' => 'make-switch',
                                         'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                         'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}

                                        {!! fieldErrorMessage('transient', $errors) !!}
                                    </div>--}}
                                </div>
                            </div>
                        </div>

                        <!-- Consultant -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {!! fieldHasError('consultant_name', $errors) !!}">
                                    {!! Form::label('consultant_name', 'Consultant Name', ['class' => 'control-label']) !!}
                                    {!! Form::text('consultant_name', $site->consultant_name, ['class' => 'form-control']) !!}
                                    {!! fieldErrorMessage('consultant_name', $errors) !!}
                                </div>
                            </div>
                        </div>
                        <div class="margin-top-10">
                            <button type="submit" class="btn green"> Save Changes</button>
                            <a href="/site/{{ $site->slug }}/settings/password">
                                <button type="button" class="btn default"> Cancel</button>
                            </a>
                        </div>
                    {!! Form::close() !!}
                </div>

                <!-- Photo Tab -->
                <div id="tab_settings_logo" class="tab-pane {{ $tabs['1'] == 'photo' ? 'active' : '' }}">
                    {!! Form::model($site, ['method' => 'POST', 'action' => ['Site\SiteController@updateLogo', $site->slug], 'files' => true]) !!}
                    {!! Form::hidden('tabs', 'settings:photo') !!}
                    {!! Form::hidden('slug', $site->slug) !!}

                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="font-green sbold uppercase" style="margin:0 0 10px;">{{ $site->name }}</h3>
                        </div>
                        <div class="col-md-6">
                            <!-- Upcoming / Completed -->
                            @if($site->status == '-1')
                                <h3 class="pull-right font-blue uppercase" style="margin:0 0 10px;">Upcoming Site</h3>
                            @elseif($site->status == '0')
                                <h3 class="pull-right font-red uppercase" style="margin:0 0 10px;">Completed Site</h3>
                            @endif
                        </div>
                    </div>

                    @include('form-error')

                    For best display use a 'square' photo<br><br>

                    <form action="#" role="form">
                        <div class="form-group">
                            <div class="fileinput fileinput-new" data-provides="fileinput">
                                <div class="fileinput-new thumbnail" style="width: 150px; height: 150px;">
                                    @if($site->photo && file_exists(public_path($site->photo)))
                                        <img src="/{{ $site->photo }}" alt=""/>
                                    @else
                                        <img src="/img/no_image.png" alt=""/>
                                    @endif
                                </div>
                                <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;"></div>
                                <div>
                                    <span class="btn default btn-file">
                                        <span class="fileinput-new"> Select image </span>
                                        <span class="fileinput-exists"> Change </span>
                                        <input type="file" name="photo"> </span>
                                    <a href="javascript:;" class="btn default fileinput-exists" data-dismiss="fileinput"> Remove </a>
                                </div>
                            </div>
                        </div>
                        <div class="margin-top-10">
                            <button type="submit" class="btn green"> Save Photo</button>
                            <a href="/site/{{ $site->slug }}/settings/logo">
                                <button type="button" class="btn default"> Cancel</button>
                            </a>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
        <!--end col-md-9-->
    </div>
</div>