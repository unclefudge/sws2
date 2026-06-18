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
                    <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteController::class, 'update'], $site->slug) }}">
                        @csrf
                        @method('PATCH')
                        <x-form.hidden name="tabs" value="settings:info"/>
                        <x-form.hidden name="id" :value="$site->id"/>
                        <x-form.hidden name="slug" :value="$site->slug"/>
                        <x-form.hidden name="client_id" :value="$site->client_id"/>
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
                                    <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                        <label for="name" class="control-label">Name</label>
                                        <x-form.input name="name" :value="$site->name"/>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                                        <label for="code" class="control-label">Job #</label>
                                        <x-form.input name="code" :value="$site->code"/>
                                    </div>
                                </div>
                                <div class="col-md-3 pull-right">
                                    <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                        <label for="status" class="control-label">Status</label>
                                        <x-form.select name="status" :options="['-1' => 'Upcoming', '1' => 'Active', '2' => 'Maintenance', '0' => 'Completed']" :value="$site->status"/>
                                    </div>
                                </div>
                            </div>

                            <!-- address -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                                        <label for="address" class="control-label">Address</label>
                                        <x-form.input name="address" :value="$site->address"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {{ $errors->has('suburb') ? 'has-error' : '' }}">
                                        <label for="suburb" class="control-label">Suburb</label>
                                        <x-form.input name="suburb" :value="$site->suburb"/>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group {{ $errors->has('state') ? 'has-error' : '' }}">
                                        <label for="state" class="control-label">State</label>
                                        <x-form.select name="state" :options="$ozstates::all()" :value="'NSW'"/>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group {{ $errors->has('postcode') ? 'has-error' : '' }}">
                                        <label for="postcode" class="control-label">Postcode</label>
                                        <x-form.input name="postcode" :value="$site->postcode"/>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <!-- Client + Supervisor(s) -->
                            <div class="row">
                                <!--<div class="col-md-6">
                                <div class="form-group {{ $errors->has('client_id') ? 'has-error' : '' }}">
                                    <label for="client_id" class="control-label">Client</label>
                            <x-form.select name="client_id" :options="Auth::user()->company->clientSelect()" :value="$site->client_id" plugin="bs-select"/>
                            </div>
                        </div>-->
                                <div class="col-md-6">
                                    <div class="form-group {{ $errors->has('client_phone') ? 'has-error' : '' }}">
                                        <label for="client_phone" class="control-label">Client Phone No.</label>
                                        <x-form.input name="client_phone" :value="$site->client_phone"/>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group {{ $errors->has('client_phone_desc') ? 'has-error' : '' }}">
                                        <label for="client_phone_desc" class="control-label">Phone Description</label>
                                        <x-form.input name="client_phone_desc" :value="$site->client_phone_desc"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {{ $errors->has('client_phone2') ? 'has-error' : '' }}">
                                        <label for="client_phone2" class="control-label">Client Second Phone No.</label>
                                        <x-form.input name="client_phone2" :value="$site->client_phone2"/>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group {{ $errors->has('client_phone2_desc') ? 'has-error' : '' }}">
                                        <label for="client_phone2_desc" class="control-label">Second Phone Description</label>
                                        <x-form.input name="client_phone2_desc" :value="$site->client_phone2_desc"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {{ $errors->has('supervisors') ? 'has-error' : '' }}" id="super-div">
                                        <label for="supervisors" class="control-label">Supervisor(s)</label>
                                        <x-form.select name="supervisors[]" :options="Auth::user()->company->supervisorsSelect()" :value="$site->supervisors->pluck('id')->toArray()" placeholder="Select one or more supervisors" multiple/>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <!-- Notes -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {{ $errors->has('notes') ? 'has-error' : '' }}">
                                        <label for="notes" class="control-label">Notes</label>
                                        <x-form.textarea name="notes" :value="$site->notes" rows="2"/>
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
                    </form>
                </div>

                <!-- Admin Tab -->
                <div id="tab_settings_admin" class="tab-pane {{ $tabs['1'] == 'admin' ? 'active' : '' }}">
                    <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteController::class, 'updateAdmin'], $site->slug) }}" enctype="multipart/form-data">
                        @csrf
                        <x-form.hidden name="tabs" value="settings:admin"/>
                        <x-form.hidden name="id" :value="$site->id"/>
                        <x-form.hidden name="slug" :value="$site->slug"/>

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
                                        <div class="form-group {{ $errors->has('contract_sent') ? 'has-error' : '' }}">
                                            <label for="contract_sent" class="control-label">Contract Sent</label>
                                            <x-form.datepicker name="contract_sent" :value="($site->contract_sent) ? $site->contract_sent->format('d/m/Y') : ''"/>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group {{ $errors->has('contract_signed') ? 'has-error' : '' }}">
                                            <label for="contract_signed" class="control-label">Contract Signed</label>
                                            <x-form.datepicker name="contract_signed" :value="($site->contract_signed) ? $site->contract_signed->format('d/m/Y') : ''" readonly/>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group {{ $errors->has('deposit_paid') ? 'has-error' : '' }}">
                                            <label for="deposit_paid" class="control-label">Deposit Paid</label>
                                            <x-form.datepicker name="deposit_paid" :value="($site->deposit_paid) ? $site->deposit_paid->format('d/m/Y') : ''" readonly/>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group {{ $errors->has('completion_signed') ? 'has-error' : '' }}">
                                            <label for="completion_signed" class="control-label">Prac Papers Signed</label>
                                            <x-form.datepicker name="completion_signed" :value="($site->completion_signed) ? $site->completion_signed->format('d/m/Y') : ''" readonly/>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2"></div>

                                <!-- Toggles -->
                                <div class="col-md-6" style="padding-left:0px">
                                    <div class="col-md-12">
                                        <div class="form-group {{ $errors->has('engineering') ? 'has-error' : '' }}">
                                            <p class="myswitch-label" style="font-size: 14px">&nbsp; Engineering Certificate</p>
                                            <label for="engineering" class="control-label">&nbsp;</label>
                                            <input type="checkbox" name="engineering" id="engineering" value="1" class="make-switch" data-on-text="Yes" data-on-color="success" data-off-text="No" data-off-color="danger" {{ old('engineering', $site->engineering ? true : false) ? 'checked' : '' }}>
                                            <x-form.error name="engineering"/>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <p class="myswitch-label" style="font-size: 14px">&nbsp; Construction Certificate</p>
                                            <label for="construction" class="control-label">&nbsp;</label>
                                            <input type="checkbox" name="construction" id="construction" value="1" class="make-switch" data-on-text="Yes" data-on-color="success" data-off-text="No" data-off-color="danger" {{ old('construction', $site->construction ? true : false) ? 'checked' : '' }}>
                                            <x-form.error name="construction"/>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group {{ $errors->has('hbcf') ? 'has-error' : '' }}">
                                            <p class="myswitch-label" style="font-size: 14px">&nbsp; Home Builder Compensation Fund</p>
                                            <label for="hbcf" class="control-label">&nbsp;</label>
                                            <input type="checkbox" name="hbcf" id="hbcf" value="1" class="make-switch" data-on-text="Yes" data-on-color="success" data-off-text="No" data-off-color="danger" {{ old('hbcf', $site->hbcf ? true : false) ? 'checked' : '' }}>
                                            <x-form.error name="hbcf"/>
                                        </div>

                                        {{--
                                        <div class="form-group {{ $errors->has('transient') ? 'has-error' : '' }}">
                                            <p class="myswitch-label" style="font-size: 14px">&nbsp; Transient</p>
                                            <label for="transient" class="control-label">&nbsp;</label>
                                            <input type="checkbox" name="transient" id="transient" value="1" class="make-switch" data-on-text="Yes" data-on-color="success" data-off-text="No" data-off-color="danger" {{ old('transient', $company->transient ? true : false) ? 'checked' : '' }}>

                                            <x-form.error name="transient"/>
                                        </div>--}}
                                    </div>
                                </div>
                            </div>

                            <!-- Consultant -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {{ $errors->has('consultant_name') ? 'has-error' : '' }}">
                                        <label for="consultant_name" class="control-label">Consultant Name</label>
                                        <x-form.input name="consultant_name" :value="$site->consultant_name"/>
                                    </div>
                                </div>
                            </div>
                            <div class="margin-top-10">
                                <button type="submit" class="btn green"> Save Changes</button>
                                <a href="/site/{{ $site->slug }}/settings/password">
                                    <button type="button" class="btn default"> Cancel</button>
                                </a>
                            </div>
                        </form>
                </div>

                <!-- Photo Tab -->
                <div id="tab_settings_logo" class="tab-pane {{ $tabs['1'] == 'photo' ? 'active' : '' }}">
                    <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteController::class, 'updateLogo'], $site->slug) }}" enctype="multipart/form-data">
                        @csrf
                        <x-form.hidden name="tabs" value="settings:photo"/>
                        <x-form.hidden name="slug" :value="$site->slug"/>

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
                                        @if($site->photo)
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
                        </form>
                </div>
            </div>
        </div>
        <!--end col-md-9-->
    </div>
</div>