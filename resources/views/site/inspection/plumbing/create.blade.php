@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->company->subscription)
            <li><a href="/site/inspection/plumbing">Plumbing Inspection Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Create Report</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Create Plumbing Inspection Report</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([\App\Http\Controllers\Site\SiteInspectionPlumbingController::class, 'store']) }}" class="horizontal-form">
                            @csrf

                            @include('form-error')

                            {{-- Progress Steps --}}
                            <div class="mt-element-step hidden-sm hidden-xs">
                                <div class="row step-thin" id="steps">
                                    <div class="col-md-6 mt-step-col first active">
                                        <div class="mt-step-number bg-white font-grey">1</div>
                                        <div class="mt-step-title uppercase font-grey-cascade">Create</div>
                                        <div class="mt-step-content font-grey-cascade">Create report</div>
                                    </div>
                                    <div class="col-md-6 mt-step-col last">
                                        <div class="mt-step-number bg-white font-grey">2</div>
                                        <div class="mt-step-title uppercase font-grey-cascade">Assign</div>
                                        <div class="mt-step-content font-grey-cascade">Assign company</div>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="form-body">
                                <h4 class="font-green-haze">Site details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    {{-- Site --}}
                                    <div class="col-md-6">
                                        <x-form.select name="site_id" label="Site (Upcoming)" plugin="select2" style="width:100%">
                                            {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id'), -1) !!}
                                        </x-form.select>
                                    </div>
                                </div>

                                <h4 class="font-green-haze">Client details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-3">
                                        <x-form.input name="client_name" label="Name"/>
                                    </div>
                                    <div class="col-md-7">
                                        <x-form.input name="client_address" label="Address"/>
                                    </div>
                                </div>

                                {{-- Photo/Docs --}}
                                <h4 class="font-green-haze">Photos/Documents</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-form.filepond/>
                                        <br><br>
                                    </div>
                                </div>

                                <h4 class="font-green-haze">Admin Notes</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-12 ">
                                        <x-form.textarea name="info" rows="5" placeholder="Details"/>
                                    </div>
                                </div>

                                <div class="form-actions right">
                                    <a href="/site/inspection/plumbing" class="btn default"> Back</a>
                                    <button type="submit" class="btn green" id="submit"> Save</button>
                                </div>
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
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script src="/js/filepond-basic.js" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            /* Select2 */
            $("#site_id").select2({placeholder: "Select Site"});
            $("#assigned_to").select2({placeholder: "Select Company"});

            updateFields();

            // On Change Site ID
            $("#site_id").change(function () {
                updateFields();
            });

            function updateFields() {
                var site_id = $("#site_id").select2("val");

                if (site_id != '') {
                    $.ajax({
                        url: '/site/data/details/' + site_id,
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            var address = '';
                            address = data.address;
                            if (data.address != '') address = address + ', ';
                            if (data.suburb != '') address = address + data.suburb + ', ';
                            if (data.state != '') address = address + data.state + ' ';
                            if (data.postcode != '') address = address + data.postcode + ' ';

                            $("#client_address").val(address);
                            $("#client_name").val(data.name);
                            //console.log(address);
                        },
                    })
                }
            }

        });

        // Force datepicker to not be able to select dates after today
        //$('.bs-datetime').datetimepicker({
        //    endDate: new Date()
        //});
    </script>
@stop


