@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/{{$site_id}}/notes">Site Notes</a><i class="fa fa-circle"></i></li>
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
                            <span class="caption-subject font-green-haze bold uppercase">Create Site Note</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('SiteNote', ['action' => 'Site\SiteNoteController@store', 'class' => 'horizontal-form', 'files' => true]) !!}
                        <input name="previous_url" type="hidden" value="{!! url()->previous() !!}">
                        @include('form-error')

                        <div class="form-body">
                            <div class="row">
                                {{-- Site --}}
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('site_id', $errors) !!}">
                                        {!! Form::label('site_id', 'Site', ['class' => 'control-label']) !!}
                                        {!! Form::select('site_id', $site_list, $site_id, ['class' => 'form-control select2', 'id' => 'site_id']) !!}
                                        {!! fieldErrorMessage('site_id', $errors) !!}
                                    </div>
                                </div>
                                {{-- Category --}}
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('category_id', $errors) !!}">
                                        {!! Form::label('category_id', 'Category', ['class' => 'control-label']) !!}
                                        {!! Form::select('category_id', ['' => 'Select category'] + $categories, 16, ['class' => 'form-control bs-select', 'id' => 'category_id']) !!}
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
                                            {!! Form::text('variation_name', $existing->variation_name, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('variation_name', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="form-group {!! fieldHasError('variation_info', $errors) !!}">
                                            {!! Form::label('variation_info', 'Variation Description', ['class' => 'control-label']) !!}
                                            {!! Form::text('variation_info', $existing->variation_info, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('variation_info', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="variation_cost_fields" style="display: none">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="form-group {!! fieldHasError('variation_net', $errors) !!}">
                                            {!! Form::label('variation_net', 'Net Cost', ['class' => 'control-label']) !!}
                                            {!! Form::text('variation_net', $existing->variation_net, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('variation_net', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('variation_cost', $errors) !!}">
                                            {!! Form::label('variation_cost', 'Gross Cost (incl GST + 20% margin)', ['class' => 'control-label']) !!}
                                            {!! Form::text('variation_cost', $existing->variation_cost, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('variation_cost', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('costing_extra_credit', $errors) !!}">
                                            {!! Form::label('costing_extra_credit', 'Credit / Extra', ['class' => 'control-label']) !!}
                                            {!! Form::select('costing_extra_credit', ['' => 'Select option', 'Extra' => 'Extra', 'Credit' => 'Credit'], $existing->costing_extra_credit, ['class' => 'form-control bs-select', 'id' => 'costing_extra_credit']) !!}
                                            {!! fieldErrorMessage('costing_extra_credit', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group {!! fieldHasError('variation_days', $errors) !!}">
                                            {!! Form::label('variation_days', 'Total Extension Days (discussed with Client) Description', ['class' => 'control-label']) !!}
                                            <input type="text" class="form-control" value="{{ $existing->variation_days }}" id="variation_days" name="variation_days" onkeydown="return isNumber(event)"/>
                                            {!! fieldErrorMessage('variation_days', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                {{-- Variation items --}}
                                <div class="row">
                                    <div class="col-md-12">Variation items</div>
                                </div>
                                @php ($i = 0)
                                @foreach ($existing->costs as $cost)
                                    @php ($i++)
                                    <div class="row">
                                        <div class="col-md-1 text-center">{{ $i }}.</div>
                                        <div class="col-md-3">
                                            <div class="form-group {!! fieldHasError("cc-$i", $errors) !!}">
                                                {!! Form::select("cc-$i", ['' => 'Select cost centre'] + $cost_centres, $cost->cost_id, ['class' => 'form-control bs-select']) !!}
                                                {!! fieldErrorMessage("cc-$i", $errors) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group {!! fieldHasError("cinfo-$i", $errors) !!}">
                                                {!! Form::text("cinfo-$i", $cost->details, ['class' => 'form-control', 'placeholder' => "Details of item $cost->order."]) !!}
                                                {!! fieldErrorMessage("cinfo-$i", $errors) !!}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                {{-- Extra Items --}}
                                <button class="btn blue" id="more">More Items</button>
                                <div id="more_items" style="display: none">
                                    @for ($i = $existing->costs->count() + 1; $i <= 20; $i++)
                                        <div class="row">
                                            <div class="col-md-1 text-center">{{ $i }}.</div>
                                            <div class="col-md-3">
                                                <div class="form-group {!! fieldHasError("cc-$i", $errors) !!}">
                                                    {!! Form::select("cc-$i", ['' => 'Select cost centre'] + $cost_centres, null, ['class' => 'form-control bs-select']) !!}
                                                    {!! fieldErrorMessage("cc-$i", $errors) !!}
                                                </div>
                                            </div>
                                            <div class="col-md-7">
                                                <div class="form-group {!! fieldHasError("cinfo-$i", $errors) !!}">
                                                    {!! Form::text("cinfo-$i", null, ['class' => 'form-control', 'placeholder' => "Details of item $i."]) !!}
                                                    {!! fieldErrorMessage("cinfo-$i", $errors) !!}
                                                </div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                                <br><br>
                            </div>

                            {{-- Costing Fields --}}
                            <div id="costing_fields" style="display: none">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('costing_extra_credit', $errors) !!}">
                                            {!! Form::label('costing_extra_credit', 'Credit / Extra', ['class' => 'control-label']) !!}
                                            {!! Form::select('costing_extra_credit', ['' => 'Select option', 'Extra' => 'Extra', 'Credit' => 'Credit'], null, ['class' => 'form-control bs-select', 'id' => 'costing_extra_credit']) !!}
                                            {!! fieldErrorMessage('costing_extra_credit', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('costing_item', $errors) !!}">
                                            {!! Form::label('costing_item', 'New item / In Lieu of', ['class' => 'control-label']) !!}
                                            {!! Form::select('costing_item', ['' => 'Select option', 'New item' => 'New item', 'In Lieu of' => 'In Lieu of'], null, ['class' => 'form-control bs-select', 'id' => 'costing_item']) !!}
                                            {!! fieldErrorMessage('costing_item', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('costing_priority', $errors) !!}">
                                            {!! Form::label('costing_priority', 'Priority', ['class' => 'control-label']) !!}
                                            {!! Form::select('costing_priority', ['' => 'Select option', '1-2 days' => '1-2 days', '3-5 days' => '3-5 days', '5+ days' => '5+ days'], null, ['class' => 'form-control bs-select', 'id' => 'costing_item']) !!}
                                            {!! fieldErrorMessage('costing_priority', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('costing_room', $errors) !!}">
                                            {!! Form::label('costing_room', 'Room', ['class' => 'control-label']) !!}
                                            {!! Form::text('costing_room', null, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('costing_room', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="form-group {!! fieldHasError('costing_location', $errors) !!}">
                                            {!! Form::label('costing_location', 'Location', ['class' => 'control-label']) !!}
                                            {!! Form::text('costing_location', null, ['class' => 'form-control']) !!}
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
                                        {!! Form::label('notes', 'Note', ['class' => 'control-label', 'id' => 'notes_label']) !!}
                                        {!! Form::textarea('notes', null, ['rows' => '5', 'class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('notes', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- Attachments --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Upload Attachments</h5>
                                    <input type="file" class="filepond" name="filepond[]" multiple/><br><br>
                                </div>
                            </div>

                            <br><br>
                            <div class="form-actions right">
                                <a href="{!! url()->previous() !!}" class="btn default"> Back</a>
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
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/js/filepond-basic.js" type="text/javascript"></script>
    <script>
        $(document).ready(function () {
            /* Select2 */
            $("#site_id").select2({placeholder: "Select Site",});

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
                $("#savenote").show();
                $("#notes_label").html('Note');

                if (cat_id == '16' || cat_id == '19' || cat_id == '20') { // Approved Site Variation, For Issue to Client Site Variations, TBA Site Variation
                    $("#variation_fields").show();
                }

                if (cat_id == '16' || cat_id == '19') { // Approved Site Variation, For Issue to Client Site Variations
                    $("#variation_cost_fields").show();
                    //$("#notes_label").html('Variation Breakup/Work Order Details');
                }

                if (cat_id == '15') {
                    $("#costing_fields").show();
                    $("#notes_label").html('Description');
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

