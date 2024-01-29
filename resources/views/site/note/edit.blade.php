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
                                    <div class="form-group {!! fieldHasError('site_id', $errors) !!}">
                                        {!! Form::label('site_id', 'Site', ['class' => 'control-label']) !!}
                                        {!! Form::select('site_id', $site_list, $note->site_id, ['class' => 'form-control select2', 'id' => 'site_id']) !!}
                                        {!! fieldErrorMessage('site_id', $errors) !!}
                                    </div>
                                </div>
                                {{-- Category --}}
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('category_id', $errors) !!}">
                                        {!! Form::label('category_id', 'Category', ['class' => 'control-label']) !!}
                                        {!! Form::select('category_id', ['' => 'Select category'] + $categories, $note->category_id, ['class' => 'form-control bs-select', 'id' => 'category_id']) !!}
                                        {!! fieldErrorMessage('category_id', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- Variation Fields --}}
                            <div id="variation_fields" style="display: none">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('variation_name', $errors) !!}">
                                            {!! Form::label('variation_name', 'Variation Name', ['class' => 'control-label']) !!}
                                            {!! Form::text('variation_name', $note->variation_name, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('variation_name', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="form-group {!! fieldHasError('variation_info', $errors) !!}">
                                            {!! Form::label('variation_info', 'Variation Description', ['class' => 'control-label']) !!}
                                            {!! Form::text('variation_info', $note->variation_info, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('variation_info', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('variation_cost', $errors) !!}">
                                            {!! Form::label('variation_cost', 'Gross Cost (incl GST + 20% margin)', ['class' => 'control-label']) !!}
                                            {!! Form::text('variation_cost', $note->variation_cost, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('variation_cost', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group {!! fieldHasError('variation_days', $errors) !!}">
                                            {!! Form::label('variation_days', 'Total Extension Days (discussed with Client) Description', ['class' => 'control-label']) !!}
                                            <input type="text" class="form-control" value="{{$note->variation_days}}" id="variation_days" name="variation_days" onkeydown="return isNumber(event)"/>
                                            {!! fieldErrorMessage('variation_days', $errors) !!}
                                        </div>
                                    </div>
                                </div>
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

                            {{-- Response Required --}}
                            <div id="response_req_field" style="display: none">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('response_req', $errors) !!}">
                                            {!! Form::label('response_req', 'Response Required', ['class' => 'control-label']) !!}
                                            {!! Form::select('response_req', ['0' => 'No - FYI only', '1' => 'Yes'], null, ['class' => 'form-control bs-select', 'id' => 'response_req']) !!}
                                            {!! fieldErrorMessage('response_req', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('notes', $errors) !!}">
                                        {!! Form::label('notes', 'Note', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('notes', $note->notes, ['rows' => '5', 'class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('notes', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- Attachments --}}
                            <h5><b>Existing Attachments</b></h5>
                            @if ($note->attachments()->count())
                                <hr style="margin: 10px 0px; padding: 0px;">
                                {{-- Image attachments --}}
                                <div class="row" style="margin: 0">
                                    @foreach ($note->attachments() as $attachment)
                                        @if ($attachment->type == 'image' && file_exists(public_path($attachment->url)))
                                            <div style="width: 60px; float: left; padding-right: 5px">
                                                <a href="{{ $attachment->url }}" target="_blank" class="html5lightbox " title="{{ $attachment->name }}" data-lity>
                                                    <img src="{{ $attachment->url }}" class="thumbnail img-responsive img-thumbnail"></a>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                {{-- File attachments  --}}
                                <div class="row" style="margin: 0">
                                    @foreach ($note->attachments() as $attachment)
                                        @if ($attachment->type == 'file' && file_exists(public_path($attachment->url)))
                                            <i class="fa fa-file-text-o"></i> &nbsp; <a href="{{ $attachment->url }}" target="_blank"> {{ $attachment->name }}</a><br>
                                        @endif
                                    @endforeach
                                </div>
                                <br>
                            @else
                                None
                            @endif

                            {{-- Attachments --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Upload Attachments</h5>
                                    <input type="file" class="filepond" name="filepond[]" multiple/><br><br>
                                </div>
                            </div>


                            <br><br>
                            <div class="form-actions right">
                                <a href="/site/{{$note->site_id}}/notes" class="btn default"> Back</a>
                                <button type="submit" class="btn green"> Save</button>
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
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script>
        //
        // FilePond
        //
        // Get a reference to the file input element
        const inputElement = document.querySelector('input[type="file"]');

        // Create a FilePond instance
        const pond = FilePond.create(inputElement);
        FilePond.setOptions({
            server: {
                url: '/file/upload',
                fetch: null,
                revert: null,
                headers: {'X-CSRF-TOKEN': $('meta[name=token]').attr('value')},
            },
            allowMultiple: true,
        });

        $(document).ready(function () {
            /* Select2 */
            $("#site_id").select2({placeholder: "Select Site",});

            $("#category_id").change(function (e) {
                e.preventDefault();
                displayFields();
            });

            displayFields();

            function displayFields() {
                var cat_id = $("#category_id").val();

                $("#variation_fields").hide();
                $("#response_req_field").hide();
                $("#savenote").show();

                if (cat_id == '16') {
                    $("#variation_fields").show();
                }

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
    </script>
@stop

