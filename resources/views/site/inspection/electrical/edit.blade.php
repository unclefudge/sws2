@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site.inspection'))
            <li><a href="/site/inspection/electrical">Electrical Inspection Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Edit Report</span></li>
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
                            <span class="caption-subject font-green-haze bold uppercase">Electrical Inspection Report</span>
                            <span class="caption-helper"> ID: {{ $report->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model($report, ['method' => 'PATCH', 'action' => ['Site\SiteInspectionElectricalController@update', $report->id], 'class' => 'horizontal-form', 'files' => true]) !!}
                        <input type="hidden" name="report_id" id="report_id" value="{{ $report->id }}">
                        <input type="hidden" name="site_id" id="site_id" value="{{ $report->site_id }}">

                        @include('form-error')

                        @if (!$report->assigned_to)
                            {{-- Progress Steps --}}
                            <div class="mt-element-step hidden-sm hidden-xs">
                                <div class="row step-thin" id="steps">
                                    <div class="col-md-6 mt-step-col first done">
                                        <div class="mt-step-number bg-white font-grey">1</div>
                                        <div class="mt-step-title uppercase font-grey-cascade">Create</div>
                                        <div class="mt-step-content font-grey-cascade">Create report</div>
                                    </div>
                                    <div class="col-md-6 mt-step-col last active">
                                        <div class="mt-step-number bg-white font-grey">2</div>
                                        <div class="mt-step-title uppercase font-grey-cascade">Assign</div>
                                        <div class="mt-step-content font-grey-cascade">Assign company</div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                        @endif

                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('site_id', 'Site', ['class' => 'control-label']) !!}
                                        {!! Form::text('site_name', $report->site->name, ['class' => 'form-control', 'readonly']) !!}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        {!! Form::label('site_code', 'Job #', ['class' => 'control-label']) !!}
                                        {!! Form::text('site_code', $report->site->code, ['class' => 'form-control', 'readonly']) !!}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h2 style="margin: 0px; padding-right: 20px">
                                        @if($report->status == '0')
                                            <span class="pull-right font-red hidden-sm hidden-xs"><small class="font-red">COMPLETED {{ $report->updated_at->format('d/m/Y') }}</small></span>
                                            <span class="text-center font-red visible-sm visible-xs">COMPLETED {{ $report->updated_at->format('d/m/Y') }}</span>
                                        @endif
                                        @if($report->status == '1' && $report->assigned_to)
                                            <span class="pull-right font-red hidden-sm hidden-xs">ACTIVE</span>
                                            <span class="text-center font-red visible-sm visible-xs">ACTIVE</span>
                                        @endif
                                    </h2>
                                </div>
                            </div>

                            <h4 class="font-green-haze">Client details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group {!! fieldHasError('client_name', $errors) !!}">
                                        {!! Form::label('client_name', 'Name', ['class' => 'control-label']) !!}
                                        {!! Form::text('client_name', null, ['class' => 'form-control', (Auth::user()->allowed2('add.site.inspection')) ? '' : 'readonly']) !!}
                                        {!! fieldErrorMessage('client_name', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="form-group {!! fieldHasError('client_address', $errors) !!}">
                                        {!! Form::label('client_address', 'Address', ['class' => 'control-label']) !!}
                                        {!! Form::text('client_address', null, ['class' => 'form-control', (Auth::user()->allowed2('add.site.inspection')) ? '' : 'readonly']) !!}
                                        {!! fieldErrorMessage('client_address', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <b>Client Primary Contact</b><br>
                                    {!! $report->site->client1_name ? $report->site->client1_name . "<br>" : '' !!}
                                    {!! ($report->site->client1_mobile) ? $report->site->client1_mobile . "<br>" : "" !!}
                                    {!! ($report->site->client1_email) ? "<a href='mailto:". $report->site->client1_email."'> " . $report->site->client1_email ."</a>" : "" !!}
                                </div>
                                <div class="col-md-6">
                                    <b>Secondary Contact</b><br>
                                    {!! $report->site->client2_name ? $report->site->client2_name . "<br>" : '' !!}
                                    {!! ($report->site->client2_mobile) ? $report->site->client2_mobile . "<br>" : "" !!}
                                    {!! ($report->site->client2_email) ? "<a href='mailto:". $report->site->client2_email."'> " . $report->site->client2_email ."</a>" : "" !!}
                                </div>
                            </div>

                            <h4 class="font-green-haze">Admin Notes</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-md-12 ">
                                    <div class="form-group {!! fieldHasError('info', $errors) !!}">
                                        {!! Form::textarea("info", $report->info, ['rows' => '5', 'class' => 'form-control', 'placeholder' => "Details",  (Auth::user()->allowed2('add.site.inspection')) ? '' : 'readonly']) !!}
                                        {!! fieldErrorMessage('info', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- Gallery --}}
                            <br>
                            <div class="row" id="photos-show">
                                <div class="col-md-7">
                                    <h4>Photos
                                        @if(Auth::user()->allowed2('add.site.inspection') || Auth::user()->allowed2('edit.site.inspection', $report))
                                            <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" id="edit-photos">Edit</button>
                                        @endif</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    @include('site/inspection/_gallery')
                                </div>
                                <div class="col-md-1"></div>
                                <div class="col-md-4" id="docs-show">
                                    <h4>Documents
                                        @if(Auth::user()->allowed2('add.site.inspection') || Auth::user()->allowed2('edit.site.inspection', $report))
                                            <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" id="edit-docs">Edit</button>
                                        @endif
                                    </h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    @include('site/inspection/_docs')
                                </div>
                            </div>

                            <div id="photos-edit">
                                <h4 class="font-green-haze">Photos / Documents
                                    @if(Auth::user()->allowed2('add.site.maintenance') || Auth::user()->allowed2('edit.site.maintenance', $report))
                                        <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" id="view-photos">View</button>
                                    @endif</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-6" style="background: #f1f0ef">
                                        <input type="file" class="filepond" name="filepond[]" multiple/><br><br>
                                    </div>
                                </div>
                                <br>
                            </div>

                            <h4 class="font-green-haze">Inspection details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                {{-- Assigned To Company --}}
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('assigned_to', $errors) !!}" style="{{ fieldHasError('assigned_to', $errors) ? '' : 'display:show' }}" id="company-div">
                                        {!! Form::label('assigned_to', 'Assigned to company', ['class' => 'control-label']) !!}
                                        @if(Auth::user()->allowed2('sig.site.inspection'))
                                            <select id="assigned_to" name="assigned_to" class="form-control bs-select" style="width:100%">
                                                @if (!$report->assigned_to)
                                                    <option value="">Select company</option>
                                                @endif
                                                @foreach (Auth::user()->company->reportsTo()->companies('1')->sortBy('name') as $company)
                                                    @if (in_array('4', $company->tradesSkilledIn->pluck('id')->toArray()))
                                                        <option value="{{ $company->id }}" {{ ($report->assigned_to && $report->assigned_to == $company->id) ? 'selected' : '' }}>{{ $company->name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        @else
                                            {!! Form::text('assigned_name', ($report->assignedTo) ? $report->assignedTo->name : '', ['class' => 'form-control', 'readonly']) !!}
                                        @endif
                                        {!! fieldErrorMessage('assigned_to', $errors) !!}
                                    </div>
                                </div>
                                {{-- Inspection Date/Time --}}
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('inspected_at', $errors) !!}" style="{{ (!$report->assigned_to) ? 'display:none' : '' }}" id="inspected_at-div">
                                        {!! Form::label('inspected_at', 'Date / Time of Inspection', ['class' => 'control-label']) !!}
                                        <div class="input-group date form_datetime form_datetime bs-datetime" data-date-end-date="0d"> <!-- bs-datetime -->
                                            {!! Form::text('inspected_at', ($report->inspected_at) ? $report->inspected_at->format('d F Y - H:i') : '', ['class' => 'form-control', 'readonly', 'style' => 'background:#FFF']) !!}
                                            <span class="input-group-addon">
                                                <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                            </span>
                                        </div>
                                        {!! fieldErrorMessage('inspected_at', $errors) !!}
                                    </div>
                                </div>

                                {{-- Client contacted --}}
                                <div class="col-md-2" style="{{ (!$report->assigned_to) ? 'display:none' : '' }}">
                                    {!! Form::label('client_contacted', 'Client contacted', ['class' => 'control-label']) !!}
                                    <div class="input-group" style="width=80%">
                                        <datepicker :value.sync="xx.client_contacted" format="dd/MM/yyyy" :placeholder="choose date"></datepicker>
                                    </div>
                                    <input v-model="xx.client_contacted" type="hidden" name="client_contacted" id="client_contacted" value="{{  ($report->client_contacted) ? $report->client_contacted->format('d/m/Y') : ''}}">
                                    {!! fieldErrorMessage('client_contacted', $errors) !!}
                                </div>

                                {{-- Status --}}
                                <div class="col-md-2 pull-right">
                                    <div class="form-group">
                                        {!! Form::label('status', 'Status', ['class' => 'control-label']) !!}
                                        <?php $complated_status = ($report->status == 3) ? 3 : 0 ?>
                                        @if ($report->status && Auth::user()->allowed2('edit.site.inspection', $report) || ($report->status == 0 && Auth::user()->allowed2('sig.site.inspection', $report)))
                                            @if (Auth::user()->allowed2('sig.site.inspection', $report))
                                                {!! Form::select('status', ['1' => 'Active', $complated_status => 'Completed', '4' => 'On Hold'], $report->status, ['class' => 'form-control bs-select', 'id' => 'status']) !!}
                                            @else
                                                {!! Form::select('status', ['1' => 'Active', $complated_status => 'Completed'], $report->status, ['class' => 'form-control bs-select', 'id' => 'status']) !!}
                                            @endif
                                        @else
                                            {!! Form::text('status_text', ($report->status == 0) ? 'Completed' : 'Active', ['class' => 'form-control', 'readonly']) !!}
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Inspectors Name + Lic--}}
                            <div class="row note note-warning" id="inspector-div" style="{{ (fieldHasError('inspected_name', $errors) || fieldHasError('inspected_lic', $errors)) ? 'display:show' : 'display:none' }}">
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('inspected_name', $errors) !!}">
                                        {!! Form::label('inspected_name', 'Inspection carried out by', ['class' => 'control-label']) !!}
                                        {!! Form::text('inspected_name', Auth::user()->name, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('inspected_name', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group {!! fieldHasError('inspected_lic', $errors) !!}">
                                        {!! Form::label('inspected_lic', 'Licence No.', ['class' => 'control-label']) !!}
                                        {!! Form::text('inspected_lic', Auth::user()->company->contractorLicence(), ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('inspected_lic', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <div id="report-div" style="{{ (!$report->assigned_to) ? 'display:none' : '' }}">
                                {{-- Existing --}}
                                <h4 class="font-green-haze">Condition of existing wiring</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('existing', $errors) !!}">
                                            {!! Form::label('existing', 'The existing wiring was found to be', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('existing', null, ['rows' => '5', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('existing', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Required --}}
                                <h4 class="font-green-haze">Required work to meet compliance</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('required', $errors) !!}">
                                            {!! Form::label('required', 'The following work is required so that Existing Electrical Wiring will comply to the requirements of S.A.A Codes and the local Council', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('required', null, ['rows' => '5', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('required', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('required_cost', $errors) !!}">
                                            {!! Form::label('required_cost', 'Cost of required work (incl GST)', ['class' => 'control-label']) !!}
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                                                {!! Form::text('required_cost', null, ['class' => 'form-control']) !!}
                                            </div>
                                            {!! fieldErrorMessage('required_cost', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Recommended --}}
                                <h4 class="font-green-haze">Recommended works</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('recommend', $errors) !!}">
                                            {!! Form::label('recommend', 'Work not esstial but strongly recommended to be carried out to prevent the necessity of costly maintenance in the future when access to same', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('recommend', null, ['rows' => '5', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('recommend', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('recommend_cost', $errors) !!}">
                                            {!! Form::label('recommend_cost', 'Cost of recommended work (incl GST)', ['class' => 'control-label']) !!}
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                                                {!! Form::text('recommend_cost', null, ['class' => 'form-control']) !!}
                                            </div>
                                            {!! fieldErrorMessage('recommend_cost', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional -->
                                <h4 class="font-green-haze">Additional Notes for Client</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('notes', $errors) !!}">
                                            {!! Form::label('notes', 'Client Notes', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('notes', null, ['rows' => '10', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('notes', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Notes --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <app-actions :table_id="{{ $report->id }}"></app-actions>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions right">
                                <a href="/site/inspection/electrical" class="btn default"> Back</a>
                                <button type="submit" class="btn green" id="submit"> Save</button>
                            </div>
                        </div>
                        {!! Form::close() !!} <!-- END FORM-->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <template id="actions-template">
        <action-modal></action-modal>
        <input v-model="xx.table_id" type="hidden" id="table_id" value="{{ $report->id }}">
        <input v-model="xx.created_by" type="hidden" id="created_by" value="{{ Auth::user()->id }}">
        <input v-model="xx.created_by_fullname" type="hidden" id="fullname" value="{{ Auth::user()->fullname }}">

        <div class="page-content-inner">
            <div class="row">
                <div class="col-md-12">
                    <h4 class="font-green-haze">Additional Notes for {{ ($report->ownedBy->nickname) ? $report->ownedBy->nickname :  $report->ownedBy->name }}
                        <button v-on:click.stop.prevent="$root.$broadcast('add-action-modal')" class="btn btn-circle green btn-outline btn-sm pull-right" data-original-title="Add">Add</button>
                    </h4>
                    <hr>
                    <table v-show="actionList.length" class="table table-striped table-bordered table-nohover order-column">
                        <thead>
                        <tr class="mytable-header">
                            <th width="10%">Date</th>
                            <th> Details</th>
                            <th width="20%"> Name</th>
                        </tr>
                        </thead>
                        <tbody>
                        <template v-for="action in actionList">
                            <tr>
                                <td>@{{ action.niceDate }}</td>
                                <td>@{{ action.action }}</td>
                                <td>@{{ action.fullname }}</td>
                            </tr>
                        </template>
                        </tbody>
                    </table>

                    <!--<pre v-if="xx.dev">@{{ $data | json }}</pre>
                    -->

                </div>
            </div>
        </div>
    </template>

    @include('misc/actions-modal')
@stop


@section('page-level-plugins-head')
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css"/>   {{-- Filepond --}}
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript">var html5lightbox_options = {watermark: "", watermarklink: ""};</script>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/js/moment.min.js" type="text/javascript"></script>
    <script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-date-time-pickers.js" type="text/javascript"></script>
    <script src="/js/libs/moment.min.js" type="text/javascript"></script>
    <script src="/js/filepond-basic.js" type="text/javascript"></script>
    <script src="/js/libs/vue.1.0.24.js " type="text/javascript"></script>
    <script src="/js/libs/vue-strap.min.js"></script>
    <script src="/js/libs/vue-resource.0.7.0.js " type="text/javascript"></script>
    <script src="/js/vue-modal-component.js"></script>
    <script src="/js/vue-app-basic-functions.js"></script>


    <script type="text/javascript">
        $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

        $(document).ready(function () {
            /* Select2 */
            $("#assigned_to").select2({placeholder: "Select Company"});

            if ($("#status").val() == '3') {
                $('#inspector-div').show();
            }

            $("#status").change(function () {
                $('#inspector-div').hide();

                if ($("#status").val() == '0') {
                    $('#inspector-div').show();
                }
            });


            $('#photos-edit').hide();
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

            $('.deleteFile').on('click', function (e) {
                e.preventDefault();
                var id = $(this).data('did');
                var name = $(this).data('name');
                swal({
                    title: "Are you sure?",
                    text: "You will not be able to restore this file!<br><b>" + name + "</b>",
                    showCancelButton: true,
                    cancelButtonColor: "#555555",
                    confirmButtonColor: "#E7505A",
                    confirmButtonText: "Yes, delete it!",
                    allowOutsideClick: true,
                    html: true,
                }, function () {
                    window.location = '/site/inspection/electrical/' + {{$report->id}} + '/delfile/' + id;
                });
            });

            /* Bootstrap Fileinput */
            /*
            $("#multifile").fileinput({
                uploadUrl: "/site/inspection/electrical/upload/", // server upload action
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
                    "report_id": report_id,
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
                data.form.append("report_id", $("#report_id").val());
            }); */
        });
    </script>
    <script>
        Vue.http.headers.common['X-CSRF-TOKEN'] = document.querySelector('#token').getAttribute('value');

        var host = window.location.hostname;
        var dev = true;
        if (host == 'safeworksite.com.au')
            dev = false;

        var xx = {
            dev: dev,
            action: '', loaded: false,
            table_name: 'site_inspection_electrical', table_id: '', record_status: '', stage: '', next_review_date: '', client_contacted: '',
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
                'add-action-modal': function (e) {
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
                    //alert('add action');

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


