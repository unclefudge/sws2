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
                        <form method="POST" action="{{ action([\App\Http\Controllers\Site\SiteInspectionElectricalController::class, 'update'], $report->id) }}" class="horizontal-form" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
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
                                            <x-form.input name="site_name" label="Site" :value="$report->site->name" readonly/>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <x-form.input name="site_code" label="Job #" :value="$report->site->code" readonly/>
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
                                        @if(Auth::user()->allowed2('add.site.inspection'))
                                            <x-form.input name="client_name" label="Name" :value="$report->client_name"/>
                                        @else
                                            <x-form.input name="client_name" label="Name" :value="$report->client_name" readonly/>
                                        @endif
                                    </div>
                                    <div class="col-md-7">
                                        @if(Auth::user()->allowed2('add.site.inspection'))
                                            <x-form.input name="client_address" label="Address" :value="$report->client_address"/>
                                        @else
                                            <x-form.input name="client_address" label="Address" :value="$report->client_address" readonly/>
                                        @endif
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
                                        @if(Auth::user()->allowed2('add.site.inspection'))
                                            <x-form.textarea name="info" rows="5" placeholder="Details" :value="$report->info"/>
                                        @else
                                            <x-form.textarea name="info" rows="5" placeholder="Details" :value="$report->info" readonly/>
                                        @endif
                                    </div>
                                </div>

                                {{-- attachments --}}
                                <br>
                                <livewire:misc.attachments context="site-inspection-electrical" :context-id="$report->id"/>

                                <h4 class="font-green-haze">Inspection details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    {{-- Assigned To Company --}}
                                    <div class="col-md-4">
                                        @if(Auth::user()->allowed2('sig.site.inspection'))
                                            <x-form.select name="assigned_to" label="Assigned to company" plugin="">
                                                @if (!$report->assigned_to)
                                                    <option value="">Select company</option>
                                                @endif
                                                @foreach (Auth::user()->company->reportsTo()->companies('1')->sortBy('name') as $company)
                                                    @if (in_array('4', $company->tradesSkilledIn->pluck('id')->toArray()))
                                                        <option value="{{ $company->id }}" {{ ($report->assigned_to && $report->assigned_to == $company->id) ? 'selected' : '' }}>{{ $company->name }}</option>
                                                    @endif
                                                @endforeach
                                            </x-form.select>
                                        @else
                                            <x-form.input name="assigned_name" label="Assigned to company" :value="$report->assignedTo ? $report->assignedTo->name : ''" readonly/>
                                        @endif
                                    </div>
                                    {{-- Inspection Date/Time --}}
                                    <div class="col-md-4">
                                        <div class="form-group" style="{{ (!$report->assigned_to) ? 'display:none' : '' }}" id="inspected_at-div">
                                            <label for="inspected_at" class="control-label">Date / Time of Inspection</label>
                                            <div class="input-group date form_datetime form_datetime bs-datetime" data-date-end-date="0d">
                                                <input type="text" name="inspected_at" id="inspected_at" class="form-control" value="{{ old('inspected_at', $report->inspected_at ? $report->inspected_at->format('d F Y - H:i') : '') }}" readonly style="background:#FFF">
                                                <span class="input-group-addon">
                                                <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                            </span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Client contacted --}}
                                    <div class="col-md-2" style="{{ (!$report->assigned_to) ? 'display:none' : '' }}">
                                        <x-form.datepicker name="client_contacted" label="Client contacted" :value="$report->client_contacted?->format('d/m/Y')" format="dd/mm/yyyy" readonly/>
                                    </div>

                                    {{-- Status --}}
                                    <div class="col-md-2 pull-right">
                                        <div class="form-group">

                                            <?php $complated_status = ($report->status == 3) ? 3 : 0 ?>
                                            @if ($report->status && Auth::user()->allowed2('edit.site.inspection', $report) || ($report->status == 0 && Auth::user()->allowed2('sig.site.inspection', $report)))
                                                @if (Auth::user()->allowed2('sig.site.inspection', $report))
                                                    <x-form.select name="status" label="Status" :options="['1' => 'Active', $complated_status => 'Completed', '4' => 'On Hold']" :value="$report->status"/>
                                                @else
                                                    <x-form.select name="status" label="Status" :options="['1' => 'Active', $complated_status => 'Completed']" :value="$report->status"/>
                                                @endif
                                            @else
                                                <x-form.input name="status_text" label="Status" :value="$report->status == 0 ? 'Completed' : 'Active'" readonly/>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                {{-- Inspectors Name + Lic--}}
                                <div class="row note note-warning" id="inspector-div" style="{{ ($errors->has('inspected_name') || $errors->has('inspected_lic')) ? 'display:block' : 'display:none' }}">
                                    <div class="col-md-4">
                                        <x-form.input name="inspected_name" label="Inspection carried out by" :value="Auth::user()->name"/>
                                    </div>
                                    <div class="col-md-2">
                                        <x-form.input name="inspected_lic" label="Licence No." :value="Auth::user()->company->contractorLicence()"/>
                                    </div>
                                </div>

                                <div class="row">
                                    {{-- Ausgrid --}}
                                    <div class="col-md-6">
                                        <x-form.select name="ausgrid" label="Will Ausgrid need to be engaged for Pre Construction works" :options="['' => 'Select option', 'Yes' => 'Yes', 'No' => 'No']" :value="$report->ausgrid"/>
                                    </div>
                                    {{-- Client Bill --}}
                                    <div class="col-md-6">
                                        <x-form.select name="clientbill" label="Do you require a copy of the Client's Electricity Bill" :options="['' => 'Select option', 'Yes' => 'Yes', 'No' => 'No']" :value="$report->clientbill"/>
                                    </div>
                                </div>
                                <div class="row">
                                    {{-- Non-Ausgrid --}}
                                    <div class="col-md-6">
                                        <x-form.select name="nonausgrid" label="Is there any Pre construction works that doesn’t require Ausgrid?" :options="['' => 'Select option', 'Yes' => 'Yes', 'No' => 'No']" :value="$report->nonausgrid"/>
                                    </div>
                                    {{-- Non-Ausgrid Weeks--}}
                                    <div class="col-md-6" id="nonausgrid_weeks-div" style="display: none">
                                        <x-form.input name="nonausgrid_weeks" label="How many weeks in advance does this work need to be done?" :value="$report->nonausgrid_weeks"/>
                                    </div>
                                </div>

                                <div id="report-div" style="{{ (!$report->assigned_to) ? 'display:none' : '' }}">
                                    {{-- Existing --}}
                                    <h4 class="font-green-haze">Condition of existing wiring</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <x-form.textarea name="existing" label="The existing wiring was found to be" rows="5" :value="$report->existing"/>
                                        </div>
                                    </div>

                                    {{-- Required --}}
                                    <h4 class="font-green-haze">Required work to meet compliance</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <x-form.textarea name="required" label="The following work is required so that Existing Electrical Wiring will comply to the requirements of S.A.A Codes and the local Council" rows="5" :value="$report->required"/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="required_cost" class="control-label">Cost of required work (incl GST)</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                                                    <input type="text" name="required_cost" id="required_cost" class="form-control" value="{{ old('required_cost', $report->required_cost) }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Recommended --}}
                                    <h4 class="font-green-haze">Recommended works</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <x-form.textarea name="recommend" label="Work not esstial but strongly recommended to be carried out to prevent the necessity of costly maintenance in the future when access to same" rows="5" :value="$report->recommend"/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="recommend_cost" class="control-label">Cost of recommended work (incl GST)</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                                                    <input type="text" name="recommend_cost" id="recommend_cost" class="form-control" value="{{ old('recommend_cost', $report->recommend_cost) }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Additional -->
                                    <h4 class="font-green-haze">Additional Notes for Client</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <x-form.textarea name="notes" label="Client Notes" rows="10" :value="$report->notes"/>
                                        </div>
                                    </div>

                                    {{-- Notes --}}
                                    <div class="row">
                                        <div class="col-md-12">
                                            <livewire:misc.actions table="site_inspection_electrical" :table-id="$report->id"/>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions right">
                                    <a href="/site/inspection/electrical" class="btn default"> Back</a>
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
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
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

            $("#nonausgrid").change(function () {
                $('#nonausgrid_weeks-div').hide();
                update_fields();

            });

            function update_fields() {
                if ($("#nonausgrid").val() == 'Yes') {
                    $('#nonausgrid_weeks-div').show();
                }
            }

            update_fields();

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
@stop


