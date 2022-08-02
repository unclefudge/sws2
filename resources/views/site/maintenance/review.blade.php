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

    .file-preview {
        height: 250px !important;
    }

    ..file-drop-zone {
        height: 250px !important;
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
                    <div class="portlet-body form">
                        <div class="page-content-inner">
                            {!! Form::model($main, ['action' => ['Site\SiteMaintenanceController@review', $main->id], 'class' => 'horizontal-form', 'files' => true]) !!}
                            <input type="hidden" name="main_id" id="main_id" value="{{ $main->id }}">
                            <input type="hidden" name="site_id" id="site_id" value="{{ $main->site_id }}">
                            @include('form-error')

                            {{-- Progress Steps --}}
                            <div class="mt-element-step hidden-sm hidden-xs">
                                <div class="row step-thin" id="steps">
                                    <div class="col-md-4 mt-step-col first done">
                                        <div class="mt-step-number bg-white font-grey">1</div>
                                        <div class="mt-step-title uppercase font-grey-cascade">Create</div>
                                        <div class="mt-step-content font-grey-cascade">Create request</div>
                                    </div>
                                    <div class="col-md-4 mt-step-col done">
                                        <div class="mt-step-number bg-white font-grey">2</div>
                                        <div class="mt-step-title uppercase font-grey-cascade">Documents</div>
                                        <div class="mt-step-content font-grey-cascade">Add Photos/Documents</div>
                                    </div>
                                    <div class="col-md-4 mt-step-col last active">
                                        <div class="mt-step-number bg-white font-grey">3</div>
                                        <div class="mt-step-title uppercase font-grey-cascade">Assign</div>
                                        <div class="mt-step-content font-grey-cascade">Assign supervisor</div>
                                    </div>

                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-5">
                                    <h4>Site Details
                                        @if(Auth::user()->allowed2('add.site.maintenance') || Auth::user()->allowed2('edit.site.maintenance', $main))
                                            <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" id="edit-site">Edit</button>
                                        @endif
                                    </h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    @if ($main->site) <b>{{ $main->site->name }} (#{{ $main->site->code }})</b> @endif<br>
                                    @if ($main->site) {{ $main->site->full_address }}<br> @endif
                                    {{--@if ($main->site && $main->site->client_phone) {{ $main->site->client_phone }} ({{ $main->site->client_phone_desc }})  @endif --}}
                                    <br>
                                    <div id="site-show">
                                        @if ($main->reported)<b>Reported:</b> {{ $main->reported->format('d/m/Y') }}<br> @endif
                                        @if ($main->completed)<b>Prac Completion:</b> {{ $main->completed->format('d/m/Y') }}<br> @endif
                                        @if ($main->supervisor)<b>Supervisor:</b> {{ $main->supervisor }} @endif
                                    </div>
                                    <div id="site-edit">
                                        <div class="form-group {!! fieldHasError('reported', $errors) !!}">
                                            {!! Form::label('reported', 'Reported', ['class' => 'control-label']) !!}
                                            {!! Form::text('reported', ($main->reported) ? $main->reported->format('d/m/Y') : null, ['class' => 'form-control', 'placeholder' => 'dd/mm/yyyy']) !!}
                                            {!! fieldErrorMessage('reported', $errors) !!}
                                        </div>
                                        <div class="form-group {!! fieldHasError('completed', $errors) !!}">
                                            {!! Form::label('completed', 'Prac Completed', ['class' => 'control-label']) !!}
                                            {!! Form::text('completed', ($main->completed) ? $main->completed->format('d/m/Y') : null, ['class' => 'form-control', 'placeholder' => 'dd/mm/yyyy']) !!}
                                            {!! fieldErrorMessage('completed', $errors) !!}
                                        </div>
                                        <div class="form-group {!! fieldHasError('supervisor', $errors) !!}">
                                            {!! Form::label('supervisor', 'Supervisor', ['class' => 'control-label']) !!}
                                            {!! Form::text('supervisor', $main->supervisor, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('supervisor', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1"></div>

                                {{-- Client Contact --}}
                                <div class="col-md-6">
                                    <h4>Client Details
                                        @if(Auth::user()->allowed2('add.site.maintenance') || Auth::user()->allowed2('edit.site.maintenance', $main))
                                            <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" id="edit-client">Edit</button>
                                        @endif
                                    </h4>
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

                            {{-- Gallery --}}
                            <br>
                            <div class="row"  id="photos-show">
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

                            {{-- Maintenance details --}}
                            <h4>Maintenance Details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                {{-- Category --}}
                                <div class="col-md-3 ">
                                    <div class="form-group">
                                        {!! Form::label('category_id', 'Category', ['class' => 'control-label']) !!}
                                        @if ($main->status && Auth::user()->allowed2('edit.site.maintenance', $main))
                                            {!! Form::select('category_id', (['' => 'Select category'] + \App\Models\Site\SiteMaintenanceCategory::all()->sortBy('name')->pluck('name' ,'id')->toArray()), null, ['class' => 'form-control select2', 'title' => 'Select category', 'id' => 'category_id']) !!}
                                        @else
                                            {!! Form::text('category_text', $main->category->name, ['class' => 'form-control', 'readonly']) !!}
                                        @endif
                                    </div>
                                </div>

                                {{-- Warranty --}}
                                <div class="col-md-2 ">
                                    <div class="form-group">
                                        {!! Form::label('warranty', 'Warranty', ['class' => 'control-label']) !!}
                                        @if ($main->status && Auth::user()->allowed2('edit.site.maintenance', $main))
                                            {!! Form::select('warranty', $maintenanceWarranty::all(), $main->warranty, ['class' => 'form-control bs-select', 'id' => 'warranty']) !!}
                                        @else
                                            {!! Form::text('warranty_text', $maintenanceWarranty::name($main->warranty), ['class' => 'form-control', 'readonly']) !!}
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if(!$main->super_id)
                                {{-- Under Review - asign to super --}}
                                <div class="note note-warning">
                                    <h4>Assign Request to Maintenance Supervisor</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px; border-color: #000000">
                                    <input type="hidden" name="visited" value="0">

                                    @if(Auth::user()->allowed2('sig.site.maintenance', $main))
                                        <div class="row">
                                            <div class="col-md-5">
                                                {{-- Supervisor --}}
                                                <div class="form-group {!! fieldHasError('super_id', $errors) !!}" style="{{ fieldHasError('company_id', $errors) ? '' : 'display:show' }}" id="company-div">
                                                    {!! Form::label('super_id', 'Assign to', ['class' => 'control-label']) !!}
                                                    <select id="super_id" name="super_id" class="form-control select2" style="width:100%">
                                                        <option value=""></option>
                                                        <optgroup label="Cape Code Supervisors"></optgroup>
                                                        @foreach (Auth::user()->company->supervisors()->sortBy('name') as $super)
                                                            <option value="{{ $super->id }}">{{ $super->name }}</option>
                                                        @endforeach
                                                        <optgroup label="External Users"></optgroup>
                                                        <option value="75">Geoff Barbuto (G.B.T Carpentry Services)</option>
                                                    </select>
                                                    {!! fieldErrorMessage('super_id', $errors) !!}
                                                </div>
                                            </div>

                                            {{-- Planner Date --}}
                                            {{--}}
                                            <div class="col-md-3 ">
                                                <div class="form-group {!! fieldHasError('visit_date', $errors) !!}">
                                                    {!! Form::label('visit_date', 'Visit Date', ['class' => 'control-label']) !!}
                                                    <div class="input-group input-medium date date-picker" data-date-format="dd/mm/yyyy" data-date-start-date="+0d" data-date-reset>
                                                        <input type="text" class="form-control" value="{!! nextWorkDate(\Carbon\Carbon::today(), '+', 3)->format('d/m/Y') !!}" readonly style="background:#FFF" id="visit_date" name="visit_date">
                                            <span class="input-group-btn">
                                                <button class="btn default" type="button">
                                                    <i class="fa fa-calendar"></i>
                                                </button>
                                            </span>
                                                    </div>
                                                </div>
                                            </div> --}}
                                        </div>
                                    @else
                                        <div class="row">
                                            <div class="col-md-7">
                                                Waiting to be assigned by authorised supervisor.
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                {{-- Under Review - client appointment set --}}
                                {{--}}
                                    <input type="hidden" name="company_id" value="{{ $main->nextClientVisit()->company->id }}">
                                    <input type="hidden" name="visit_date" value="{{ $main->nextClientVisit()->from->format('d/m/Y') }}">
                                    <input type="hidden" name="visited" value="1"> --}}
                            @endif
                        </div>


                        {{-- Items --}}
                        <h4>Maintenance Item</h4>
                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                        <div class="row">
                            {{-- Item Details  --}}
                            <div class="col-md-12 ">
                                <div class="form-group {!! fieldHasError('item1', $errors) !!}">
                                    {!! Form::label('item1', 'Item details', ['class' => 'control-label']) !!}
                                    {!! Form::textarea("item1", $main->items->first()->name, ['rows' => '5', 'class' => 'form-control', 'placeholder' => "Specific details of maintenance request."]) !!}
                                    {!! fieldErrorMessage('item1', $errors) !!}
                                </div>
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


                        {{-- Notes --}}
                        <div class="row">
                            <div class="col-md-12">
                                <app-actions :table_id="{{ $main->id }}"></app-actions>
                            </div>
                        </div>

                        <hr>
                        <div class="pull-right" style="min-height: 50px">
                            <a href="/site/maintenance" class="btn default"> Back</a>
                            @if(Auth::user()->allowed2('edit.site.maintenance', $main))
                                @if ($main->step == 3 && Auth::user()->allowed2('sig.site.maintenance', $main))
                                    <button type="submit" name="save" class="btn blue"> Assign Request</button>
                                @elseif (Auth::user()->allowed2('edit.site.maintenance', $main))
                                    <button type="submit" name="save" class="btn blue"> Save</button>
                                @endif
                            @endif
                        </div>
                        <br><br>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>

    </div>
    </div>
    </div>

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
                        @if (Auth::user()->allowed2('edit.site.maintenance', $main))
                            <button v-on:click.prevent="$root.$broadcast('add-action-modal')" class="btn btn-circle green btn-outline btn-sm pull-right" data-original-title="Add">Add</button>
                        @endif
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
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>
    <!--<link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>-->
    <script type="text/javascript">var html5lightbox_options = {watermark: "", watermarklink: ""};</script>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/js/libs/fileinput.min.js"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/js/moment.min.js" type="text/javascript"></script>
    <script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
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
        $("#company_id").select2({placeholder: "Select Company", width: '100%'});
        $("#category_id").select2({placeholder: "Select category", width: "100%"});
        $("#assigned_to").select2({placeholder: "Select Company", width: '100%'});
        $("#super_id").select2({placeholder: "Select Supervisor", width: "100%"});

        /*
         $("#more").click(function (e) {
         e.preventDefault();
         $('#more').hide();
         $('#more_items').show();
         });*/

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
    var host = window.location.hostname;
    var dev = true;
    if (host == 'safeworksite.com.au')
        dev = false;

    var xx = {
        dev: dev,
        action: '', loaded: false,
        table_name: 'site_maintenance', table_id: '', record_status: '', record_resdate: '',
        created_by: '', created_by_fullname: '',
    };

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
    });
</script>
@stop

