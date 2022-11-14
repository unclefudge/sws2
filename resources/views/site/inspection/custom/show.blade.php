@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/inspection/list/{{ $form->template->id }}">{{ $form->template->name }}</a><i class="fa fa-circle"></i></li>
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
                            @foreach ($form->page($pagenumber)->sections as $section)
                                <div id="sdiv-{{$section->id}}">
                                    {{-- Section Title --}}
                                    @if ($section->name)
                                        <div class="row" style="background: #f0f6fa; margin: 10px 0px 5px 0px; padding: 5px 0px; cursor: pointer" onclick="toggleSection({{$section->id}})">
                                            <div class="col-md-12">
                                                <h4 class="font-dark"><small><i id="sdiv-{{$section->id}}-arrow" class="fa fa-angle-down font-dark" style="margin-right: 10px"></i></small> {{ $section->name }}
                                                </h4>
                                            </div>
                                        </div>
                                    @endif

                                    <div id="sdiv-{{$section->id}}-content">
                                        {{-- Questions --}}
                                        <div style="margin-bottom: 0px">
                                            @foreach ($section->questions as $question)
                                                <?php
                                                $val = null;
                                                $qLogic = (count($question->logic)) ? 'data-logic="true"' : '';
                                                $response = $question->response($form->id);
                                                if (count($response))
                                                    $val = ($question->multiple) ? $response->pluck('value')->toArray() : $response->first()->value;

                                                // Highlight required fields if form marked 'complete'
                                                $highlight_required = false;
                                                if ($showrequired && $question->required) {
                                                    if ($question->type == 'media')
                                                        $highlight_required = ($question->files($form->id)->count()) ? false : true; // check for media
                                                    elseif ($question->multiple)
                                                        $highlight_required = ($val) ? false : true;  // non empty array for multi select/button response
                                                    else
                                                        $highlight_required = ($val || $val == '0') ? false : true; // val not blank/null for text/textarea response
                                                }
                                                ?>


                                                <div id="qdiv-{{$question->id}}">
                                                    @if ($form->status)
                                                        {{-- Active Form - allow edit --}}
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <?php $required = ($question->required) ? "<small><span class='fa fa-thin fa-asterisk font-red' style='opacity: 0.7'></span></small>" : '' ?>
                                                                    <label for="name" class="control-label {{ ($highlight_required) ? 'font-red' : ''}}" style="font-size: 18px">{{ $question->name }} {!! $required  !!}
                                                                        {{--}}<small>T:{{ $question->type }} TS: {{ $question->type_special }} V:{!! (is_array($val)) ? print_r(implode(',', $val)) : $val !!}</small>--}}
                                                                    </label>

                                                                    @switch($question->type)
                                                                    @case('text') {{-- Text --}}
                                                                    <input type="text" name="q{{$question->id}}" class="form-control" value="{{ $val }}" {{ (!$form->status) ? "disabled" : '' }}>
                                                                    @break

                                                                    @case('textarea'){{-- Textarea --}}
                                                                    <textarea name="q{{$question->id}}" rows="5" class="form-control" placeholder="Details" {{ (!$form->status) ? "disabled" : '' }}>{!! $val !!}</textarea>
                                                                    @break

                                                                    @case('datetime'){{-- Datetime --}}
                                                                    @if ($form->status)
                                                                        <div class="input-group date form_datetime form_datetime bs-datetime" data-date-end-date="0d" style="width: 300px">
                                                                            <?php $val = ($question->id == 2 && $val) ? $val : Carbon\Carbon::now()->format('d/m/Y G:i') ?>
                                                                            <input type="text" name="q{{$question->id}}" class="form-control" style="background:#FFF" value="{{ $val }}">
                                                                            <span class="input-group-addon"><button class="btn default date-set" type="button" style="height: 34px;"><i class="fa fa-calendar"></i></button></span>
                                                                        </div>
                                                                    @else
                                                                        <input type="text" name="q{{$question->id}}" class="form-control" value="{{ $val }}" style="width:300px" disabled>
                                                                    @endif
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
                                                                        {!! customFormSelectButtons($question->id, $val, $form->status) !!}
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
                                                    @else
                                                        {{-- Completed Form - display only text response --}}
                                                        <div class="row" style="margin-bottom: 20px">
                                                            <div class="col-md-12">
                                                                <span class="" style="font-size:18px"><b>{{ $question->name }}</b></span><br>
                                                                {!! nl2br($question->responseFormatted($form->id)) !!}
                                                                @if (is_array($val))
                                                                    @foreach ($val as $v)
                                                                        <input type="hidden" id="q{{$question->id}}[]" name="q{{$question->id}}[]" value="{{ $v }}" disabled>
                                                                    @endforeach
                                                                @else
                                                                    <input type="hidden" id="q{{$question->id}}" name="q{{$question->id}}" value="{{ $val }}" disabled>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif

                                                    {{-- Gallery --}}
                                                    <div id="showmedia-{{$question->id}}" style="width: 400px; {{ ($question->type != 'media' || !$form->status) ? 'display:none' : '' }}">
                                                        <input type="file" class="my-filepond" name="q{{$question->id}}-media[]" data-qid="{{$question->id}}" multiple/>
                                                    </div>
                                                    <div id="q{{$question->id}}-gallery" style="margin-bottom: 20px">
                                                        @if ($question->files($form->id)->count())
                                                            {{--}}<div style="margin-bottom: 10px">Media:</div>--}}
                                                            @foreach ($question->files($form->id) as $file)
                                                                <img src="{{$file->attachment}}" class="mygallery" id="q{{$question->id}}-photo-{{$file->attachment}}" width="100" style="margin-right: 20px">
                                                            @endforeach
                                                        @endif
                                                    </div>


                                                    {{-- Notes - Show --}}
                                                    <input type="hidden" id="q{{$question->id}}-notes-orig" value="{!! ($question->extraNotesForm($form->id)) ? $question->extraNotesForm($form->id)->notes : '' !!}">
                                                    <div id="shownote-{{$question->id}}" class="row hoverdiv button-note" data-qid="{{$question->id}}" style="margin: 10px 0px; {{ ($question->extraNotesForm($form->id)) ? '' : 'display:none' }}">
                                                        <div class="col-md-12" id="shownote-{{$question->id}}-div" style="padding-left: 0px; margin-bottom: 10px">
                                                            <b>Notes</b><br>
                                                            {!! ($question->extraNotesForm($form->id)) ? $question->extraNotesForm($form->id)->notes : '' !!}
                                                        </div>
                                                    </div>
                                                    {{-- Notes - Edit --}}
                                                    <div id="editnote-{{$question->id}}" style="margin-top:10px; display:none">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <textarea id="q{{$question->id}}-notes" name="q{{$question->id}}-notes" rows="5" class="form-control" placeholder="Notes">{!! ($question->extraNotesForm($form->id)) ? $question->extraNotesForm($form->id)->notes : '' !!}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="row" style="margin: 10px 0px">
                                                            <div class="col-md-12">
                                                                <button class="btn green button-savenote" data-qid="{{$question->id}}">Save</button>
                                                                <button class="btn default button-cancelnote" data-qid="{{$question->id}}">Cancel</button>
                                                                <button class="btn dark button-delnote" data-qid="{{$question->id}}">&nbsp;<i class="fa fa-trash"></i>&nbsp;</button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Actions - Show --}}
                                                    @if ($question->actions($form->id)->count())
                                                        <div class="row">
                                                            <div class="col-md-12"><b>Actions</b><br></div>
                                                        </div>

                                                        @foreach($question->actions($form->id) as $todo)
                                                            <div class="row hoverDiv" style="margin: 10px 0px; padding: 10px 0px; background-color: #f9f9f9; border: #ddd 1px solid" onclick="todo({{$todo->id}})">
                                                                <div class="col-xs-8">
                                                                    {!! nl2br($todo->info) !!}<br><br>
                                                                    <b>Assigned to:</b> {{ $todo->assignedToBySBC() }}
                                                                    @if ($todo->comments)
                                                                        <br><b>Comments:</b> {!! nl2br($todo->comments) !!}
                                                                    @endif
                                                                    @if ($todo->attachment) <br><a href="{{ $todo->attachmentUrl }}" data-lity class="btn btn-xs blue"><i class="fa fa-picture-o"> Attachment</i></a> @endif
                                                                </div>
                                                                <div class="col-xs-4 text-right">
                                                                    <?php
                                                                    $done_by = App\User::find($todo->done_by);
                                                                    $done_at = ($done_by) ? $todo->done_at->format('d/m/Y') : '';
                                                                    $done_by = ($done_by) ? $done_by->full_name : 'unknown';
                                                                    ?>
                                                                    @if ($todo->status == '1' && !$todo->done_by)
                                                                        <span class="font-red">Outstanding</span>{!! ($todo->due_at) ? "<br>Due: " . $todo->due_at->format('d/m/Y') : '' !!}
                                                                    @elseif ($todo->status == '2' && !$todo->done_by)
                                                                        <span class="font-yellow">In Progress</span>{!! ($todo->due_at) ? "<br>Due: " . $todo->due_at->format('d/m/Y') : '' !!}
                                                                    @elseif ($todo->status == '-1' && !$todo->done_by)
                                                                        <span class="font-red">Can't do</span>{!! ($todo->due_at) ? "<br>Due: " . $todo->due_at->format('d/m/Y') : '' !!}
                                                                    @else
                                                                        <span class="font-green">Completed</span><br>{!! $done_by  !!} ({{ $done_at }})
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif

                                                    {{-- Question Extras (Notes, Media, Actions --}}
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <button class="btn default btn-xs pull-right button-action" data-qid="{{$question->id}}">Action <i class="fa fa-check-square-o"></i></button>
                                                            @if ($question->type != 'media' || !$form->status)
                                                                <button class="btn default btn-xs pull-right button-media" style="margin-right: 10px" data-qid="{{$question->id}}">Media <i class="fa fa-picture-o"></i></button>
                                                            @endif
                                                            <button class="btn default btn-xs pull-right button-note" style="margin-right: 10px" data-qid="{{$question->id}}">Note <i class="fa fa-edit"></i></button>
                                                        </div>
                                                    </div>
                                                    <hr class="field-hr">
                                                </div> {{-- end question div --}}
                                            @endforeach
                                        </div>
                                    </div> {{-- end section-content div --}}
                                </div> {{-- end section div --}}
                            @endforeach

                            <br><br>
                            <div class="form-actions right">
                                @if ($pagenumber != 1)
                                    <button class="btn blue pagebtn" id="prevpage" gotopage="{{ $pagenumber-1 }}">< Previous Page</button>
                                @endif
                                @if ($pagenumber < $form->pages->count())
                                    <button class="btn blue pagebtn" id="nextpage" gotopage="{{ $pagenumber+1 }}">Next Page ></button>
                                @endif
                                @if ($form->status && ($pagenumber == $form->pages->count() || $showrequired))
                                    <button class="btn green pagebtn" id="complete" gotopage="complete">Complete Inspection</button>
                                @endif
                                @if (!$form->status)
                                    <button class="btn green pagebtn" id="save" gotopage="{{ $pagenumber }}">Save</button>
                                    <button class="btn red" id="reopen" gotopage="{{ $pagenumber }}">Re-open Inspection</button>
                                @endif
                            </div>
                        </div>
                        {!! Form::close() !!}
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

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
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

            $('#shownote-' + qid).toggle();
            $('#editnote-' + qid).toggle();
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

                console.log("id:" + id + " q:" + question_id + " m:" + match_op + " mval:" + match_val + " t:" + trigger + ' tid:' + tid + ' qval:' + qval);

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
        var section = document.getElementById('sdiv-'+sid+'-content');
        var arrow = document.getElementById('sdiv-'+sid+'-arrow');
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