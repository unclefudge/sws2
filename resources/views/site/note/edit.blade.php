@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/{{$note->site_id}}/notes">Site Notes</a><i class="fa fa-circle"></i></li>
        <li><span>Edit</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Edit Site Note</span>
                            <span class="caption-helper">ID: {{ $note->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteNoteController::class, 'update'], $note->id) }}" class="horizontal-form" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')

                        @include('form-error')

                        <div class="form-body">
                            <div class="row">
                                {{-- Site --}}
                                <div class="col-md-6">
                                    <x-form.input name="site_name" label="Site" :value="$note->site->name" id="site_name" readonly/>
                                    <x-form.hidden name="site_id" :value="$note->site_id"/>
                                </div>
                                {{-- Category --}}
                                <div class="col-md-4">
                                    <x-form.input name="category_name" label="Category" :value="($note->category_id) ? $note->category->name : 'none'" id="category_name" readonly/>
                                    <x-form.hidden name="category_id" id="category_id" :value="$note->category_id"/>
                                </div>
                            </div>

                            {{-- Variation Fields --}}
                            <div id="variation_fields" style="display: none">
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-form.input name="variation_name" label="Variation Name" :value="$note->variation_name"/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-form.textarea name="variation_info" label="Variation Description" :value="$note->variation_info" rows="5"/>
                                    </div>
                                </div>
                            </div>
                            <div id="variation_cost_fields" style="display: none">
                                <div class="row">
                                    <div class="col-md-3">
                                        <x-form.input name="variation_net" label="Net Cost <span class='font-grey-silver'>(Admin use only)</span>" :value="$note->variation_net"/>
                                    </div>

                                    <div class="col-md-3">
                                        <x-form.input name="variation_cost" label="Gross Cost (incl GST + 20% margin)" :value="$note->variation_cost"/>
                                    </div>

                                    <div id="extracredit_div">
                                        <div class="col-md-3">
                                            <x-form.select name="variation_extra_credit" label="Credit / Extra" :options="['' => 'Select option', 'Extra' => 'Extra', 'Credit' => 'Credit']" :value="$note->costing_extra_credit" id="variation_extra_credit"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-form.input name="variation_days" label="Total Extension Days (discussed with Client) Description <span class='font-grey-silver'>(Admin use only)</span>" :value="$note->variation_days" onkeydown="return isNumber(event)"/>
                                    </div>
                                </div>
                                {{-- Variation items --}}
                                <div class="row">
                                    <div class="col-md-12">Variation items <span class="font-grey-silver">(Admin use only)</span></div>
                                </div>
                                {{-- Cost centre & Details --}}
                                @foreach ($note->costs as $cost)
                                    <div class="row">
                                        <div class="col-md-3">
                                            <x-form.select name="cc-{{ $cost->id }}" :options="['' => 'Select cost centre'] + $cost_centres" :value="$cost->category->id"/>
                                        </div>
                                        <div class="col-md-9">
                                            <x-form.input name="cinfo-{{ $cost->id }}" :value="$cost->details" placeholder="Details & Cost of item."/>
                                        </div>
                                    </div>
                                @endforeach
                                @php $notes_label = 'Note (Admin use only)'; @endphp
                                <br>
                            </div>

                            {{-- Costing Fields --}}
                            <div id="costing_fields" style="display: none">
                                <div class="row">
                                    <div class="col-md-3">
                                        <x-form.select name="costing_extra_credit" label="Credit / Extra" :options="['' => 'Select option', 'Extra' => 'Extra', 'Credit' => 'Credit']" :value="$note->costing_extra_credit" id="costing_extra_credit"/>
                                    </div>
                                    <div class="col-md-3">
                                        <x-form.select name="costing_item" label="New item / In Lieu of" :options="['' => 'Select option', 'New item' => 'New item', 'In Lieu of' => 'In Lieu of']" :value="$note->costing_item" id="costing_item"/>
                                    </div>
                                    <div class="col-md-3">
                                        <x-form.select name="costing_priority" label="Priority" :options="['' => 'Select option', '1-2 days' => '1-2 days', '3-5 days' => '3-5 days', '5+ days' => '5+ days']" :value="$note->costing_priority" id="costing_priority"/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <x-form.input name="costing_room" label="Room" :value="$note->costing_room"/>
                                    </div>
                                    <div class="col-md-7">
                                        <x-form.input name="costing_location" label="Location" :value="$note->costing_location"/>
                                    </div>
                                </div>
                            </div>

                            {{-- Prac Completion Fields --}}
                            <div id="prac_completion_fields" style="display: none">
                                <div class="row">
                                    <div class="col-md-3">
                                        <x-form.datepicker name="prac_notified" label="Prac Notified" help="Date you will be delivering letters. Please make sure you have given 7 working days notice." :value="($note->prac_notified) ? $note->prac_notified->format('d/m/Y') : ''"/>
                                    </div>

                                    <div class="col-md-3">
                                        <x-form.datepicker name="prac_meeting_date" label="Prac Meeting Date" help="Date you will be holding the Prac Meeting with the Client." :value="($note->prac_meeting) ? $note->prac_meeting->format('d/m/Y') : ''"/>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group {{ $errors->has('prac_meeting_time') ? 'has-error' : '' }}">
                                            <label for="prac_notified" class="control-label"> Prac Meeting Time
                                                <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover" data-content="Time you will be holding the Prac Meeting with the Client."> <i class="fa fa-question-circle font-grey-silver"></i></a>
                                            </label>
                                            <div class="input-group">
                                                {{--}}<input type="text" class="form-control timepicker timepicker-no-seconds" value="09:00 AM">--}}
                                                <input type="text" name="prac_meeting_time" id="prac_meeting_time" value="{{ old('prac_meeting_time', ($note->prac_meeting) ? $note->prac_meeting->format('h:i A') : '') }}" class="form-control timepicker" placeholder="09:00 AM">
                                                <span class="input-group-btn">
                                                    <button class="btn default" type="button"><i class="fa fa-clock-o"></i></button>
                                                </span>
                                            </div>
                                            <x-form.error name="prac_meeting_time"/>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Early Occupation Fields --}}
                            <div id="occupation_fields" style="display: none">
                                <div class="row">
                                    <div class="col-md-3">
                                        <x-form.datepicker name="occupation_date" label="Date of Occupancy" help="Date client took occupancy"/>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="occupation_area" label="Areas Client has taken Occupation of" rows="5"/>
                                    </div>
                                </div>
                            </div>

                            {{-- Response Required --}}
                            <div id="response_req_field" style="display: none">
                                <div class="row">
                                    <div class="col-md-3">
                                        <x-form.select name="response_req" label="Response Required" :options="['0' => 'No - FYI only', '1' => 'Yes']" :value="$note->response_req" id="response_req"/>
                                    </div>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <x-form.textarea name="notes" label="Note (Admin use only)" :value="$note->notes" rows="5"/>
                                </div>
                            </div>

                            {{-- Attachments --}}
                            <h5><b>Attachments</b></h5>
                            <hr style="margin: 10px 0px; padding: 0px;">
                            @php
                                $attachments = $note->attachments;
                                $images = $attachments->where('type', 'image');
                                $files  = $attachments->where('type', 'file');
                            @endphp
                            @if ($attachments->isNotEmpty())
                                {{-- Image attachments --}}
                                @if ($images->isNotEmpty())
                                    <div class="row" style="margin: 0">
                                        @foreach ($images as $attachment)
                                            <div style="width: 60px; float: left; padding-right: 5px">
                                                @if (Auth::user()->hasPermission2("del.site.note"))
                                                    <i class="fa fa-times font-red deleteFile" style="cursor:pointer;" data-name="{{ $attachment->name }}" data-attachid="{{$attachment->id}}"></i>
                                                @endif
                                                <a href="{{ $attachment->url }}" target="_blank" data-lity>
                                                    <img src="{{ $attachment->url }}" class="thumbnail img-responsive img-thumbnail">
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- File attachments --}}
                                @if ($files->isNotEmpty())
                                    <div class="row" style="margin: 0">
                                        @foreach ($files as $attachment)
                                            <i class="fa fa-file-text-o"></i> &nbsp; <a href="{{ $attachment->url }}" target="_blank"> {{ $attachment->name }}</a>
                                            @if (Auth::user()->hasPermission2("del.site.note"))
                                                <i class="fa fa-times font-red deleteFile" style="cursor:pointer;" data-name="{{ $attachment->name }}" data-attachid="{{$attachment->id}}"></i>
                                            @endif
                                            <br>
                                        @endforeach
                                    </div>
                                @endif
                            @else
                                None
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Upload Attachments</h5>
                                    <x-form.filepond/><br><br>
                                </div>
                            </div>


                            <br><br>
                            <div class="form-actions right">
                                <a href="/site/{{$note->site_id}}/notes" class="btn default"> Back</a>
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
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script src="/js/filepond-basic.js" type="text/javascript"></script>
    <script>
        $(document).ready(function () {
            /* Select2 */
            $("#site_id").select2({placeholder: "Select Site", width: '100%'});
            $("#site_id2").select2({placeholder: "Select Site", width: '100%'});

            $("#category_id").change(function (e) {
                e.preventDefault();
                displayFields();
            });

            $("#more").click(function (e) {
                e.preventDefault();
                $('#more').hide();
                $('#more_items').show();
            });

            displayFields();

            function displayFields() {
                var cat_id = $("#category_id").val();

                $("#variation_fields").hide();
                $("#variation_cost_fields").hide();
                $("#costing_fields").hide();
                $("#response_req_field").hide();
                $("#prac_completion_fields").hide();
                $("#occupation_fields").hide();
                $("#siteall_div").hide();
                $("#extracredit_div").show();
                $("#savenote").show();
                $("#notes_label").html('Note (Admin use only)');
                $("#uploads_label").html('Upload Attachments');

                // Approved Site Variation, For Issue to Client Site Variations, TBA Site Variation, Wet Calls
                if (cat_id == '16' || cat_id == '19' || cat_id == '20' || cat_id == '93') {
                    $("#variation_fields").show();
                }

                // Approved Site Variation, For Issue to Client Site Variations, Wet Calls
                if (cat_id == '16' || cat_id == '19' || cat_id == '93') {
                    $("#variation_cost_fields").show();
                }

                // Costing Request
                if (cat_id == '15') {
                    $("#costing_fields").show();
                    $("#notes_label").html('Description');
                }

                // Prac Completion Request
                if (cat_id == '89') {
                    $("#prac_completion_fields").show();
                }

                // Wet Call Request
                if (cat_id == '93') {
                    $("#siteall_div").show();
                    $("#site_div").hide();
                    $("#extracredit_div").hide();
                } else {
                    $("#siteall_div").hide();
                    $("#site_div").show();
                }


                // Early Occupation
                if (cat_id == '94') {
                    $("#occupation_fields").show();
                    $("#uploads_label").html('Upload Pre Occupation Photos (timestamped images are required)');
                }

                // Allowance, Plan & Details, Compliance
                var response_req_cats = ['12', '13', '14']
                if (response_req_cats.includes(cat_id)) {
                    $("#response_req_field").show();
                } else {
                    $("#response_req").val('');
                    $("#response_req_field").hide();
                }
            };
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
            format: 'dd/mm/yyyy',
        });
    </script>
@stop

