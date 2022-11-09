@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/inspection/custom">Safety in Design</a><i class="fa fa-circle"></i></li>
        <li><span>View Report</span></li>
    </ul>
@stop

@section('content')

    <div id="vueApp">
        <div class="page-content-inner">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light bordered">
                        <div class="portlet-title">
                            <div class="caption">
                                <span class="caption-subject font-green-haze bold uppercase">Site Inspection</span>
                                <span class="caption-helper"> - ID: {{ $form->id }}</span>
                            </div>
                        </div>
                        <div class="portlet-body form">
                            {!! Form::model('form', ['method' => 'PATCH', 'action' => ['Misc\Form\FormController@update', $form->id], 'class' => 'horizontal-form',  'files' => true, 'id' => 'custom_form']) !!}
                            <input type="hidden" name="form_id" id="form_id" value="{{ $form->id }}">
                            <input type="hidden" name="page" id="page" value="{{ $pagenumber }}">
                            <input type="hidden" name="nextpage" id="nextpage" value="{{ $pagenumber+1 }}">
                            <input type="hidden" name="showrequired" id="showrequired" value="{{ $showrequired }}">

                            @include('form-error')
                            @if ($showrequired && $failed_questions->count())
                                <div class="alert alert-danger alert-dismissable">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
                                    <i class="fa fa-warning"></i><strong> The follwing questions require a response</strong>
                                    <ul>
                                        @foreach ($failed_questions as $question)
                                            <li style="list-style-type: none;">@if ($form->pages->count() > 1) Page {{ $question->section->page->order }}: @endif{{ $question->name }}</li>
                                        @endforeach
                                    </ul>
                                </div>

                            @endif

                            <div class="form-body">
                                {{-- Template name + description--}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <h3 style="margin-top: 0px"> {{ $form->template->name }} @if (!$form->status)<span class="font-red pull-right" style="margin-top: 0px">COMPLETED {{ ($form->completed) ? $form->completed->format('d/m/Y') : '' }}</span>@endif</h3>
                                        {{ $form->template->description }}<br><br>
                                    </div>
                                </div>
                                {{--}}
                                <div class="row">
                                    <div class="col-md-12" style="height: 400px">
                                        <input type="file" class="my-pond" name="qq">
                                    </div>
                                </div>--}}

                                <hr class="field-hr">

                                {{-- Page Icons --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <h3 class="font-green-haze" style="display: inline-block; margin: 0px">{{ $form->pageName($pagenumber) }}</h3>
                                    <span class="pull-right">
                                       @for ($x = 1; $x <= $form->pages->count(); $x++)
                                            @if ($x == $pagenumber)
                                                <button class="btn dark" style="margin-right: 10px; cursor: default" id="pagebtn-current">{{ $x }}</button>
                                            @else
                                                <button class="btn btn-default pagebtn" style="margin-right: 10px;" page="{{$x}}">{{ $x }}</button>
                                            @endif
                                        @endfor
                                    </span>
                                    </div>
                                </div>
                                <hr class="field-hr">

                                {{-- Current Page --}}

                                {{-- Sections --}}
                                @foreach ($form->page($pagenumber)->sections as $section)
                                    <div id="sdiv-{{$section->id}}">
                                        {{-- Section Title --}}
                                        @if ($section->name)
                                            <div class="row" style="background: #f0f6fa; margin: 10px 0px 5px 0px; padding: 5px 0px;">
                                                <div class="col-md-12">

                                                    <h4 class="font-dark">{{ $section->name }}
                                                        <small><i class="fa fa-angle-down font-dark pull-right" style="margin-right: 10px"></i></small>
                                                    </h4>

                                                </div>
                                            </div>
                                        @endif


                                        {{-- Questions --}}
                                        <div style="margin-bottom: 0px">
                                            @foreach ($section->questions as $question)
                                                <?php
                                                $val = null;
                                                $qLogic = (count($question->logic)) ? 'data-logic="true"' : '';
                                                $response = $question->response($form->id);
                                                if (count($response))
                                                    $val = ($question->multiple) ? $response->pluck('value')->toArray() : $response->first()->value;

                                                $highlight_required = ($showrequired && $question->required && !$val) ? true : false;


                                                //$val = ($response) ? $response->value : null;
                                                ?>
                                                <div id="qdiv-{{$question->id}}">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <?php $required = ($question->required) ? "<small><span class='fa fa-thin fa-asterisk font-red'></span></small>" : '' ?>
                                                                <label for="name" class="control-label {{ ($highlight_required) ? 'font-red' : ''}}">{{ $question->name }} {!! $required  !!}
                                                                    {{--}}<small>T:{{ $question->type }} TS: {{ $question->type_special }} V:{!! (is_array($val)) ? print_r(implode(',', $val)) : $val !!}</small>--}}
                                                                </label>

                                                                @switch($question->type)
                                                                @case('text') {{-- Text --}}
                                                                <input type="text" name="q{{$question->id}}" class="form-control" value="{{ $val }}">
                                                                @break

                                                                @case('textarea'){{-- Textarea --}}
                                                                <textarea name="q{{$question->id}}" rows="5" class="form-control" placeholder="Details">{!! $val !!}</textarea>
                                                                @break

                                                                @case('datetime'){{-- Datetime --}}
                                                                <div class="input-group date form_datetime form_datetime bs-datetime" data-date-end-date="0d" style="width: 300px">
                                                                    <?php $val = ($question->id == 2 && $val) ? $val : Carbon\Carbon::now()->format('d/m/Y G:i') ?>
                                                                    <input type="text" name="q{{$question->id}}" class="form-control" readonly="" style="background:#FFF" value="{{ $val }}">
                                                                    <span class="input-group-addon"><button class="btn default date-set" type="button" style="height: 34px;"><i class="fa fa-calendar"></i></button></span>
                                                                </div>
                                                                @break

                                                                @case('media') {{-- Media --}}
                                                                    <div class="col-md-6">No files</div>
                                                                    <div class="col-md-6"><input type="file" class="my-filepond" name="q{{$question->id}}" style="margin-top: 10px" multiple /></div>
                                                                    {{--}}<input type="file" class="my-filepond" name="Quest{{$question->id}}" style="margin-top: 10px" multiple />--}}
                                                                @break

                                                                @case('select') {{-- Select --}}

                                                                {{-- Site --}}
                                                                @if ($question->type_special == 'site')
                                                                    <select id="q{{$question->id}}" name="q{{$question->id}}" class="form-control select2" style="width:100%">
                                                                        {!! Auth::user()->authSitesSelect2Options('view.site.list', $val) !!}
                                                                    </select>
                                                                @endif

                                                                {{-- Staff --}}
                                                                @if ($question->type_special == 'staff')
                                                                    {!! Form::select("q$question->id", Auth::user()->company->staffSelect(null, '1'), ($val) ? $val : Auth::user()->id, ['class' => 'form-control select2', 'name' => "q$question->id", 'id' => "q$question->id"]) !!}
                                                                @endif

                                                                {{-- Special Rest--}}
                                                                @if ($question->type_special && !in_array($question->type_special, ['site', 'staff']))
                                                                    <input type="hidden" id="q{{$question->id}}" name="q{{$question->id}}" value="{{ $val }}">
                                                                    {!! customFormSelectButtons($question->id, $val) !!}
                                                                @endif

                                                                {{-- Other Selects--}}
                                                                @if (!$question->type_special)
                                                                    @if ($question->multiple)
                                                                        {!! Form::select("q$question->id", $question->optionsArray(), $val, ['class' => "form-control select2", 'name' => "q$question->id[]", 'id' => "q$question->id",  'multiple', $qLogic]) !!}
                                                                    @else
                                                                        {!! Form::select("q$question->id", ['' => 'Select option'] + $question->optionsArray(), $val, ['class' => "form-control select2", 'name' => "q$question->id", 'id' => "q$question->id", $qLogic]) !!}
                                                                    @endif
                                                                @endif

                                                                @break


                                                                @default
                                                                @endswitch

                                                                {{-- Required check --}}
                                                                @if ($highlight_required)
                                                                    <div class="font-red">You must provide a response to this question</div>
                                                                @endif

                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Notes - Show --}}
                                                    <input type="hidden" id="q{{$question->id}}-notes-orig" value="{!! ($question->extraNotesForm($form->id)) ? $question->extraNotesForm($form->id)->notes : '' !!}">
                                                    <div id="shownote-{{$question->id}}" class="row hoverdiv button-note" data-qid="{{$question->id}}" style="{{ ($question->extraNotesForm($form->id)) ? '' : 'display:none' }}">
                                                        <div class="col-md-12" id="shownote-{{$question->id}}-div">
                                                            {!! ($question->extraNotesForm($form->id)) ? $question->extraNotesForm($form->id)->notes : '' !!}
                                                        </div>
                                                    </div>
                                                    {{-- Notes - Edit --}}
                                                    <div id="editnote-{{$question->id}}" style="display:none">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <textarea id="q{{$question->id}}-notes" name="q{{$question->id}}-notes" rows="5" class="form-control" placeholder="Notes">{!! ($question->extraNotesForm($form->id)) ? $question->extraNotesForm($form->id)->notes : '' !!}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="row" style="margin-top: 10px">
                                                            <div class="col-md-12">
                                                                <button class="btn green button-savenote" data-qid="{{$question->id}}">Save</button>
                                                                <button class="btn default button-cancelnote" data-qid="{{$question->id}}">Cancel</button>
                                                                <button class="btn dark button-delnote" data-qid="{{$question->id}}">&nbsp;<i class="fa fa-trash"></i>&nbsp;</button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Question Extras (Notes, Media, Actions --}}
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <button class="btn default btn-xs pull-right button-action" data-qid="{{$question->id}}">Action <i class="fa fa-check-square-o"></i></button>
                                                            <button class="btn default btn-xs pull-right button-media" style="margin-right: 10px" data-qid="{{$question->id}}">Media <i class="fa fa-picture-o"></i></button>
                                                            <button class="btn default btn-xs pull-right button-note" style="margin-right: 10px" data-qid="{{$question->id}}">Note <i class="fa fa-edit"></i></button>
                                                        </div>
                                                    </div>
                                                    <hr class="field-hr">
                                                </div> {{-- end question div --}}
                                            @endforeach
                                        </div>
                                    </div> {{-- end section div --}}
                                @endforeach

                                <br><br>
                                <div class="form-actions right">
                                    @if ($pagenumber != 1)
                                        <button class="btn green pagebtn" id="nextpage" page="{{ $pagenumber-1 }}">< Previous Page</button>
                                    @endif
                                    @if ($pagenumber < $form->pages->count())
                                        <button class="btn green pagebtn" id="nextpage" page="{{ $pagenumber+1 }}">Next Page ></button>
                                    @endif
                                    @if ($pagenumber == $form->pages->count() || $showrequired)
                                        <button class="btn blue pagebtn" id="nextpage" page="{{ $pagenumber }}">Complete Inspection</button>
                                    @endif
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@stop

@section('page-level-plugins-head')
    {{-- Filepond --}}
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css"/>
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">
    {{--}}<link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">--}}

    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    {{--<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>--}}
    {{--<script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>--}}
    {{--}}<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js"></script>--}}
    {{--}}<script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>--}}
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>

    <!-- FilePond -->
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-resize/dist/filepond-plugin-image-resize.js"></script>
    <!--<script src="https://unpkg.com/filepond-plugin-image-transform/dist/filepond-plugin-image-transform.js"></script>-->
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
    <!--<script src="https://unpkg.com/jquery-filepond/filepond.jquery.js"></script>-->
    {{--}}<script src="https://unpkg.com/jquery-filepond/filepond.jquery.js"></script>--}}
    {{--}}<script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-resize.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-transform.min.js"></script>--}}
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/js/custom-form-filepond.js"></script>
<script src="/js/filepond-setup.js"></script>
<script type="text/javascript">
    $.ajaxSetup({
        header: $('meta[name="_token"]').attr('content')
    })

    $(document).ready(function () {
        /* Select2 */
        //$("#site_id").select2({placeholder: "Select Site"});

        // Define Select 2 questions
        var select2_ids = @json($s2_ids);
        var select2_phs = @json($s2_phs);
        for (var i = 0; i < select2_ids.length; i++) {
            var id = select2_ids[i];
            var placeholder = (select2_phs[id]) ? select2_phs[id] : "Select one or more options";
            $("#q" + id).select2({placeholder: placeholder});
            //console.log("s2:" + select2_ids[i]);
        }

        // Prevent form from submitting for current page
        $('#pagebtn-current').click(function (e) {
            e.preventDefault(e);// do nothing
        });

        // Manually submit form new page
        $('.pagebtn').click(function (e) {
            e.preventDefault(e);
            var page = $(this).attr('page');
            $('#nextpage').val($(this).attr('page'));
            //alert('btn:'+ page);
            // $('#custom_form').submit();
            document.getElementById('custom_form').submit();
        });

        //
        // Note functions
        //

        // Edit Note
        $('.button-note').click(function (e) {
            e.preventDefault(e);
            var qid = $(this).attr('data-qid');
            $('#shownote-' + qid).hide();
            $('#editnote-' + qid).show();
        });

        // Save Note
        $('.button-savenote').click(function (e) {
            e.preventDefault(e);
            //var qid = e.target.id.split('btn-savenote-').pop();
            var qid = $(this).attr('data-qid');
            $('#editnote-' + qid).hide();
            $('#shownote-' + qid).show();
            $('#q' + qid + '-notes-orig').val($('#q' + qid + '-notes').val()); // Update orig note to new saved val
            $('#shownote-' + qid + '-div').html($('#q' + qid + '-notes').val());
        });

        // Cancel Note
        $('.button-cancelnote').click(function (e) {
            e.preventDefault(e);
            var qid = $(this).attr('data-qid');
            $('#editnote-' + qid).hide();
            $('#shownote-' + qid).show();
            // Reset val to orig for both value + div html
            $('#shownote-' + qid + '-div').html($('#q' + qid + '-notes-orig').val())
            $('#q' + qid + '-notes').val($('#q' + qid + '-notes-orig').val());
        });

        // Delete Note
        $('.button-delnote').click(function (e) {
            e.preventDefault(e);
            //var qid = e.target.id.split('btn-delnote-').pop();
            var qid = $(this).attr('data-qid');
            $('#editnote-' + qid).hide();
            $('#shownote-' + qid).hide();
            $('#shownote-' + qid + '-div').html($('#q' + qid + '-notes-orig').val('')); // clear html for note
            $('#q' + qid + '-notes').val('');  // clear val for note
        });

        //
        // Media functions
        //

        // add Media
        $('.button-media').click(function (e) {
            e.preventDefault(e);
            var qid = $(this).attr('data-qid');
            alert('add media:' + qid);
            //$('#shownote-' + qid).hide();
            //$('#editnote-' + qid).show();
        });

        //
        // Action function
        //

        // add Media
        $('.button-action').click(function (e) {
            e.preventDefault(e);
            var qid = $(this).attr('data-qid');
            alert('add action:' + qid);
            //$('#shownote-' + qid).hide();
            //$('#editnote-' + qid).show();
        });

        //
        // Select Buttons
        //
        $('.button-resp').click(function (e) {
            e.preventDefault(e);
            var qid = $(this).attr('data-qid');
            var rid = $(this).attr('data-rid');
            var btype = $(this).attr('data-btype');
            var bval = $(this).attr('data-bval');
            var logic = $(this).attr('data-logic');
            //alert('q:'+qid+' r:'+rid);

            // Loop through all buttons for selected question + remove active classes
            var buttons = document.querySelectorAll(`[data-qid='${qid}']`);
            for (var i = 0; i < buttons.length; i++) {
                $('#' + buttons[i].id).removeClass('btn-default red green dark')
            }

            // Add active class to selected button
            if ($('#q' + qid).val() != rid) {
                $('#q' + qid + '-' + rid).addClass(btype);
                $('#q' + qid).val(rid);
                //console.log('adding:'+btype+' bval:'+bval + ' rid:'+rid+ ' qval:'+$('#q' + qid).val());
            } else
                $('#q' + qid).val('');

            //console.log(buttons[0].id);
            //console.log(buttons);

            // Apply logic if required
            if (logic)
                performLogic();

        });

        // Select Buttons
        $('select').change(function (e) {
            var logic = $(this).attr('data-logic');
            // Apply logic if required
            if (logic)
                performLogic();
        });


        //
        // Page Logic
        //
        function performLogic() {
            var logic = @json($formlogic);

            for (var i = 0; i < logic.length; i++) {
                var id = logic[i]['id'];
                var question_id = logic[i]['question_id'];
                var match_op = logic[i]['match_operation'];
                var match_val = logic[i]['match_value'];
                var trigger = logic[i]['trigger'];
                var tid = logic[i]['trigger_id'];
                var qval = String($('#q' + question_id).val());
                var qval_array = (qval) ? qval.split(',') : [];

                //console.log("id:" + id + " q:" + question_id + " m:" + match_op + " mval:" + match_val + " t:" + trigger + ' tid:' + tid + ' qval:' + qval);

                // Sections
                if (trigger == 'section') {
                    if (match_op == '=') {
                        if (qval == match_val)
                            $('#sdiv-' + tid).show();
                        else
                            $('#sdiv-' + tid).hide();
                    }
                }

                // Questions
                if (trigger == 'question') {
                    //console.log('Question qid:' + question_id + ' qval:' + qval + ' mval:' + match_val + ' tid:' + tid);
                    if (match_op == '=') {
                        if (qval == match_val)
                            $('#qdiv-' + tid).show();
                        else
                            $('#qdiv-' + tid).hide();
                    }

                    // Match item in array
                    if (match_op == '=*') {
                        var match_array = match_val.split(',');
                        $('#qdiv-' + tid).hide();
                        // Loop through response value to determine if trigger is actioned
                        for (var i = 0; i < qval_array.length; i++) {
                            if (match_array.includes(qval_array[i])) {
                                $('#qdiv-' + tid).show();
                            }
                        }
                    }
                }
            }
        }

        performLogic();


        //$('#custom_form').on('submit', function (e) {
        //    e.preventDefault(e);
        //});

    });

    // Force datepicker to not be able to select dates after today
    $('.bs-datetime').datetimepicker({
        endDate: new Date(),
        format: 'dd/mm/yyyy hh:ii',
    });

</script>
@stop