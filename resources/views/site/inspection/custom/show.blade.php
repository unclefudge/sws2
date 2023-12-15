@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/inspection">Site Inspections</a><i class="fa fa-circle"></i></li>
        <li><a href="/site/inspection/list/{{ $form->template->parent_id }}">{{ $form->template->name }}</a><i class="fa fa-circle"></i></li>
        <li><span>View Report</span></li>
    </ul>
@stop

@section('content')

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
                        <input type="hidden" name="page_id" id="page_id" value="{{ $page->id }}">
                        <input type="hidden" name="status" id="status" value="{{ $form->status }}">
                        <input type="hidden" name="page" id="page" value="{{ $pagenumber }}">
                        <input type="hidden" name="nextpage" id="nextpage" value="{{ $pagenumber+1 }}">
                        <input type="hidden" name="addAction" id="addAction" value="0">
                        <input type="hidden" name="showAction" id="showAction" value="0">
                        <input type="hidden" name="showrequired" id="showrequired" value="{{ $showrequired }}">

                        @include('form-error')
                        @if ($showrequired && $failed_questions->count())
                            <div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
                                <i class="fa fa-warning"></i><strong> The follwing questions require a response</strong>
                                <ul>
                                    @foreach ($failed_questions as $question)
                                        <li style="list-style-type: none;">@if ($form->pages()->count() > 1)
                                                Page {{ $question->section->page->order }}:
                                            @endif{{ $question->name }}</li>
                                    @endforeach
                                </ul>
                            </div>

                        @endif

                        <div class="form-body">
                            {{-- Template name + description--}}
                            <div class="row">
                                <div class="col-md-12">
                                    <h3 style="margin-top: 0px"> {{ $form->template->name }} @if (!$form->status)
                                            <span class="font-red pull-right" style="margin-top: 0px">COMPLETED {{ ($form->completed_at) ? $form->completed_at->format('d/m/Y') : '' }}</span>
                                        @endif</h3>
                                    {{ $form->template->description }}<br><br>
                                </div>
                            </div>
                            <hr class="field-hr">

                            {{-- Page Icons --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <h3 class="font-green-haze" style="display: inline-block; margin: 0px">{{ $form->pageName($pagenumber) }}</h3>
                                    <span class="pull-right">
                                       @for ($x = 1; $x <= $form->pages()->count(); $x++)
                                            @if ($x == $pagenumber)
                                                <button class="btn dark" style="margin: 0 5px 5px 0; cursor: default" id="pagebtn-current">{{ $x }}</button>
                                            @else
                                                <button class="btn btn-default pagebtn" style="margin: 0 5px 5px 0;" gotopage="{{$x}}">{{ $x }}</button>
                                            @endif
                                        @endfor
                                    </span>
                                </div>
                            </div>
                            <hr class="field-hr">

                            {{-- Current Page --}}

                            {{-- Sections --}}
                            @foreach ($sections as $section)
                                <div id="sdiv-{{$section->id}}">
                                    {{-- Section Title --}}
                                    @if ($section->name)
                                        <div class="row" style="background: #f0f6fa; margin: 10px 0px 5px 0px; padding: 5px 0px; cursor: pointer" onclick="toggleSection({{$section->id}})">
                                            <div class="col-md-12">
                                                <h4 class="font-dark">
                                                    <small><i id="sdiv-{{$section->id}}-arrow" class="fa fa-angle-down font-dark" style="margin-right: 10px"></i></small> {{ $section->name }}
                                                </h4>
                                            </div>
                                        </div>
                                    @endif

                                    <div id="sdiv-{{$section->id}}-content">
                                        {{-- Questions --}}
                                        <div style="margin-bottom: 0px">
                                            @foreach ($section->questions as $question)
                                                @include('site/inspection/custom/_show_question')
                                            @endforeach
                                        </div>

                                        {{-- Child sections --}}
                                        @foreach ($section->allChildSections as $childSection)
                                            @include('site/inspection/custom/_child_section', ['child_section' => $childSection])
                                        @endforeach
                                    </div> {{-- end section-content div --}}
                                </div> {{-- end section div --}}
                            @endforeach


                            {{-- Media Summary --}}
                            @if (!$form->status && $pagenumber == '1')
                                <h3 class="font-green-haze">Media Summary</h3>
                                @if ($form->files()->count())
                                    {{--}}<div style="margin-bottom: 10px">Media:</div>--}}
                                    @foreach ($form->files() as $file)
                                        <img src="{{$file->attachment}}" class="mygallery" id="q{{$question->id}}-photo-{{$file->attachment}}" width="100" style="margin:0px 10px 10px 0px">
                                    @endforeach
                                @else
                                    No media found
                                @endif
                            @endif

                            <br><br>
                            <div class="form-actions right">
                                @if ($pagenumber != 1)
                                    <button class="btn blue pagebtn" id="prevpage" gotopage="{{ $pagenumber-1 }}">< Previous Page</button>
                                @endif
                                @if ($pagenumber < $form->pages()->count())
                                    <button class="btn blue pagebtn" id="nextpage" gotopage="{{ $pagenumber+1 }}">Next Page ></button>
                                @endif
                                @if ($form->status && ($pagenumber == $form->pages()->count() || $showrequired))
                                    <button class="btn green pagebtn" id="complete" gotopage="complete">Complete Inspection</button>
                                @endif
                                @if (!$form->status)
                                    <button class="btn green pagebtn" id="save" gotopage="{{ $pagenumber }}">Save</button>
                                    <button class="btn red" id="reopen" gotopage="{{ $pagenumber }}">Re-open Inspection</button>
                                @endif
                            </div>
                        </div>
                        {!! Form::close() !!}

                        {{--}}
                        @if (count($projects) > 0)
                            <ul>
                                @foreach ($projects as $project)
                                    @include('site/inspection/custom/_show_section', $project)
                                @endforeach
                            </ul>
                        @else
                            No Projects
                        @endif
                        --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="myGalleryFullscreen" class="mygallery-overlay">
        <a id="closeGallery" href="javascript:void(0)" class="mygallery-close" onclick="closeGalleryPreview()"><i class="fa fa-times"></i></a>
        <a id="deleteGallery" href="javascript:void(0)" class="mygallery-delete" onclick="deleteGalleryPreview()"><i class="fa fa-trash"></i></a>
        <a id="downloadGallery" href="javascript:void(0)" class="mygallery-download" onclick="downloadGalleryPreview()"><i class="fa fa-download"></i></a>
        <div class="mygallery-overlay-content">
            <img class="img-fluid" id="myGalleryImage" src="">
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
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <!--<script src="https://unpkg.com/filepond-plugin-image-transform/dist/filepond-plugin-image-transform.js"></script>-->
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
    <!--<script src="https://unpkg.com/jquery-filepond/filepond.jquery.js"></script>-->
    {{--}}<script src="https://unpkg.com/jquery-filepond/filepond.jquery.js"></script>--}}
    {{--}}<script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-resize.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-transform.min.js"></script>--}}
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/js/site-inspection-filepond.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            const formStatus = {{ $form->status }};

            // Define Select 2 questions
            if (formStatus) {
                /* Select2 */
                var select2_ids = @json($s2_ids);
                var select2_phs = @json($s2_phs);
                for (var i = 0; i < select2_ids.length; i++) {
                    var id = select2_ids[i];
                    var placeholder = (select2_phs[id]) ? select2_phs[id] : "Select one or more options";
                    $("#q" + id).select2({placeholder: placeholder});
                    if (formStatus == 0)
                        $("#q" + id).prop('disabled', true);
                    //console.log("s2:" + select2_ids[i]);
                }
            }

            //
            // Page Previous/Next/Complete/Reopen Buttons
            //

            // Prevent form from submitting for current page
            $('#pagebtn-current').click(function (e) {
                e.preventDefault(e);// do nothing
            });

            // Page buttons
            $('.pagebtn').click(function (e) {
                e.preventDefault(e);
                $('#nextpage').val($(this).attr('gotopage'));
                document.getElementById('custom_form').submit();
            });

            // Re-open button
            $('#reopen').click(function (e) {
                e.preventDefault(e);
                var page = $(this).attr('page');
                $('#nextpage').val($(this).attr('page'));
                $('#status').val(1);
                document.getElementById('custom_form').submit();
            });

            //
            // Note functions
            //

            // Edit Note
            $('.button-note').click(function (e) {
                e.preventDefault(e);
                var qid = $(this).attr('data-qid');

                if ($('#shownote-' + qid).css('display') == 'none') {
                    $('#q' + qid + '-notes-orig').val($('#q' + qid + '-notes').val()); // Update orig note to new saved val
                    $('#shownote-' + qid + '-div').html($('#q' + qid + '-notes').val());
                }

                // If both or hidden then display only the edit
                if ($('#shownote-' + qid).css('display') == 'none' && $('#editnote-' + qid).css('display') == 'none') {
                    $('#editnote-' + qid).show();
                } else {
                    $('#shownote-' + qid).toggle();
                    $('#editnote-' + qid).toggle();
                }
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
                $('#q' + qid + '-gallery').show();
                $('#showmedia-' + qid).toggle();
            });

            $('.mygallery').click(function (e) {
                var imageClicked = document.getElementById(e.target.id);
                openGalleryPreview(imageClicked);
            });

            //
            // Action functions
            //

            // add Action
            $('.button-action').click(function (e) {
                e.preventDefault(e);
                var qid = $(this).attr('data-qid');
                $('#addAction').val(qid);
                document.getElementById('custom_form').submit();
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
                    $('#' + buttons[i].id).removeClass('btn-default red green dark yellow-saffron')
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
                console.log('\nLogic Check')
                console.log(logic);

                var aa = ['1', '2', '3'];
                var str = String('');
                var newa = str.split(',');

                /*console.log('start bug');
                console.log(aa);
                console.log(str);
                console.log(newa);
                console.log('end bug');*/

                for (var i = 0; i < logic.length; i++) {
                    var id = logic[i]['id'];
                    var question_id = logic[i]['question_id'];
                    var match_op = logic[i]['match_operation'];
                    var match_val = logic[i]['match_value'];
                    var trigger = logic[i]['trigger'];
                    var tid = logic[i]['trigger_id'];
                    var qval = String($('#q' + question_id).val());
                    var qval_array = (qval) ? qval.split(',') : [];

                    // Sections
                    if (trigger == 'section') {
                        if (match_op == '=') {
                            if (qval == match_val) {
                                $('#sdiv-' + tid).show();
                                //console.log('show section sdiv-' + tid);
                            } else {
                                $('#sdiv-' + tid).hide();
                                //console.log('hide section sdiv-' + tid);
                            }
                        }

                        // Match item in array
                        if (match_op == '=*') {
                            var match_array = match_val.split(',');
                            $('#sdiv-' + tid).hide();
                            // Loop through response value to determine if trigger is actioned
                            for (var x = 0; x < qval_array.length; x++) {
                                if (match_array.includes(qval_array[x])) {
                                    $('#sdiv-' + tid).show();
                                }
                            }
                        }
                    }

                    // Questions
                    if (trigger == 'question') {
                        //console.log('Trigger Question qid:' + question_id + ' qval:' + qval + ' mval:' + match_val + ' tid:' + tid);
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
                            for (var x = 0; x < qval_array.length; x++) {
                                if (match_array.includes(qval_array[x])) {
                                    $('#qdiv-' + tid).show();
                                }
                            }
                        }
                    }
                }
            }

            performLogic();
        });

        // Force datepicker to not be able to select dates after today
        $('.bs-datetime').datetimepicker({
            endDate: new Date(),
            format: 'dd/mm/yyyy hh:ii',
        });

        function todo(qid) {
            document.getElementById('showAction').value = qid;
            document.getElementById('custom_form').submit();
        }

        //
        // Toggle Sections
        //
        function toggleSection(sid) {
            var section = document.getElementById('sdiv-' + sid + '-content');
            var arrow = document.getElementById('sdiv-' + sid + '-arrow');
            if (section.style.display === 'none') {
                section.style.display = '';
                arrow.classList.remove('fa-angle-right');
                arrow.classList.add('fa-angle-down');
            } else {
                section.style.display = 'none';
                arrow.classList.remove('fa-angle-down');
                arrow.classList.add('fa-angle-right');
            }

        }


    </script>
@stop