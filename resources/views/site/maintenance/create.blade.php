@inject('maintenanceWarranty', 'App\Http\Utilities\MaintenanceWarranty')
@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/maintenance">Maintenance Register</a><i class="fa fa-circle"></i></li>
        <li><span>Create</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-pencil "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Maintenance Request</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('SiteQa', ['action' => 'Site\SiteMaintenanceController@store', 'class' => 'horizontal-form', 'files' => true]) !!}
                        @include('form-error')

                        {{-- Progress Steps --}}
                        <div class="mt-element-step hidden-sm hidden-xs">
                            <div class="row step-thin" id="steps">
                                <div class="col-md-4 mt-step-col first active">
                                    <div class="mt-step-number bg-white font-grey">1</div>
                                    <div class="mt-step-title uppercase font-grey-cascade">Create</div>
                                    <div class="mt-step-content font-grey-cascade">Create request</div>
                                </div>
                                <div class="col-md-4 mt-step-col">
                                    <div class="mt-step-number bg-white font-grey">2</div>
                                    <div class="mt-step-title uppercase font-grey-cascade">Documents</div>
                                    <div class="mt-step-content font-grey-cascade">Add Photos/Documents</div>
                                </div>
                                <div class="col-md-4 mt-step-col last">
                                    <div class="mt-step-number bg-white font-grey">3</div>
                                    <div class="mt-step-title uppercase font-grey-cascade">Assign</div>
                                    <div class="mt-step-content font-grey-cascade">Assign supervisor</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-body">

                            <h4>Site Details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('site_id', $errors) !!}">
                                        {!! Form::label('site_id', 'Completed/Maintenance Sites', ['class' => 'control-label']) !!}
                                        <select id="site_id" name="site_id" class="form-control select2" style="width:100%">
                                            <optgroup label="Completed Sites"></optgroup>
                                            {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id'), 0) !!}
                                            <optgroup label="Maintenance Sites"></optgroup>
                                            {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id'), 2) !!}
                                        </select>
                                        {!! fieldErrorMessage('site_id', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('site_suburb', 'Suburb', ['class' => 'control-label']) !!}
                                        {!! Form::text('site_suburb', null, ['class' => 'form-control', 'readonly']) !!}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        {!! Form::label('site_code', 'Site No.', ['class' => 'control-label']) !!}
                                        {!! Form::text('site_code', null, ['class' => 'form-control', 'readonly']) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('supervisor', $errors) !!}">
                                        {!! Form::label('supervisor', 'Supervisor', ['class' => 'control-label']) !!}
                                        {!! Form::text('supervisor', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('supervisor', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group {!! fieldHasError('completed', $errors) !!}">
                                        {!! Form::label('completed', 'Prac Completed', ['class' => 'control-label']) !!}
                                        <div class="input-group date date-picker">
                                            {!! Form::text('completed', '', ['class' => 'form-control form-control-inline', 'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy", 'placeholder' => 'dd/mm/yyyy']) !!}
                                            <span class="input-group-btn"><button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button></span>
                                        </div>
                                        {!! fieldErrorMessage('completed', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group {!! fieldHasError('reported', $errors) !!}">
                                        {!! Form::label('reported', 'Reported', ['class' => 'control-label']) !!}
                                        <div class="input-group date date-picker">
                                            {!! Form::text('reported', \Carbon\Carbon::now()->format('d/m/Y'), ['class' => 'form-control form-control-inline', 'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy", 'placeholder' => 'dd/mm/yyyy']) !!}
                                            <span class="input-group-btn"><button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button></span>
                                        </div>
                                        {!! fieldErrorMessage('reported', $errors) !!}
                                    </div>
                                </div>
                            </div>


                            <h4>Client Contact Details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('contact_name', $errors) !!}">
                                        {!! Form::label('contact_name', 'Name', ['class' => 'control-label']) !!}
                                        {!! Form::text('contact_name', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('contact_name', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group {!! fieldHasError('contact_phone', $errors) !!}">
                                        {!! Form::label('contact_phone', 'Phone', ['class' => 'control-label']) !!}
                                        {!! Form::text('contact_phone', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('contact_phone', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group {!! fieldHasError('contact_email', $errors) !!}">
                                        {!! Form::label('contact_email', 'Email', ['class' => 'control-label']) !!}
                                        {!! Form::text('contact_email', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('contact_email', $errors) !!}
                                    </div>
                                </div>
                            </div>


                            <h4>Request Details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                {{-- Category --}}
                                <div class="col-md-3 ">
                                    <div class="form-group">
                                        {!! Form::label('category_id', 'Category', ['class' => 'control-label']) !!}
                                        {!! Form::select('category_id', (['' => 'Select category'] + \App\Models\Site\SiteMaintenanceCategory::all()->sortBy('name')->pluck('name' ,'id')->toArray()), null, ['class' => 'form-control select2', 'title' => 'Select category', 'id' => 'category_id']) !!}
                                    </div>
                                </div>

                                {{-- Warranty --}}
                                <div class="col-md-2 ">
                                    <div class="form-group">
                                        {!! Form::label('warranty', 'Warranty', ['class' => 'control-label']) !!}
                                        {!! Form::select('warranty', $maintenanceWarranty::all(), null, ['class' => 'form-control bs-select', 'id' => 'warranty']) !!}
                                    </div>
                                </div>
                            </div>

                            <!-- Multi File upload -->
                            {{--}}
                            <div id="multifile-div">
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
                            </div>--}}


                                    <!-- Items -->
                            <div id="items-div">

                                <h4>Maintenance Item</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    {{-- Item Details  --}}
                                    <div class="col-md-12 ">
                                        <div class="form-group {!! fieldHasError('item1', $errors) !!}">
                                            {!! Form::label('item1', 'Item details', ['class' => 'control-label']) !!}
                                            {!! Form::textarea("item1", null, ['rows' => '5', 'class' => 'form-control', 'placeholder' => "Specific details of maintenance request."]) !!}
                                            {!! fieldErrorMessage('item1', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{--
                                <br>
                                <div class="row" style="border: 1px solid #e7ecf1; padding: 10px 0px; margin: 0px; background: #f0f6fa; font-weight: bold">
                                    <div class="col-md-12">MAINTENANCE ITEMS</div>
                                </div>
                                <br>
                                @for ($i = 1; $i <= 10; $i++)
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <div class="form-group">{!! Form::textarea("item$i", '', ['rows' => '2', 'class' => 'form-control', 'placeholder' => "Item $i."]) !!}</div>
                                        </div>
                                    </div>
                                @endfor


                                <button class="btn blue" id="more">More Items</button>
                                <div class="row" id="more_items" style="display: none">
                                    @for ($i = 10 + 1; $i <= 25; $i++)
                                        <div class="col-md-12">
                                            <div class="form-group">{!! Form::textarea("item$i", null, ['rows' => '2', 'class' => 'form-control', 'placeholder' => "Item $i."]) !!}</div>
                                        </div>
                                    @endfor
                                </div>
                                --}}
                            </div>
                        </div>

                        <h4>Additional Info</h4>
                        <div class="row">
                            {{-- Item Details  --}}
                            <div class="col-md-12 ">
                                <div class="form-group {!! fieldHasError('notes', $errors) !!}">
                                    {!! Form::textarea("notes", null, ['rows' => '5', 'class' => 'form-control', 'placeholder' => "Details"]) !!}
                                    {!! fieldErrorMessage('notes', $errors) !!}
                                </div>
                            </div>
                        </div>
                        <div class="form-actions right">
                            <a href="/site/maintenance" class="btn default"> Back</a>
                            <button type="submit" class="btn green"> Save</button>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
    @stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/js/libs/fileinput.min.js"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script>
    $.ajaxSetup({
        headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
    });

    $(document).ready(function () {
        /* Select2 */
        $("#site_id").select2({placeholder: "Select Site", width: "100%"});
        $("#category_id").select2({placeholder: "Select category", width: "100%"});
        //$("#super_id").select2({placeholder: "Select Supervisor", width: "100%"});

        updateFields();

        // On Change Site ID
        $("#site_id").change(function () {
            updateFields();
        });

        /*
        $("#more").click(function (e) {
            e.preventDefault();
            $('#more').hide();
            $('#more_items').show();
        });*/


        function updateFields() {
            var site_id = $("#site_id").select2("val");
            $("#completed").val('');
            //$('#multifile-div').hide();
            //$('#items-div').hide();

            if (site_id != '') {
                //$('#multifile-div').show();
                //$('#items-div').show();
                $.ajax({
                    url: '/site/data/details/' + site_id,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        $("#site_suburb").val(data.suburb);
                        $("#site_code").val(data.code);
                        console.log(data.suburb);
                    },
                })

                $.ajax({
                    url: '/site/maintenance/data/prac_completion/' + site_id,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        console.log(data);
                        var year = data.substring(0, 4);
                        var month = data.substring(5, 7);
                        var day = data.substring(8, 10);
                        $("#completed").val(day + '/' + month + '/' + year);
                    },
                })

                $.ajax({
                    url: '/site/maintenance/data/site_super/' + site_id,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        console.log(data);
                        $("#supervisor").val(data);
                        //$('#supervisor').trigger('change.select2');
                    },
                })
                //alert('h');
            }
        }
    });

    $('.date-picker').datepicker({
        autoclose: true,
        format: 'dd/mm/yyyy',
    });
</script>
@stop

