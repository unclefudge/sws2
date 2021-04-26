@inject('maintenanceWarranty', 'App\Http\Utilities\MaintenanceWarranty')
@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/maintenance">Maintenance</a><i class="fa fa-circle"></i></li>
        <li><span>View Request</span></li>
    </ul>
@stop

<style>
    a.mytable-header-link {
        font-size: 14px;
        font-weight: 600;
        color: #333 !important;
    }

    @media screen and (min-width: 992px) {
        .datepicker-input {
            width: 130px !important;
        }
    }

    @media screen and (min-width: 1200px) {
        .datepicker-input {
            width: 160px !important;
        }
    }
</style>

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Site Maintenance Request</span>
                            <span class="caption-helper">ID: {{ $main->code }}</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="page-content-inner">
                            {!! Form::model($main, ['method' => 'PATCH', 'action' => ['Site\SiteMaintenanceController@update', $main->id], 'class' => 'horizontal-form']) !!}
                            <input type="hidden" id="site_id" value="{{ $main->site_id }}">

                            @include('form-error')

                            <input v-model="xx.main.id" type="hidden" id="main_id" value="{{ $main->id }}">
                            <input v-model="xx.main.name" type="hidden" id="main_name" value="{{ $main->name }}">
                            <input v-model="xx.main.site_id" type="hidden" id="main_site_id" value="{{ $main->site_id }}">

                            <input v-model="xx.main.status" type="hidden" id="main_status" value="{{ $main->status }}">
                            <input v-model="xx.main.warranty" type="hidden" id="main_warranty" value="{{ $main->warranty }}">
                            <input v-model="xx.main.signed" type="hidden" id="main_signed" value="{{ $main->isSigned() }}">
                            <input v-model="xx.table_id" type="hidden" id="table_id" value="{{ $main->id }}">
                            <input v-model="xx.record_status" type="hidden" id="record_status" value="{{ $main->status }}">
                            <input v-model="xx.user_id" type="hidden" id="user_id" value="{{ Auth::user()->id }}">
                            <input v-model="xx.user_fullname" type="hidden" id="fullname" value="{{ Auth::user()->fullname }}">
                            <input v-model="xx.company_id" type="hidden" id="company_id" value="{{ Auth::user()->company->reportsTo()->id }}">
                            <input v-model="xx.user_manager" type="hidden" id="user_manager" value="{{ Auth::user()->allowed2('sig.site.maintenance', $main) }}">
                            <input v-model="xx.user_supervisor" type="hidden" id="user_supervisor"
                                   value="{!! (in_array(Auth::user()->id, $main->site->areaSupervisors()->pluck('id')->toArray()) || $main->super_id == Auth::user()->id || Auth::user()->hasPermission2('sig.site.maintenance')) ? 1 : 0  !!}">
                            <input v-model="xx.user_signoff" type="hidden" id="user_signoff" value="{{ Auth::user()->hasPermission2('sig.site.maintenance') }}">
                            <input v-model="xx.user_edit" type="hidden" id="user_edit" value="{{ (Auth::user()->allowed2('edit.site.maintenance', $main) || $main->super_id == Auth::user()->id) ? 1 : 0 }}">


                            <!-- Fullscreen devices -->
                            @if ($main->status && $main->items->count() == $main->itemsChecked()->count())
                                <div class="col-md-12 note note-warning">
                                    <p>All items have been completed and request requires
                                        <button class="btn btn-xs btn-outline dark disabled">Sign Off</button>
                                        at the bottom
                                    </p>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-5">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h4>Site Details
                                                @if ($main->status > 0)
                                                    <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" id="edit-site">Edit</button>@endif
                                            </h4>
                                        </div>
                                    </div>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    @if ($main->site) <b>{{ $main->site->name }} (#{{ $main->site->code }})</b> @endif<br>
                                    @if ($main->site) {{ $main->site->full_address }}<br> @endif
                                    <br>
                                    @if ($main->completed)<b>Prac Completion:</b> {{ $main->completed->format('d/m/Y') }}<br> @endif
                                    <div id="site-show">
                                        @if ($main->supervisor)<b>Supervisor:</b> {{ $main->supervisor }} @endif
                                    </div>
                                    <div id="site-edit">
                                        <div class="form-group {!! fieldHasError('completed', $errors) !!}">
                                            {!! Form::label('completed', 'Prac Completed', ['class' => 'control-label']) !!}
                                            {!! Form::text('completed', ($main->completed) ? $main->completed->format('d/m/Y') : null, ['class' => 'form-control', 'placeholder' => 'dd/mm/yyyy']) !!}
                                            {!! fieldErrorMessage('completed', $errors) !!}
                                        </div>
                                        <div class="form-group {!! fieldHasError('supervisor', $errors) !!}">
                                            {!! Form::label('supervisor', 'Supervisor', ['class' => 'control-label']) !!}
                                            {!! Form::text('supervisor', null, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('supervisor', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1"></div>

                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h4>Client Details
                                                @if ($main->status > 0)
                                                    <button class="btn dark btn-outline btn-sm" style="margin: -10px 0px 0px 100px; border: 0px" id="edit-client">Edit</button>@endif
                                            </h4>
                                        </div>
                                        <div class="col-md-6">
                                            <h2 style="margin: 0px; padding-right: 20px">
                                                @if($main->status == '-1')
                                                    <span class="pull-right font-red hidden-sm hidden-xs">DECLINED</span>
                                                    <span class="text-center font-red visible-sm visible-xs">DECLINED</span>
                                                @endif
                                                @if($main->status == '0')
                                                    <span class="pull-right font-red hidden-sm hidden-xs"><small class="font-red">COMPLETED {{ $main->updated_at->format('d/m/Y') }}</small></span>
                                                    <span class="text-center font-red visible-sm visible-xs">COMPLETED {{ $main->updated_at->format('d/m/Y') }}</span>
                                                @endif
                                                @if($main->status == '1')
                                                    <span class="pull-right font-red hidden-sm hidden-xs">ACTIVE</span>
                                                    <span class="text-center font-red visible-sm visible-xs">ACTIVE</span>
                                                @endif
                                                @if($main->status == '2')
                                                    <span class="pull-right font-red hidden-sm hidden-xs">UNDER REVIEW</span>
                                                    <span class="text-center font-red visible-sm visible-xs">UNDER REVIEW</span>
                                                @endif
                                                @if($main->status == '3')
                                                    <span class="pull-right font-red hidden-sm hidden-xs">ON HOLD</span>
                                                    <span class="text-center font-red visible-sm visible-xs">ON HOLD</span>
                                                @endif
                                            </h2>
                                        </div>
                                    </div>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    <div id="client-show">
                                        @if ($main->contact_name) <b>{{ $main->contact_name }}</b> @endif<br>
                                        @if ($main->contact_phone) {{ $main->contact_phone }}<br> @endif
                                        @if ($main->contact_email) {{ $main->contact_email }}<br> @endif
                                        @if($main->nextClientVisit())
                                            <br><b>Scheduled Visit:</b> {{ $main->nextClientVisit()->company->name }} &nbsp; ({{ $main->nextClientVisit()->from->format('d/m/Y') }})<br>
                                        @endif
                                    </div>
                                    <div id="client-edit">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group {!! fieldHasError('contact_name', $errors) !!}">
                                                    {!! Form::label('contact_name', 'Name', ['class' => 'control-label']) !!}
                                                    {!! Form::text('contact_name', null, ['class' => 'form-control']) !!}
                                                    {!! fieldErrorMessage('contact_name', $errors) !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group {!! fieldHasError('contact_phone', $errors) !!}">
                                                    {!! Form::label('contact_phone', 'Phone', ['class' => 'control-label']) !!}
                                                    {!! Form::text('contact_phone', null, ['class' => 'form-control']) !!}
                                                    {!! fieldErrorMessage('contact_phone', $errors) !!}
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="form-group {!! fieldHasError('contact_email', $errors) !!}">
                                                    {!! Form::label('contact_email', 'Email', ['class' => 'control-label']) !!}
                                                    {!! Form::text('contact_email', null, ['class' => 'form-control']) !!}
                                                    {!! fieldErrorMessage('contact_email', $errors) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>


                            {{-- Gallery --}}
                            <br>
                            <div class="row" id="photos-show">
                                <div class="col-md-7">
                                    <h4>Photos
                                        @if(Auth::user()->allowed2('add.site.maintenance') || Auth::user()->allowed2('edit.site.maintenance', $main))
                                            <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" id="edit-photos">Edit</button>
                                        @endif</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    @include('site/maintenance/_gallery')
                                </div>
                                <div class="col-md-1"></div>
                                <div class="col-md-4" id="docs-show">
                                    <h4>Documents
                                        @if(Auth::user()->allowed2('add.site.maintenance') || Auth::user()->allowed2('edit.site.maintenance', $main))
                                            <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" id="edit-docs">Edit</button>
                                        @endif
                                    </h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    @include('site/maintenance/_docs')
                                </div>
                            </div>

                            <div id="photos-edit">
                                <h4>Photos / Documents
                                    @if(Auth::user()->allowed2('add.site.maintenance') || Auth::user()->allowed2('edit.site.maintenance', $main))
                                        <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" id="view-photos">View</button>
                                    @endif</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="note note-warning">
                                    Multiple photos/images can be uploaded with this maintenance request.
                                    <ul>
                                        <li>Once you have selected your files upload them by clicking
                                            <button class="btn dark btn-outline btn-xs" href="javascript:;"><i class="fa fa-upload"></i> Upload</button>
                                        </li>
                                    </ul>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="control-label">Select Files</label>
                                            <input id="multifile" name="multifile[]" type="file" multiple class="file-loading">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Under Review - asign to super --}}
                            <h4>Maintenance Details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                {{-- Goodwill --}}
                                {{--}}
                                <div class="col-md-2 ">
                                    <div class="form-group">
                                        {!! Form::label('goodwill', 'Goodwill', ['class' => 'control-label']) !!}
                                        @if ($main->status && Auth::user()->allowed2('sig.site.maintenance', $main))
                                            {!! Form::select('goodwill', ['1' => 'Yes', '0' => 'No'], $main->goodwill, ['class' => 'form-control bs-select', 'id' => 'goodwill']) !!}
                                        @else
                                            {!! Form::text('goodwill_text', ($main->goodwill) ? 'Yes' : 'No', ['class' => 'form-control', 'readonly']) !!}
                                        @endif
                                    </div>
                                </div>--}}

                                {{-- Category --}}
                                <div class="col-md-3 ">
                                    <div class="form-group">
                                        {!! Form::label('category_id', 'Category', ['class' => 'control-label']) !!}
                                        @if ($main->status && Auth::user()->allowed2('sig.site.maintenance', $main))
                                            {!! Form::select('category_id', (['' => 'Select category'] + \App\Models\Site\SiteMaintenanceCategory::all()->sortBy('name')->pluck('name' ,'id')->toArray()), null, ['class' => 'form-control select2', 'title' => 'Select category', 'id' => 'category_id']) !!}
                                        @else
                                            {!! Form::text('category_text', ($main->category_id) ? \App\Models\Site\SiteMaintenanceCategory::find($main->category_id)->name : 'Select Category', ['class' => 'form-control', 'readonly']) !!}
                                        @endif
                                    </div>
                                </div>

                                {{-- Warranty --}}
                                <div class="col-md-2 ">
                                    <div class="form-group">
                                        {!! Form::label('warranty', 'Warranty', ['class' => 'control-label']) !!}
                                        @if ($main->status && Auth::user()->allowed2('sig.site.maintenance', $main))
                                            {!! Form::select('warranty', $maintenanceWarranty::all(), $main->warranty, ['class' => 'form-control bs-select', 'id' => 'warranty']) !!}
                                        @else
                                            {!! Form::text('warranty_text', $maintenanceWarranty::name($main->warranty), ['class' => 'form-control', 'readonly']) !!}
                                        @endif
                                    </div>
                                </div>

                                {{-- Client Contacted --}}
                                <div class="col-md-2">
                                    {!! Form::label('client_contacted', 'Client Contacted', ['class' => 'control-label']) !!}
                                    @if ($main->status && Auth::user()->allowed2('edit.site.maintenance', $main))
                                    <div class="input-group" style="width=80%">
                                        <datepicker :value.sync="xx.client_contacted" format="dd/MM/yyyy" :placeholder="choose date"></datepicker>
                                    </div>
                                    <input v-model="xx.client_contacted" type="hidden" name="client_contacted" value="{{  ($main->client_contacted) ? $main->client_contacted->format('d/m/Y') : ''}}">
                                    @else
                                        {!! Form::text('client_contacted', ($main->client_contacted) ? $main->client_contacted->format('d/m/Y') : '', ['class' => 'form-control', 'readonly']) !!}
                                    @endif
                                </div>

                                {{-- Client Appointment --}}
                                <div class="col-md-2">
                                    {!! Form::label('client_appointment', 'Client Appointment', ['class' => 'control-label']) !!}
                                    @if ($main->status && Auth::user()->allowed2('edit.site.maintenance', $main))
                                        <div class="input-group">
                                            <datepicker :value.sync="xx.client_appointment" format="dd/MM/yyyy" :placeholder="choose date"></datepicker>
                                        </div>
                                        <input v-model="xx.client_appointment" type="hidden" name="client_appointment" value="{{  ($main->client_appointment) ? $main->client_appointment->format('d/m/Y') : ''}}">
                                    @else
                                        {!! Form::text('client_appointment', ($main->client_appointment) ? $main->client_appointment->format('d/m/Y') : '', ['class' => 'form-control', 'readonly']) !!}
                                    @endif
                                </div>

                                {{-- Status --}}
                                <div class="col-md-2 pull-right">
                                    <div class="form-group">
                                        {!! Form::label('status', 'Status', ['class' => 'control-label']) !!}
                                        @if ($main->status && Auth::user()->allowed2('sig.site.maintenance', $main))
                                            {!! Form::select('status', ['1' => 'Active', '-1' => 'Decline',  '3' => 'On Hold'], $main->status, ['class' => 'form-control bs-select', 'id' => 'status']) !!}
                                        @elseif ($main->status && Auth::user()->allowed2('edit.site.maintenance', $main))
                                            {!! Form::select('status', ['1' => 'Active', '3' => 'On Hold'], $main->status, ['class' => 'form-control bs-select', 'id' => 'status']) !!}
                                        @elseif ($main->status == 0 && Auth::user()->allowed2('edit.site.maintenance', $main))
                                            {!! Form::select('status', ['0' => 'Completed', '1' => 'Re-Activate'], $main->status, ['class' => 'form-control bs-select', 'id' => 'status']) !!}
                                        @else
                                            {!! Form::text('status_text', ($main->status == 0) ? 'Completed' : 'Declined', ['class' => 'form-control', 'readonly']) !!}
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row note note-warning" id="onhold-div" style="{{ fieldHasError('onhold_reason', $errors) ? 'display:show' : 'display:none' }}">
                                {{-- On Hold Reason --}}
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('onhold_reason', $errors) !!}" style="{{ fieldHasError('onhold_reason', $errors) ? '' : 'display:show' }}" id="onhold_reason-div">
                                        {!! Form::label('onhold_reason', 'Please specify the reason for placing request ON HOLD', ['class' => 'control-label']) !!}
                                        {!! Form::text('onhold_reason', null, ['class' => 'form-control', 'id' => 'onhold_reason']) !!}
                                        {!! fieldErrorMessage('onhold_reason', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                {{-- Assigned Supervisor --}}
                                <div class="col-md-5">
                                    <div class="form-group {!! fieldHasError('super_id', $errors) !!}" style="{{ fieldHasError('super_id', $errors) ? '' : 'display:show' }}" id="company-div">
                                        {!! Form::label('super_id', 'Maintenance Supervisor', ['class' => 'control-label']) !!}
                                        @if ($main->status && Auth::user()->allowed2('sig.site.maintenance', $main))
                                            {{-- Supervisor --}}
                                            <select id="super_id" name="super_id" class="form-control select2" style="width:100%">
                                                <option value=""></option>
                                                <optgroup label="Cape Code Supervisors"></optgroup>
                                                @foreach (Auth::user()->company->supervisors()->sortBy('name') as $super)
                                                    <option value="{{ $super->id }}" {{ ($super->id == $main->super_id) ? 'selected' : '' }}>{{ $super->name }}</option>
                                                @endforeach
                                                <optgroup label="External Users"></optgroup>
                                                <option value="75" {{ ('75' == $main->super_id) ? 'selected' : '' }}>Geoff Barbuto (G.B.T Carpentry Services)</option>
                                            </select>
                                            {!! fieldErrorMessage('super_id', $errors) !!}
                                        @else
                                            {!! Form::text('assigned_super_text', ($main->super_id) ? $main->taskOwner->name : 'Unassigned', ['class' => 'form-control', 'readonly']) !!}
                                        @endif
                                        {!! fieldErrorMessage('super_id', $errors) !!}
                                    </div>
                                </div>

                                {{-- AC Form --}}
                                @if (!$main->status)
                                    <div class="col-md-2 pull-right">
                                        {!! Form::label('ac_form_sent', 'AC Form Sent', ['class' => 'control-label']) !!}

                                        @if (Auth::user()->allowed2('add.site.maintenance'))
                                            <div class="input-group">
                                                <datepicker :value.sync="xx.ac_format_sent" format="dd/MM/yyyy" :placeholder="choose date"></datepicker>
                                                {{--}}<span class="input-group-btn"><button class="btn default" type="button"><i class="fa fa-calendar" style="line-height: 20px"></i></button></span>--}}
                                            </div>
                                            <input v-model="xx.ac_format_sent" type="hidden" name="ac_form_sent" value="{{  ($main->ac_form_sent) ? $main->ac_form_sent->format('d/m/Y') : ''}}">
                                        @else
                                            {!! Form::text('ac_form_sent', ($main->ac_form_sent) ? $main->ac_form_sent->format('d/m/Y') : '', ['class' => 'form-control', 'readonly']) !!}
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                {{-- Assigned To Company --}}
                                <div class="col-md-5">
                                    <div class="form-group {!! fieldHasError('assigned_to', $errors) !!}" style="{{ fieldHasError('assigned_to', $errors) ? '' : 'display:show' }}" id="company-div">
                                        {!! Form::label('assigned_to', 'Assigned to company', ['class' => 'control-label']) !!}
                                        @if ($main->status && Auth::user()->allowed2('edit.site.maintenance', $main))
                                            <select id="assigned_to" name="assigned_to" class="form-control select2" style="width:100%">
                                                <option value="">Select company</option>
                                                @foreach (Auth::user()->company->reportsTo()->companies('1')->sortBy('name') as $company)
                                                    <option value="{{ $company->id }}" {!! ($company->id == $main->assigned_to) ? 'selected' : ''  !!}>{{ $company->name }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            {!! Form::text('assigned_text', ($main->assignedTo) ? $main->assignedTo->name : 'Unassigned', ['class' => 'form-control', 'readonly']) !!}
                                        @endif
                                        {!! fieldErrorMessage('assigned_to', $errors) !!}
                                    </div>
                                </div>

                                <div class="col-md-1">&nbsp;</div>

                                @if (Auth::user()->allowed2('edit.site.maintenance', $main))
                                    <div class="col-md-1 pull-right">
                                        <button type="submit" name="save" class="btn blue" style="margin-top: 25px"> Save</button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- List Items --}}
                        <div class="row">
                            <div class="col-md-12">
                                <app-main></app-main>
                            </div>
                        </div>

                        {{--}}
                        <h4>Additional Info</h4>
                        <div class="row">
                            <div class="col-md-12 ">
                                <div class="form-group {!! fieldHasError('notes', $errors) !!}">
                                    {!! Form::textarea("notes", nl2br($main->notes), ['rows' => '5', 'class' => 'form-control', 'placeholder' => "Details."]) !!}
                                    {!! fieldErrorMessage('notes', $errors) !!}
                                </div>
                            </div>
                        </div>--}}


                        {{-- Actions --}}
                        <div class="row">
                            <div class="col-md-12">
                                <app-actions :table_id="{{ $main->id }}"></app-actions>
                            </div>
                        </div>
                        {!! Form::close() !!}

                        {{-- Sign Off --}}
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <h5><b>MAINTENANCE REQUEST ELECTRONIC SIGN-OFF</b></h5>
                                <p>The above maintenance items have been checked by the site construction supervisor and conform to the Cape Cod standard set.</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-3 text-right">Maintenance Supervisor:</div>
                            <div class="col-sm-9">
                                @if ($main->supervisor_sign_by)
                                    {!! \App\User::find($main->supervisor_sign_by)->full_name !!}, &nbsp;{{ $main->supervisor_sign_at->format('d/m/Y') }}
                                @else
                                    <button v-if="xx.main.items_total != 0 && xx.main.items_done == xx.main.items_total && xx.user_supervisor == 1" v-on:click="$root.$broadcast('signOff', 'super')"
                                            class=" btn blue btn-xs btn-outline sbold uppercase margin-bottom">Sign Off
                                    </button>
                                    <span v-if="xx.main.items_total != 0 && xx.main.items_done == xx.main.items_total && xx.user_supervisor == 0" class="font-red">Pending</span>
                                    <span v-if="xx.main.items_total != 0 && xx.main.items_done != xx.main.items_total" class="font-grey-silver">Waiting for items to be completed</span>
                                @endif
                            </div>
                            <div class="col-sm-3 text-right">Construction Manager:</div>
                            <div class="col-sm-9">
                                @if ($main->manager_sign_by)
                                    {!! \App\User::find($main->manager_sign_by)->full_name !!}, &nbsp;{{ $main->manager_sign_at->format('d/m/Y') }}
                                @else
                                    @if ($main->supervisor_sign_by)
                                        <button v-if="xx.main.items_total != 0 && xx.main.items_done == xx.main.items_total && (xx.user_manager == 1 || xx.user_signoff)"
                                                v-on:click="$root.$broadcast('signOff', 'manager')"
                                                class=" btn blue btn-xs btn-outline sbold uppercase margin-bottom">Sign Off
                                        </button>
                                        <span v-if="xx.main.items_total != 0 && xx.main.items_done == xx.main.items_total && xx.user_manager == 0 && !xx.user_signoff" class="font-red">Pending</span>
                                    @else
                                        <span v-if="xx.main.items_total != 0 && xx.main.items_done == xx.main.items_total" class="font-red">Waiting for Maintenance Supervisor Sign Off</span>
                                        <span v-if="xx.main.items_total != 0 && xx.main.items_done != xx.main.items_total" class="font-grey-silver">Waiting for items to be completed</span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <hr>
                        <div class="pull-right" style="min-height: 50px">
                            <a href="/site/maintenance" class="btn default"> Back</a>
                            @if (!$main->master && Auth::user()->allowed2('edit.site.main', $main))
                                <button v-if="xx.main.status == 1 && xx.main.items_total != 0 && xx.main.items_done != xx.main.items_total" class="btn blue"
                                        v-on:click="$root.$broadcast('updateReportStatus', 2)"> Place On Hold
                                </button>
                                <button v-if="xx.main.status == 2 || xx.main.status == -1 " class="btn green" v-on:click="$root.$broadcast('updateReportStatus', 1)"> Make Active</button>
                            @endif
                        </div>
                        <br><br>
                    </div>
                </div>
            </div>

        </div>
    </div>
    </div>

    <!--<pre v-if="xx.dev">@{{ $data | json }}</pre>
    -->

    <!-- loading Spinner -->
    <div v-show="xx.spinner" style="background-color: #FFF; padding: 20px;">
        <div class="loadSpinnerOverlay">
            <div class="loadSpinner"><i class="fa fa-spinner fa-pulse fa-2x fa-fw margin-bottom"></i> Loading...</div>
        </div>
    </div>

    <template id="main-template">
        <!-- QA Items -->
        <table v-show="xx.itemList.length" class="table table-striped table-bordered table-nohover order-column">
            <thead>
            <tr class="mytable-header">
                <th width="5%"></th>
                <th> Maintenance Item</th>
                <th width="15%"> Completed</th>
                {{--}}<th width="15%"> Checked</th>--}}
            </tr>
            </thead>
            <tbody>
            <template v-for="item in xx.itemList | orderBy item.order">
                <tr class="@{{ textColour(item)  }}">
                    {{-- checkbox --}}
                    <td class="text-center" style="padding-top: 15px">
                        <span v-if="item.status == '-1'">N/A</span>
                        <i v-if="item.done_by" class="fa fa-check-square-o font-green" style="font-size: 20px; padding-top: 5px"></i>
                        <i v-if="!item.done_by && !item.status" class="fa fa-square-o font-red" style="font-size: 20px; padding-top: 5px"></i>
                    </td>
                    {{-- Item --}}
                    <td style="padding-top: 15px;">@{{ item.name }}</td>
                    {{-- Completed --}}
                    <td>
                        <div v-if="item.done_by">
                            @{{ item.done_at | formatDate }}<br>@{{ item.done_by_name }} <a v-if="xx.main.status != 0 && xx.main.signed != 1" v-on:click="itemStatusReset(item)"><i class="fa fa-times font-red"></i></a>
                        </div>
                        <div v-else>
                            <select v-if="!item.done_by && xx.user_edit == 1 && xx.main.signed == 0" v-model="item.status" class='form-control' v-on:change="itemStatus(item)">
                                <option v-for="option in xx.sel_checked" value="@{{ option.value }}" selected="@{{option.value == item.status}}">@{{ option.text }}</option>
                            </select>
                        </div>

                    </td>
                    {{-- Checked --}}
                    {{--}}
                    <td>
                        <div v-if="!item.done_by"></div>
                        <div v-if="item.done_by">
                            <div v-if="item.sign_by">
                                @{{ item.sign_at | formatDate }}<br>@{{ item.sign_by_name }} <a v-if="xx.main.status != 0" v-on:click="itemSignReset(item)"><i class="fa fa-times font-red"></i></a>
                            </div>
                            <div v-else>
                                <select v-if="xx.user_supervisor == 1 && xx.main.signed == 0" v-model="item.super" class='form-control' v-on:change="itemSign(item)">
                                    <option v-for="option in xx.sel_checked2" value="@{{ option.value }}">@{{ option.text }}</option>
                                </select>
                            </div>
                        </div>
                    </td>--}}
                </tr>
            </template>
            </tbody>
        </table>
        <!--
           Confirm Item Checked Modal
         -->
        <confirm-Signoff :show.sync="xx.showSignOff" effect="fade">
            <div slot="modal-header" class="modal-header">
                <h4 class="modal-title text-center"><b>Update Item Company</b></h4>
            </div>
            <div slot="modal-body" class="modal-body">
                <p><b>@{{ xx.record.name }}</b></p>
                Completed by
                <div class="row" style="padding-bottom: 10px">
                    <div class="col-md-8">
                        <select-picker :name.sync="xx.done_by" :options.sync="xx.sel_company" :function="doNothing"></select-picker>
                    </div>
                </div>
            </div>
            <div slot="modal-footer" class="modal-footer">
                <button type="button" class="btn dark btn-outline" v-on:click="xx.showSignOff = false">&nbsp; No &nbsp;</button>
                <button type="button" class="btn btn-success" v-on:click="updateItemCompany(xx.record, true)" :disabled="! xx.done_by"
                ">&nbsp; Save &nbsp;</button>
            </div>
        </confirm-Signoff>
    </template>


    <template id="actions-template">
        <action-modal></action-modal>
        <input v-model="xx.table_id" type="hidden" id="table_id" value="{{ $main->id }}">
        <input v-model="xx.created_by" type="hidden" id="created_by" value="{{ Auth::user()->id }}">
        <input v-model="xx.created_by_fullname" type="hidden" id="fullname" value="{{ Auth::user()->fullname }}">

        <div class="page-content-inner">
            <div class="row">
                <div class="col-md-12">
                    <h3>Notes
                        {{-- Show add if user has permission to edit maintenance --}}
                        {{--}}@if (Auth::user()->allowed2('edit.site.main', $main)) --}}
                        <button v-show="xx.record_status == '1'" v-on:click="$root.$broadcast('add-action-modal')" class="btn btn-circle green btn-outline btn-sm pull-right" data-original-title="Add">Add</button>
                        {{--}}@endif --}}
                    </h3>
                    <table v-show="actionList.length" class="table table-striped table-bordered table-nohover order-column">
                        <thead>
                        <tr class="mytable-header">
                            <th width="10%">Date</th>
                            <th> Action</th>
                            <th width="20%"> Name</th>
                            <th width="5%"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <template v-for="action in actionList">
                            <tr>
                                <td>@{{ action.niceDate }}</td>
                                <td>@{{ action.action }}</td>
                                <td>@{{ action.fullname }}</td>
                                <td>
                                    <!--<button v-show="xx.record_status != 0" class=" btn blue btn-xs btn-outline sbold uppercase margin-bottom">
                                        <i class="fa fa-plus"></i> <span class="hidden-xs hidden-sm>"> Assign Task</span>
                                    </button>-->
                                    <!--
                                    <button v-show="action.created_by == xx.created_by" v-on:click="$root.$broadcast('edit-action-modal', action)"
                                            class=" btn blue btn-xs btn-outline sbold uppercase margin-bottom">
                                        <i class="fa fa-pencil"></i> <span class="hidden-xs hidden-sm>">Edit</span>
                                    </button>
                                    -->
                                </td>
                            </tr>
                        </template>
                        </tbody>
                    </table>

                    <!--<pre v-if="xx.dev">@{{ $data | json }}</pre> -->

                </div>
            </div>
        </div>
    </template>

    @include('misc/actions-modal')

@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
    <script type="text/javascript">var html5lightbox_options = {watermark: "", watermarklink: ""};</script>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/js/libs/fileinput.min.js"></script>
    <script src="/js/moment.min.js" type="text/javascript"></script>
    <!--<script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>-->
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/js/libs/vue.1.0.24.js " type="text/javascript"></script>
<script src="/js/libs/vue-strap.min.js"></script>
<script src="/js/libs/vue-resource.0.7.0.js " type="text/javascript"></script>
<script src="/js/vue-modal-component.js"></script>
<script src="/js/vue-app-basic-functions.js"></script>

<script>
    $.ajaxSetup({
        headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
    });

    $(document).ready(function () {
        /* Select2 */
        $("#super_id").select2({placeholder: "Select supervisor", width: '100%'});
        $("#assigned_to").select2({placeholder: "Select company", width: '100%'});
        $("#category_id").select2({placeholder: "Select category", width: "100%"});

        $("#status").change(function () {
            $('#onhold-div').hide();

            if ($("#status").val() == '3') {
                $('#onhold-div').show();
            }
        });

        $("#warranty").change(function () {
            //alert('gg');
            $('#goodwill-div').hide();

            if ($("warranty").val() == 'other') {
                $('#goodwill-div').show();
            }
        });

        $('#site-edit').hide();
        $('#client-edit').hide();
        $('#photos-edit').hide();

        $("#edit-site").click(function (e) {
            e.preventDefault();
            $('#edit-site').hide();
            $('#site-show').hide();
            $('#site-edit').show();
        });

        $("#edit-client").click(function (e) {
            e.preventDefault();
            $('#edit-client').hide();
            $('#client-show').hide();
            $('#client-edit').show();
        });
        $("#edit-photos").click(function (e) {
            e.preventDefault();
            $('#photos-show').hide();
            $('#photos-edit').show();
        });
        $("#edit-docs").click(function (e) {
            e.preventDefault();
            $('#photos-show').hide();
            $('#photos-edit').show();
        });
        $("#view-photos").click(function (e) {
            e.preventDefault();
            $('#photos-show').show();
            $('#photos-edit').hide();
        });

        /* Bootstrap Fileinput */
        $("#multifile").fileinput({
            uploadUrl: "/site/maintenance/upload/", // server upload action
            uploadAsync: true,
            //allowedFileExtensions: ["image"],
            //allowedFileTypes: ["image"],
            browseClass: "btn blue",
            browseLabel: "Browse",
            browseIcon: "<i class=\"fa fa-folder-open\"></i> ",
            //removeClass: "btn red",
            removeLabel: "",
            removeIcon: "<i class=\"fa fa-trash\"></i> ",
            uploadClass: "btn dark",
            uploadIcon: "<i class=\"fa fa-upload\"></i> ",
            uploadExtraData: {
                "site_id": site_id,
                "main_id": main_id,
            },
            layoutTemplates: {
                main1: '<div class="input-group {class}">\n' +
                '   {caption}\n' +
                '   <div class="input-group-btn">\n' +
                '       {remove}\n' +
                '       {upload}\n' +
                '       {browse}\n' +
                '   </div>\n' +
                '</div>\n' +
                '<div class="kv-upload-progress hide" style="margin-top:10px"></div>\n' +
                '{preview}\n'
            },
        });

        $('#multifile').on('filepreupload', function (event, data, previewId, index, jqXHR) {
            data.form.append("site_id", $("#site_id").val());
            data.form.append("main_id", $("#main_id").val());
        });
    });
</script>
<script>
    var xx = {
        dev: dev,
        main: {id: '', name: '', site_id: '', status: '', warranty: '', signed: '', items_total: 0, items_done: 0},
        spinner: false, showSignOff: false, showAction: false,
        record: {},
        action: '', loaded: false,
        table_name: 'site_maintenance', table_id: '', record_status: '', record_resdate: '',
        created_by: '', created_by_fullname: '',
        done_by: '',
        itemList: [],
        actionList: [], sel_checked: [], sel_checked2: [], sel_company: [],
        ac_format_sent: '', client_contacted: '', client_appointment: ''
    };

    //
    // QA Items
    //
    Vue.component('app-main', {
        template: '#main-template',

        created: function () {
            this.getMain();
        },
        data: function () {
            return {xx: xx};
        },
        events: {
            'updateReportStatus': function (status) {
                this.xx.main.status = status;
                this.updateReportDB(this.xx.main, true);
            },
            'signOff': function (type) {
                this.xx.main.signoff = type;
                this.updateReportDB(this.xx.main, true);
            },
        },
        components: {
            confirmSignoff: VueStrap.modal,
        },
        filters: {
            formatDate: function (date) {
                return moment(date).format('DD/MM/YYYY');
            },
        },
        methods: {
            getMain: function () {
                this.xx.spinner = true;
                setTimeout(function () {
                    this.xx.load_plan = true;
                    $.getJSON('/site/maintenance/' + this.xx.main.id + '/items', function (data) {
                        this.xx.itemList = data[0];
                        this.xx.sel_checked = data[1];
                        this.xx.sel_checked2 = data[2];
                        this.xx.spinner = false;
                        this.itemsCompleted();
                    }.bind(this));
                }.bind(this), 100);
            },
            itemsCompleted: function () {
                this.xx.main.items_total = 0;
                this.xx.main.items_done = 0;
                for (var i = 0; i < this.xx.itemList.length; i++) {
                    if ((this.xx.itemList[i]['done_by'])) { // || this.xx.itemList[i]['status'] == -1)) && this.xx.itemList[i]['sign_by']) {
                        this.xx.main.items_done++;
                    }
                    this.xx.main.items_total++;
                }
            },
            itemStatus: function (record) {
                if (record.status == '1') {
                    record.done_at = moment().format('YYYY-MM-DD');
                    record.done_by = this.xx.user_id;
                    record.done_by_name = this.xx.user_fullname;
                }
                this.updateItemDB(record);
            },
            itemStatusReset: function (record) {
                record.status = '';
                record.done_at = '';
                record.done_by = '';
                record.done_by_name = '';
                this.updateItemDB(record);
            },
            itemSign: function (record) {
                if (record.super == '1') {
                    record.sign_at = moment().format('YYYY-MM-DD');
                    record.sign_by = this.xx.user_id;
                    record.sign_by_name = this.xx.user_fullname;
                    this.updateItemDB(record);
                } else
                    this.itemStatusReset(record);
            },
            itemSignReset: function (record) {
                record.sign_at = '';
                record.sign_by = '';
                record.sign_by_name = '';
                this.updateItemDB(record);
            },
            /*
             itemCompany: function (record) {
             this.xx.sel_company = [];
             // Get Company list
             $.getJSON('/site/qa/company/' + record.task_id, function (companies) {
             this.xx.sel_company = companies;
             this.xx.done_by = record.done_by;
             this.xx.showSignOff = true;
             this.xx.record = record;

             }.bind(this));
             },*/
            updateItemCompany: function (record, response) {
                if (response) {
                    record.done_by = this.xx.done_by;
                    //alert('by:'+record.done_by);

                    // Get company name + licence from dropdown menu array
                    var company = objectFindByKey(this.xx.sel_company, 'value', record.done_by);
                    record.done_by_company = company.text;
                    record.dony_by_licence = company.licence;

                    // Get original item from list
                    var obj = objectFindByKey(this.xx.itemList, 'id', record.id);
                    obj = record;
                    this.updateItemDB(obj);
                }
                this.xx.record = {};
                this.xx.done_by = '';
                this.xx.showSignOff = false;
            },
            updateItemDB: function (record) {
                //alert('update item id:'+record.id+' task:'+record.task_id+' by:'+record.done_by);
                this.$http.patch('/site/maintenance/item/' + record.id, record)
                        .then(function (response) {
                            this.itemsCompleted();
                            toastr.success('Updated record');
                        }.bind(this))
                        .catch(function (response) {
                            record.status = '';
                            record.done_at = '';
                            record.done_by = '';
                            record.done_by_name = '';
                            alert('failed to update item');
                        });
            },
            updateReportDB: function (record, redirect) {
                this.$http.patch('/site/maintenance/' + record.id + '/update', record)
                        .then(function (response) {
                            this.itemsCompleted();
                            //if (redirect)
                            //    window.location.href = '/site/maintenance/' + record.id;
                            toastr.success('Updated record');

                        }.bind(this)).catch(function (response) {
                    alert('failed to update report');
                });
            },
            textColour: function (record) {
                if (record.status == '-1')
                    return 'font-grey-silver';
                if (record.status == '0' && record.signed_by != '0')
                    return 'leaveBG';
                return '';
            },
            doNothing: function () {
                //
            },
        },
    });


    Vue.component('app-actions', {
        template: '#actions-template',
        props: ['table', 'table_id', 'status'],

        created: function () {
            this.getActions();
        },
        data: function () {
            return {xx: xx, actionList: []};
        },
        events: {
            'addActionEvent': function (action) {
                //this.actionList.push(action);
                this.actionList.unshift(action);
            },
        },
        methods: {
            getActions: function () {
                $.getJSON('/action/' + this.xx.table_name + '/' + this.table_id, function (actions) {
                    this.actionList = actions;
                }.bind(this));
            },
        },
    });

    Vue.component('ActionModal', {
        template: '#actionModal-template',
        props: ['show'],
        data: function () {
            var action = {};
            return {xx: xx, action: action, oAction: ''};
        },
        events: {
            'add-action-modal': function () {
                var newaction = {};
                this.oAction = '';
                this.action = newaction;
                this.xx.action = 'add';
                this.show = true;
            },
            'edit-action-modal': function (action) {
                this.oAction = action.action;
                this.action = action;
                this.xx.action = 'edit';
                this.show = true;
            }
        },
        methods: {
            close: function () {
                this.show = false;
                this.action.action = this.oAction;
            },
            addAction: function (action) {
                var actiondata = {
                    action: action.action,
                    table: this.xx.table_name,
                    table_id: this.xx.table_id,
                    niceDate: moment().format('DD/MM/YY'),
                    created_by: this.xx.created_by,
                    fullname: this.xx.created_by_fullname,
                };

                console.log(actiondata);
                this.$http.post('/action', actiondata)
                        .then(function (response) {
                            toastr.success('Created new action ');
                            actiondata.id = response.data.id;
                            this.$dispatch('addActionEvent', actiondata);
                        }.bind(this))
                        .catch(function (response) {
                            alert('failed adding new action');
                        });

                this.close();
            },
            updateAction: function (action) {
                this.$http.patch('/action/' + action.id, action)
                        .then(function (response) {
                            toastr.success('Saved Action');
                        }.bind(this))
                        .catch(function (response) {
                            alert('failed to save action [' + action.id + ']');
                        });
                this.show = false;
            },
        }
    });


    var myApp = new Vue({
        el: 'body',
        data: {xx: xx},
        components: {
            datepicker: VueStrap.datepicker,
        },
    });
</script>
@stop

