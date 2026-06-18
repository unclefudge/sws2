@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/asbestos/register">Asbestos Register</a><i class="fa fa-circle"></i></li>
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
                            <span class="caption-subject font-green-haze bold uppercase">Create Asbestos Register</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteAsbestosRegisterController::class, 'store']) }}" class="horizontal-form" enctype="multipart/form-data">
                            @csrf
                            @include('form-error')
                            <x-form.hidden name="no-asbestos" value="0" id="no-asbestos" class="form-control" :use-old="false"/>

                            <div class="form-body">
                                {{-- Site --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-form.select name="site_id" label="Site" :options="$sitelist" plugin="select2" placeholder="Select Site"/>
                                    </div>

                                    {{-- No Asbestos --}}
                                    <div class="col-md-3 pull-right">
                                        <button class="btn red" style="margin-top: 20px" id="btn-no-asbestos" name="btn-no-asbestos"> Mark No Asbestos Found</button>
                                    </div>
                                </div>
                                <div id="asbestos-div">

                                    {{-- Asbestos Details --}}
                                    <h4>Asbestos Details</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">

                                    {{-- Amount --}}
                                    <div class="row">
                                        {{--  Date --}}
                                        <div class="col-md-3">
                                            <x-form.datepicker name="date" label="Date Identified"/>
                                        </div>
                                        <div class="col-md-2">
                                            <x-form.input name="amount" label="Quantity (m2)"/>
                                        </div>
                                        <div class="col-md-3">
                                            <x-form.select name="friable" label="Asbestos Class" :options="['' => 'Select class', '1' => 'Class A (Friable)', '0' => 'Class B (Non-Friable)']"/>
                                        </div>
                                    </div>

                                    {{-- Type --}}
                                    <div class="row">
                                        <div class="col-md-5">
                                            <x-form.select name="type" label="Type" :options="['' => 'Select type', 'Asbestos Cement Sheets/Products' => 'Asbestos Cement Sheets/Products', 'Vinyl floor covering' => 'Vinyl floor covering', 'other' => 'Other']"/>
                                        </div>
                                        <div class="col-md-7" style="display: none" id="type_other_div">
                                            <x-form.input name="type_other" label="Other type" placeholder="Please specify other"/>
                                        </div>
                                    </div>

                                    {{-- Location --}}
                                    <div class="row">
                                        <div class="col-md-5">
                                            <x-form.input name="location" label="Location of Asbestos" placeholder="Location of asbestos"/>
                                        </div>
                                    </div>

                                    {{-- Condition --}}
                                    <div class="row">
                                        <div class="col-md-12">
                                            <x-form.textarea name="condition" label="Condition" placeholder="Condition of asbestos"/>
                                        </div>
                                    </div>

                                    {{-- Assessment --}}
                                    <div class="row">
                                        <div class="col-md-12">
                                            <x-form.textarea name="assessment" label="Assessment" placeholder="Assessment of asbestos"/>
                                        </div>
                                    </div>
                                    <br><br>
                                </div>
                                <div class="form-actions right">
                                    <a href="/site/asbestos/register" class="btn default"> Back</a>
                                    <button type="submit" class="btn green"> Save</button>
                                </div>

                            </div> <!-- /Form body -->
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/js/libs/fileinput.min.js"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script>
        $(document).ready(function () {
            /* Select2 */
            $("#site_id").select2({placeholder: "Select Site",});

            // On Change Type
            $("#type").change(function () {
                $("#type").val() == 'other' ? $("#type_other_div").show() : $("#type_other_div").hide(); // Type
            });

            // On Click No Asbestos
            $("#btn-no-asbestos").click(function (e) {
                e.preventDefault(e);
                $("#no-asbestos").val('1');
                $("#asbestos-div").hide();
            });
        });

        function isNumber(evt) {
            evt = (evt) ? evt : window.event;
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if ((charCode > 31 && charCode < 48) || charCode > 57) {
                return false;
            }
            return true;
        }

        $('.date-picker').datepicker({
            autoclose: true,
            clearBtn: true,
            format: 'dd/mm/yyyy',
        });

    </script>
@stop
