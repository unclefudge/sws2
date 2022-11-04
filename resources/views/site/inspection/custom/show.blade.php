@extends('layout-stripdown')

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

<style>
    .hoverFinger:hover {
        cursor: pointer;
    }

    .filepond--root {
        height: 50px !important;
    }

    .button-resp {
        margin-right: 10px;
        width: 25%;
    }
</style>

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
                            <input type="hidden" name="nextpage" id="nextpage" value="{{ $page+1 }}">
                            @include('form-error')

                            <div class="form-body">
                                {{-- Template name + description--}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <h3 style="margin-top: 0px"> {{ $form->template->name }}</h3>
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
                                        <h3 class="font-green-haze" style="display: inline-block; margin: 0px">{{ $form->pageName($page) }}</h3>
                                    <span class="pull-right">
                                       @for ($x = 1; $x <= $form->pages->count(); $x++)
                                            @if ($x == $page)
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
                                @foreach ($form->page($page)->sections as $section)
                                    {{-- Section Title --}}
                                    <div class="row" style="background: #f0f6fa; margin: 10px 0px 5px 0px; padding: 5px 0px;">
                                        <div class="col-md-12">
                                            <table>
                                                <tr>
                                                    <td width="5%"><i class="fa fa-plus font-dark" style="margin-right: 10px"></i></td>
                                                    <td width="95%">
                                                        <h4 class="font-dark">{{ $section->name }}</h4>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>


                                    {{-- Questions --}}
                                    <div style="margin-bottom: 0px">
                                        @foreach ($section->questions as $question)
                                            <?php
                                            $val = null;
                                            $response = $question->response($form->id);
                                            if (count($response))
                                                $val = ($question->multiple) ? $response->pluck('id')->toArray() : $response->first()->value;

                                            //$val = ($response) ? $response->value : null;
                                            ?>
                                            <div class="row" style="padding: 0px 10px">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <?php $required = ($question->required) ? "<small><span class='fa fa-thin fa-asterisk font-red'></span></small>" : '' ?>
                                                        <label for="name" class="control-label">{{ $question->name }} {!! $required  !!}
                                                            <small>T:{{ $question->type }} TS: {{ $question->type_special }} V:{{ $val }}</small>
                                                        </label>

                                                        @switch($question->type)
                                                        @case('text') {{-- Text --}}
                                                        <input type="text" name="q{{$question->id}}" class="form-control" value="{{ $val }}">
                                                        @break

                                                        @case('textarea'){{-- Textarea --}}
                                                        <textarea name="q{{$question->id}}" rows="5" class="form-control" placeholder="Details">{!! $val !!}</textarea>
                                                        @break

                                                        @case('datetime'){{-- Datetime --}}
                                                        <div class="input-group date form_datetime form_datetime bs-datetime" data-date-end-date="0d">
                                                            <?php $val = ($question->id == 2 && $val) ? $val : Carbon\Carbon::now()->format('d/m/Y G:i') ?>
                                                            <input type="text" name="q{{$question->id}}" class="form-control" readonly="" style="background:#FFF" value="{{ $val }}">
                                                            <span class="input-group-addon"><button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button></span>
                                                        </div>
                                                        @break

                                                        @case('media') {{-- Media --}}
                                                        {{--}}<input type="file" class="my-pond" name="{{$question->id}}"/>--}}
                                                        @break

                                                        @case('select') {{-- Select --}}

                                                        {{-- Site --}}
                                                        @if ($question->type_special == 'site')
                                                            <select id="q{{$question->id}}" name="q{{$question->id}}" class="form-control select2" style="width:100%">
                                                                {!! Auth::user()->authSitesSelect2Options('view.site.list', $val) !!}
                                                            </select>
                                                        @endif

                                                        {{-- User --}}
                                                        @if ($question->type_special == 'user')
                                                            {!! Form::select("q$question->id", Auth::user()->company->staffSelect(null, '1'), ($val) ? $val : Auth::user()->id, ['class' => 'form-control select2', 'name' => "q$question->id", 'id' => "q$question->id"]) !!}
                                                        @endif

                                                        {{-- Special Rest--}}
                                                        @if ($question->type_special && !in_array($question->type_special, ['site', 'user']))
                                                            <input type="hidden" id="q{{$question->id}}" name="q{{$question->id}}" value="{{ $val }}">
                                                            {!! customFormSelectButtons($question->id, $val) !!}
                                                        @endif

                                                        {{-- Other Selects--}}
                                                        @if (!$question->type_special)
                                                            @if ($question->multiple)
                                                                {!! Form::select("q$question->id", $question->optionsArray(), explode(',', $val), ['class' => "form-control select2", 'name' => "q$question->id[]", 'id' => "q$question->id",  'multiple']) !!}
                                                            @else
                                                                {!! Form::select("q$question->id", $question->optionsArray(), $val, ['class' => "form-control select2", 'name' => "q$question->id", 'id' => "q$question->id"]) !!}
                                                            @endif
                                                        @endif

                                                        @break


                                                        @default
                                                        @endswitch


                                                    </div>
                                                </div>
                                            </div>
                                            @if (!$loop->last)
                                                <hr class="field-hr">
                                            @endif
                                        @endforeach
                                    </div>
                                @endforeach

                                <br><br>
                                <div class="form-actions right">
                                    @if ($page != 1)
                                        <button class="btn green pagebtn" id="nextpage" page="{{ $page-1 }}">< Previous Page</button>
                                    @endif
                                    @if ($page < $form->pages->count())
                                        <button class="btn green pagebtn" id="nextpage" page="{{ $page+1 }}">Next Page ></button>
                                    @else
                                        <button class="btn blue pagebtn" id="nextpage" page="{{ $page }}">Complete Inspection</button>
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


    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>

    <!-- FilePond -->
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-resize/dist/filepond-plugin-image-resize.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-transform/dist/filepond-plugin-image-transform.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
    {{--}}<script src="https://unpkg.com/jquery-filepond/filepond.jquery.js"></script>--}}
    {{--}}<script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-resize.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-transform.min.js"></script>--}}
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
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
            console.log("s2:"+select2_ids[i]);
        }


        /* $('#nextpage').click(function (e) {
         e.preventDefault(e);
         var name = $(this).attr('name');
         if (name) {
         var id = name.substr(12);
         }
         alert('next:'+ name);
         });*/

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

        // Select Buttons
        $('.button-resp').click(function (e) {
            e.preventDefault(e);
            var qid = $(this).attr('data-qid');
            var rid = $(this).attr('data-rid');
            var btype = $(this).attr('data-btype');
            var bval = $(this).attr('data-bval');
            //alert('q:'+qid+' r:'+rid);

            // Loop through all buttons for selected question + remove active classes
            var buttons = document.querySelectorAll(`[data-qid='${qid}']`);
            for (var i = 0; i < buttons.length; i++) {
                //console.log(buttons[i].id);
                $('#' + buttons[i].id).removeClass('btn-default red dark')
            }

            // Add active class to selected button
            if ($('#q' + qid).val() != bval) {
                $('#q' + qid + '-' + rid).addClass(btype);
                $('#q' + qid).val(rid);
            } else
                $('#q' + qid).val('');

            //console.log(buttons[0].id);
            console.log(buttons);

        });


        /*
         $('.select-special').change(function (e) {
         e.preventDefault(e);
         var id = $(this).attr('id');
         var type = $(this).attr('type');

         if (id) {
         var dataId = '[data-id="q'+id+'"]';
         var selectButton = document.querySelector(dataId);
         $('#pagebtn-current').addClass('font-red')
         console.log(selectButton.textContent);
         }

         alert(id+':'+type);
         console.log(e);
         console.log(e.eventPhase);
         });*/

        $('#custom_form').on('submit', function (e) {
            e.preventDefault(e);
//alert('submit');
//submit_form();
        });


// First register any plugins
//$.fn.filepond.registerPlugin(FilePondPluginImagePreview);
//$.fn.filepond.registerPlugin(FilePondPluginFileValidateSize);
//$.fn.filepond.setDefaults({
//    maxFileSize: '3MB',
//});

        /*
         // Turn input element into a pond
         $('.my-pond').filepond();

         // Set allowMultiple property to true
         $('.my-pond').filepond('allowMultiple', true);

         // Listen for addfile event
         $('.my-pond').on('FilePond:addfile', function (e) {
         console.log('file added event', e);
         });*/

// Manually add a file using the addfile method
//$('.my-pond').first().filepond('addFile', 'index.html').then(function(file){
//    console.log('file added', file);
//});


        updateFields();

        function updateFields() {

        }

    });

    // Force datepicker to not be able to select dates after today
    $('.bs-datetime').datetimepicker({
        endDate: new Date(),
        format: 'dd/mm/yyyy hh:ii',
    });

</script>
@stop