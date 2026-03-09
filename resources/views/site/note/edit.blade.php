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
                        {!! Form::model($note, ['method' => 'PATCH', 'action' => ['Site\SiteNoteController@update', $note->id], 'class' => 'horizontal-form']) !!}

                        @include('form-error')

                        <div class="form-body">
                            <div class="row">
                                {{-- Site --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('site_name', 'Site', ['class' => 'control-label']) !!}
                                        {!! Form::text('site_name', $note->site->name, ['class' => 'form-control', 'readonly', 'id' => 'site_name']) !!}
                                        <input type="hidden" name="site_id" value="{{ $note->site_id }}">
                                    </div>
                                </div>
                                {{-- Category --}}
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('category_name', 'Category', ['class' => 'control-label']) !!}
                                        {!! Form::text('category_name', ($note->category_id) ? $note->category->name : 'none', ['class' => 'form-control', 'readonly', 'id' => 'category_name']) !!}
                                        <input type="hidden" name="category_id" id="category_id" value="{{ $note->category_id }}">
                                    </div>
                                </div>
                            </div>

                            {{-- Variation Fields --}}
                            <div id="variation_fields" style="display: none">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group {!! fieldHasError('variation_name', $errors) !!}">
                                            {!! Form::label('variation_name', 'Variation Name', ['class' => 'control-label']) !!}
                                            {!! Form::text('variation_name', $note->variation_name, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('variation_name', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group {!! fieldHasError('variation_info', $errors) !!}">
                                            {!! Form::label('variation_info', 'Variation Description', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('variation_info', $note->variation_info, ['rows' => '5', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('variation_info', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="variation_cost_fields" style="display: none">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('variation_net', $errors) !!}">
                                            <label for="variation_net" class="control-label">Net Cost <span class="font-grey-silver">(Admin use only)</span> </label>
                                            {!! Form::text('variation_net', $note->variation_net, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('variation_net', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('variation_cost', $errors) !!}">
                                            {!! Form::label('variation_cost', 'Gross Cost (incl GST + 20% margin)', ['class' => 'control-label']) !!}
                                            {!! Form::text('variation_cost', $note->variation_cost, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('variation_cost', $errors) !!}
                                        </div>
                                    </div>
                                    <div id="extracredit_div">
                                        <div class="col-md-3">
                                            <div class="form-group {!! fieldHasError('variation_extra_credit', $errors) !!}">
                                                {!! Form::label('variation_extra_credit', 'Credit / Extra', ['class' => 'control-label']) !!}
                                                {!! Form::select('variation_extra_credit', ['' => 'Select option', 'Extra' => 'Extra', 'Credit' => 'Credit'], $note->costing_extra_credit, ['class' => 'form-control bs-select', 'id' => 'variation_extra_credit']) !!}
                                                {!! fieldErrorMessage('variation_extra_credit', $errors) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group {!! fieldHasError('variation_days', $errors) !!}">
                                            <label for="variation_days" class="control-label">Total Extension Days (discussed with Client) Description <span class="font-grey-silver">(Admin use only)</span> </label>
                                            <input type="text" class="form-control" value="{{ $note->variation_days }}" id="variation_days" name="variation_days" onkeydown="return isNumber(event)"/>
                                            {!! fieldErrorMessage('variation_days', $errors) !!}
                                        </div>
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
                                            <div class="form-group {!! fieldHasError("cc-$cost->id", $errors) !!}">
                                                {!! Form::select("cc-$cost->id", ['' => 'Select cost centre'] + $cost_centres, $cost->category->id, ['class' => 'form-control bs-select']) !!}
                                                {!! fieldErrorMessage("cc-$cost->id", $errors) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="form-group {!! fieldHasError("cinfo-$cost->id", $errors) !!}">
                                                {!! Form::text("cinfo-$cost->id", $cost->details, ['class' => 'form-control', 'placeholder' => "Details & Cost of item."]) !!}
                                                {!! fieldErrorMessage("cinfo-$cost->id", $errors) !!}
                                            </div>
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
                                        <div class="form-group {!! fieldHasError('costing_extra_credit', $errors) !!}">
                                            {!! Form::label('costing_extra_credit', 'Credit / Extra', ['class' => 'control-label']) !!}
                                            {!! Form::select('costing_extra_credit', ['' => 'Select option', 'Extra' => 'Extra', 'Credit' => 'Credit'], $note->costing_extra_credit, ['class' => 'form-control bs-select', 'id' => 'costing_extra_credit']) !!}
                                            {!! fieldErrorMessage('costing_extra_credit', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('costing_item', $errors) !!}">
                                            {!! Form::label('costing_item', 'New item / In Lieu of', ['class' => 'control-label']) !!}
                                            {!! Form::select('costing_item', ['' => 'Select option', 'New item' => 'New item', 'In Lieu of' => 'In Lieu of'], $note->costing_item, ['class' => 'form-control bs-select', 'id' => 'costing_item']) !!}
                                            {!! fieldErrorMessage('costing_item', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('costing_priority', $errors) !!}">
                                            {!! Form::label('costing_priority', 'Priority', ['class' => 'control-label']) !!}
                                            {!! Form::select('costing_priority', ['' => 'Select option', '1-2 days' => '1-2 days', '3-5 days' => '3-5 days', '5+ days' => '5+ days'], $note->costing_priority, ['class' => 'form-control bs-select', 'id' => 'costing_item']) !!}
                                            {!! fieldErrorMessage('costing_priority', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('costing_room', $errors) !!}">
                                            {!! Form::label('costing_room', 'Room', ['class' => 'control-label']) !!}
                                            {!! Form::text('costing_room', $note->costing_room, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('costing_room', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="form-group {!! fieldHasError('costing_location', $errors) !!}">
                                            {!! Form::label('costing_location', 'Location', ['class' => 'control-label']) !!}
                                            {!! Form::text('costing_location', $note->costing_location, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('costing_location', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Prac Completion Fields --}}
                            <div id="prac_completion_fields" style="display: none">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('prac_notified', $errors) !!}">
                                            <label for="prac_notified" class="control-label"> Prac Notified
                                                <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                                   data-content="Date you will be delivering letters. Please make sure you have given 7 working days notice."> <i class="fa fa-question-circle font-grey-silver"></i>
                                                </a>
                                            </label>
                                            <div class="input-group date date-picker">
                                                {!! Form::text('prac_notified', ($note->prac_notified) ? $note->prac_notified->format('d/m/Y') : '', ['class' => 'form-control form-control-inline', 'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy", 'placeholder' => 'dd/mm/yyyy']) !!}
                                                <span class="input-group-btn"><button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button></span>
                                            </div>
                                            {!! fieldErrorMessage('prac_notified', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('prac_meeting_date', $errors) !!}">
                                            <label for="prac_notified" class="control-label"> Prac Meeting Date
                                                <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                                   data-content="Date you will be holding the Prac Meeting with the Client."> <i class="fa fa-question-circle font-grey-silver"></i>
                                                </a>
                                            </label>
                                            <div class="input-group date date-picker">
                                                {!! Form::text('prac_meeting_date', ($note->prac_meeting) ? $note->prac_meeting->format('d/m/Y') : '', ['class' => 'form-control form-control-inline', 'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy", 'placeholder' => 'dd/mm/yyyy']) !!}
                                                <span class="input-group-btn"><button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button></span>
                                            </div>
                                            {!! fieldErrorMessage('prac_meeting_date', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('prac_meeting_time', $errors) !!}">
                                            <label for="prac_notified" class="control-label"> Prac Meeting Time
                                                <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                                   data-content="Time you will be holding the Prac Meeting with the Client."> <i class="fa fa-question-circle font-grey-silver"></i>
                                                </a>
                                            </label>
                                            <div class="input-group">
                                                {{--}}<input type="text" class="form-control timepicker timepicker-no-seconds" value="09:00 AM">--}}
                                                {!! Form::text('prac_meeting_time', ($note->prac_meeting) ? $note->prac_meeting->format('h:i A') : '', ['class' => 'form-control timepicker', 'placeholder' => "09:00 AM"]) !!}
                                                <span class="input-group-btn">
                                                    <button class="btn default" type="button"><i class="fa fa-clock-o"></i></button>
                                                </span>
                                            </div>
                                            {!! fieldErrorMessage('prac_meeting_time', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Early Occupation Fields --}}
                            <div id="occupation_fields" style="display: none">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('occupation_date', $errors) !!}">
                                            <label for="occupation_date" class="control-label"> Date of Occupancy
                                                <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                                   data-content="Date client took occupancy"> <i class="fa fa-question-circle font-grey-silver"></i>
                                                </a>
                                            </label>
                                            <div class="input-group date date-picker">
                                                {!! Form::text('occupation_date', '', ['class' => 'form-control form-control-inline', 'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy", 'placeholder' => 'dd/mm/yyyy']) !!}
                                                <span class="input-group-btn"><button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button></span>
                                            </div>
                                            {!! fieldErrorMessage('occupation_date', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('occupation_area', $errors) !!}">
                                            {!! Form::label('occupation_area', 'Areas Client has taken Occupation of', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('occupation_area', null, ['rows' => '5', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('occupation_area', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Response Required --}}
                            <div id="response_req_field" style="display: none">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('response_req', $errors) !!}">
                                            {!! Form::label('response_req', 'Response Required', ['class' => 'control-label']) !!}
                                            {!! Form::select('response_req', ['0' => 'No - FYI only', '1' => 'Yes'], $note->response_req, ['class' => 'form-control bs-select', 'id' => 'response_req']) !!}
                                            {!! fieldErrorMessage('response_req', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('notes', $errors) !!}">
                                        {!! Form::label('notes', 'Note (Admin use only)', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('notes', $note->notes, ['rows' => '5', 'class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('notes', $errors) !!}
                                    </div>
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
                                    <input type="file" class="filepond" name="filepond[]" multiple/><br><br>
                                </div>
                            </div>


                            <br><br>
                            <div class="form-actions right">
                                <a href="/site/{{$note->site_id}}/notes" class="btn default"> Back</a>
                                <button type="submit" class="btn green" id="submit"> Save</button>
                            </div>

                        </div>
                        {!! Form::close() !!}
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

