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
                            <span class="caption-subject font-green-haze bold uppercase">Maintenance Request</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteMaintenanceController::class, 'store']) }}" class="horizontal-form" enctype="multipart/form-data">
                            @csrf
                            @include('form-error')

                            {{-- Progress Steps --}}
                            <div class="mt-element-step hidden-sm hidden-xs">
                                <div class="row step-thin" id="steps">
                                    <div class="col-md-6 mt-step-col first active">
                                        <div class="mt-step-number bg-white font-grey">1</div>
                                        <div class="mt-step-title uppercase font-grey-cascade">Create</div>
                                        <div class="mt-step-content font-grey-cascade">Create request</div>
                                    </div>
                                    <div class="col-md-6 mt-step-col last">
                                        <div class="mt-step-number bg-white font-grey">2</div>
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
                                        <x-form.select name="site_id" id="site_id" label="Completed/Maintenance Sites" plugin="select2" style="width:100%">
                                            <optgroup label="Completed Sites"></optgroup>
                                            {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id'), 0) !!}
                                            <optgroup label="Maintenance Sites"></optgroup>
                                            {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id'), 2) !!}
                                        </x-form.select>
                                    </div>
                                    <div class="col-md-4">
                                        <x-form.input name="site_suburb" label="Suburb" readonly/>
                                    </div>
                                    <div class="col-md-2">
                                        <x-form.input name="site_code" label="Job #" readonly/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-form.input name="supervisor" label="Supervisor"/>
                                    </div>
                                    <div class="col-md-3">
                                        <x-form.datepicker name="completed" label="Prac Completed" placeholder="dd/mm/yyyy"/>
                                    </div>
                                    <div class="col-md-3">
                                        <x-form.datepicker name="reported" label="Reported" :value="\Carbon\Carbon::now()->format('d/m/Y')" placeholder="dd/mm/yyyy"/>
                                    </div>
                                </div>


                                <h4>Client Contact Details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-4">
                                        <x-form.input name="contact_name" label="Name"/>
                                    </div>
                                    <div class="col-md-3">
                                        <x-form.input name="contact_phone" label="Phone"/>
                                    </div>
                                    <div class="col-md-5">
                                        <x-form.input name="contact_email" label="Email"/>
                                    </div>
                                </div>


                                <h4>Request Details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    {{-- Category --}}
                                    <div class="col-md-3 ">
                                        <x-form.select name="category_id" id="category_id" label="Category" :options="['' => 'Select category'] + \App\Models\Site\SiteMaintenanceCategory::all()->sortBy('name')->pluck('name', 'id')->toArray()" plugin="select2" title="Select category"/>
                                    </div>

                                    {{-- Warranty --}}
                                    <div class="col-md-2 ">
                                        <x-form.select name="warranty" id="warranty" label="Warranty" :options="$maintenanceWarranty::all()"/>
                                    </div>
                                </div>
                                {{-- Photo/Docs --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Upload Photos/Documents</h5>
                                        <x-form.filepond/>
                                        <br><br>
                                    </div>
                                </div>

                                {{-- Items --}}
                                <div id="items-div">
                                    <h4>Maintenance Item(s)</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <div class="row">
                                            <div class="col-xs-1 ">Item {{$i}}</div>
                                            <div class="col-xs-11 ">
                                                <x-form.textarea :name="'item'.$i" rows="3" :placeholder="'Specific details of maintenance request item '.$i.'.'"/>
                                            </div>
                                        </div>
                                    @endfor

                                    {{-- Extra Items --}}
                                    <button class="btn blue" id="more">More Items</button>
                                    <div id="more_items" style="display: none">
                                        @for ($i = 6; $i <= 20; $i++)
                                            <div class="row">
                                                <div class="col-xs-1 ">Item {{$i}}</div>
                                                <div class="col-xs-11 ">
                                                    <x-form.textarea :name="'item'.$i" rows="3" :placeholder="'Specific details of maintenance request item '.$i.'.'"/>
                                                </div>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions right">
                                <a href="/site/maintenance" class="btn default"> Back</a>
                                <button type="submit" class="btn green" id="submit"> Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css"/>   {{-- Filepond --}}
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script src="/js/filepond-basic.js" type="text/javascript"></script>
    <script>
        $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

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

            $("#more").click(function (e) {
                e.preventDefault();
                $('#more').hide();
                $('#more_items').show();
            });


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
                }
            }
        });

        $('.date-picker').datepicker({
            autoclose: true,
            format: 'dd/mm/yyyy',
        });
    </script>
@stop

