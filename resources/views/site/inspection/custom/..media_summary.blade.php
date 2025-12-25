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
                        <input type="hidden" name="status" id="status" value="{{ $form->status }}">

                        @include('form-error')

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
                                    <h3 class="font-green-haze" style="display: inline-block; margin: 0px">Media Summary</h3>
                                    <span class="pull-right">
                                        <button class="btn dark" style="margin: 0 5px 5px 0; cursor: default" id="pagebtn-current">Media</button>
                                       @for ($x = 1; $x <= $form->pages()->count(); $x++)
                                            <button class="btn btn-default pagebtn" style="margin: 0 5px 5px 0;" gotopage="{{$x}}">{{ $x }}</button>
                                        @endfor
                                    </span>
                                </div>
                            </div>
                            <hr class="field-hr">

                            {{-- Media Summary --}}
                            @if ($form->files()->count())
                                {{-- Gallery View Options --}}
                                <div class="row">
                                    <div class="col-md-12">

                                        <a href="/site/inspection/{{$form->id}}/media/icon" id="btn-icon" class="btn btn-sm {{ ($view == 'icon') ? 'dark' : 'btn-default' }}" data-view="icon" style="cursor: default">Icon</a>
                                        <a href="/site/inspection/{{$form->id}}/media/list" id="btn-list" class="btn btn-sm {{ ($view == 'list') ? 'dark' : 'btn-default' }}" data-view="list">List</a>
                                        <a href="/site/inspection/{{$form->id}}/media/grid" id="btn-grid" class="btn btn-sm {{ ($view == 'grid') ? 'dark' : 'btn-default' }}" data-view="grid">Grid</a>
                                        <a href="/site/inspection/{{$form->id}}/media/full" id="btn-full" class="btn btn-sm {{ ($view == 'full') ? 'dark' : 'btn-default' }}" data-view="full">Full</a>
                                        {{--}}
                                        <button id="btn-icon" class="btn dark btn-sm galview" data-view="icon" style="cursor: default">Icon</button>
                                        <button id="btn-list" class="btn btn-default btn-sm galview" data-view="list">List</button>
                                        <button id="btn-grid" class="btn btn-default btn-sm galview" data-view="grid">Grid</button>
                                        <button id="btn-full" class="btn btn-default btn-sm galview" data-view="full">Full</button>--}}
                                    </div>
                                </div>
                                <br>
                                {{-- Icon View --}}
                                @if ($view == "icon")
                                    <div id="view_icon">
                                        @foreach ($form->photos()->sortBy('order') as $file)
                                                <?php $rn = rand(); ?>
                                            <img src="{{$file->url}}?v={{$rn}}" class="mygallery" id="q{{$file->question_id}}-photo-{{$file->attachment}}?v={{$rn}}" data-fid="{{$file->id}}" data-attach="{{$file->attachment}}" width="100" style="margin:0px 10px 10px 0px">
                                        @endforeach
                                    </div>
                                @endif
                                {{-- List View --}}
                                @if ($view == "list")
                                    <div id="view_list">
                                        <table style="width: 100%;">
                                            @foreach ($form->photos()->sortBy('order')  as $file)
                                                    <?php $rn = rand(); $did = "q$file->question_id-photo-$file->attachment?v=$rn" ?>
                                                <tr style="padding: 10px 0px 10px 0px; border-bottom: 1px solid #ccc;">
                                                    <td style="width:130px" class="text-center">
                                                        <img src="{{$file->url}}?v={{$rn}}" class="mygallery listImage" id="{{$did}}" data-fid="{{$file->id}}" data-attach="{{$file->attachment}}" height="70" style="margin:10px 10px 10px 0px"></td>
                                                    <td style="padding-top: 10px; vertical-align: top">
                                                        <div id="pname-{{$file->id}}">
                                                            <button type="button" class="btn btn-sm blue pull-right editImage" style="margin-left: 5px" data-fid="{{$file->id}}" data-attach="{{$file->attachment}}">Edit</button>
                                                            {{ $file->question->name }}
                                                        </div>
                                                        <div id="pedit-{{$file->id}}" style="display: none">
                                                            <div class="btn-group" role="group">
                                                                <button class="btn btn-sm default rotate-right" style="margin-right: 3px;" data-id="{{$did}}" data-fid="{{$file->id}}" data-attach="{{$file->attachment}}"><i
                                                                            class="fa fa-rotate-right"></i></button>
                                                                <button class="btn btn-sm default rotate-left" data-id="{{$did}}" data-fid="{{$file->id}}" data-attach="{{$file->attachment}}"><i class="fa fa-rotate-left"></i></button>
                                                            </div>
                                                            <button type="button" class="btn btn-sm blue saveImage" data-fid="{{$file->id}}" data-attach="{{$file->attachment}}">Save</button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                @endif
                                {{-- Grid View --}}
                                @if ($view == "grid")
                                    <div id="view_grid">
                                        <table style="width: 100%;">
                                            @for ($i=1; $i <= $form->photos()->count(); $i++)
                                                    <?php
                                                    if ($i > $form->photos()->count()) break;
                                                    $p1 = \App\Models\Misc\Form\FormFile::where('form_id', $form->id)->where('order', $i)->first();
                                                    $p1_img = ($p1) ? "<img src='$p1->attachment?v=" . rand() . "' width='100%'>" : '';
                                                    $p1_txt = ($p1) ? $p1->question->name : '';
                                                    $p2 = \App\Models\Misc\Form\FormFile::where('form_id', $form->id)->where('order', $i + 1)->first();
                                                    $p2_img = ($p2) ? "<img src='$p2->attachment?v=" . rand() . "' width='100%'>" : '';
                                                    $p2_txt = ($p2) ? $p2->question->name : '';
                                                    ?>
                                                <tr>
                                                    <td style="width=45%; vertical-align: bottom; background-color: #fafafa">{!! $p1_img !!}</td>
                                                    <td style="width=10%">&nbsp;</td>
                                                    <td style="width=45%; vertical-align: bottom; {{ ($p2_img) ? 'background-color: #fafafa' : '' }}">{!! $p2_img !!}</td>
                                                </tr>
                                                <tr>
                                                    <td style="color: #fff; padding:5px 10px; vertical-align: top; background-color: #222; ">{!! $p1_txt !!}</td>
                                                    <td>&nbsp;</td>
                                                    <td style="color: #fff; padding:5px 10px; vertical-align: top; {{ ($p2_img) ? 'background-color: #222' : '' }}">{!! $p2_txt !!}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3">&nbsp;</td>
                                                </tr>
                                                    <?php $i++ ?>
                                            @endfor
                                        </table>
                                    </div>
                                @endif
                                {{-- Full View --}}
                                @if ($view == "full")
                                    <div id="view_full">
                                        @foreach ($form->photos() as $file)
                                            @if ($file->type == 'image')
                                                <div><img src="{{$file->url}}?v={{rand()}}" id="q{{$file->question_id}}-photo-{{$file->attachment}}" width="100%"></div>
                                                <div style="background: #222; color: #fff; padding:5px 10px; vertical-align: top; margin-bottom: 20px ">{{ $file->question->name }}</div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                                @if ($form->docs()->count())
                                    <div><b style="font-size: 18px">Files</b></div>
                                    {{-- Files --}}
                                    <div id="file_gallery" style="margin-bottom: 20px">
                                        @foreach ($form->files() as $file)
                                            @if ($file->type == 'file')
                                                <div id="q{{$file->question_id}}-file-{{$file->id}}">
                                                    <i class="fa fa-file-text-o"></i> &nbsp; <a href="{{$file->url}}" target="_blank">{{ $file->name }}</a>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            @else
                                No media found
                            @endif


                            <br><br>
                            <div class="form-actions right">
                                @if (!$form->status)
                                    {{--}}<button class="btn green pagebtn" id="save" gotopage="{{ $pagenumber }}">Save</button>
                                    <button class="btn red" id="reopen" gotopage="{{ $pagenumber }}">Re-open Inspection</button>--}}
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
        <a id="downloadGallery" href="javascript:void(0)" class="mygallery-download" onclick="downloadGalleryPreview()"><i class="fa fa-download"></i></a>
        @if ($form->status)
            <a id="deleteGallery" href="javascript:void(0)" class="mygallery-delete" onclick="deleteGalleryPreview()"><i class="fa fa-trash"></i></a>
        @endif
        <div class="mygallery-overlay-content">
            <img class="img-fluid" id="myGalleryImage" src="">
        </div>
    </div>

@stop

@section('page-level-plugins-head')
    {{-- Filepond --}}
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css"/>
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">

    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
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
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <!--<script src="https://unpkg.com/filepond-plugin-image-transform/dist/filepond-plugin-image-transform.js"></script>-->
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script src="/js/site-inspection-filepond.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            const formStatus = {{ $form->status }};

            // Gallet view options
            $('.galview').click(function (e) {
                e.preventDefault(e);
                var view = $(this).attr('data-view');
                $('#view_icon').hide();
                $('#view_list').hide();
                $('#view_grid').hide();
                $('#view_full').hide();
                $('#view_' + view).show();

                $('#btn-icon').removeClass("dark");
                $('#btn-list').removeClass("dark");
                $('#btn-grid').removeClass("dark");
                $('#btn-full').removeClass("dark");

                $('#btn-' + view).addClass("dark");
                $('#btn-icon').addClass("btn-default");
                $('#btn-list').addClass("btn-default");
                $('#btn-grid').addClass("btn-default");
                $('#btn-full').addClass("btn-default");
            });

            // Rotate images
            $('.rotate-right').click(function (e) {
                e.preventDefault(e);
                var id = $(this).attr('data-id');
                var fid = $(this).attr('data-fid');
                var file = $(this).attr('data-attach');
                rotateImage(id, fid, 'clockwise');
            });

            $('.rotate-left').click(function (e) {
                e.preventDefault(e);
                var id = $(this).attr('data-id');
                var fid = $(this).attr('data-fid');
                var file = $(this).attr('data-attach');
                rotateImage(id, fid, 'counterclockwise');
            });

            $('.editImage').click(function (e) {
                e.preventDefault(e);
                var fid = $(this).attr('data-fid');
                $('#pname-' + fid).hide();
                $('#pedit-' + fid).show();

            });

            $('.saveImage').click(function (e) {
                e.preventDefault(e);
                var fid = $(this).attr('data-fid');
                var rotate = imageCurrentRotation[fid];
                //alert('save:' + fid + " rot:" + rotate);
                $.ajax({
                    url: '/form/media/' + fid + '/rotate/' + rotate,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        console.log(data);
                    },
                });
                $('#pname-' + fid).show();
                $('#pedit-' + fid).hide();

            });


            $('#deleteGallery').click(function (e) {
                e.preventDefault(e);
                // url = /filebank/form/{id}/filename.jpg?v=1234
                var image = document.getElementById("myGalleryImage");
                var host = window.location.protocol + "//" + window.location.host;
                var file_url = image.src.split(host)[1];
                var form_id = file_url.split('/filebank/inspection/')[1].split('/')[0]; // get only the filename ie strip out '/filebank/form/'
                var attach = file_url.split('?v=')[0]; // get only the filename ie strip out '/filebank/form/{id}/'
                var file = file_url.split('/filebank/inspection/')[1].split('/')[1].split('?v=')[0]; // get only the filename ie strip out '/filebank/form/{id}/'
                var qid = file.split('-')[0];
                var fid = imageAttachment[attach];
                //alert(attach);
                //alert(fid);
                $.ajax({
                    url: '/form/media/' + fid + '/delete/',
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        console.log(data);
                    },
                });
            });


            // Delete Files
            $('.deleteFile').click(function (e) {
                e.preventDefault(e);
                var fid = $(this).attr('data-fid');
                var file = $(this).attr('data-file');
                $('#' + fid).hide();

                // Create new input element with name of file to delete and add to DOM
                var input = document.createElement("input");
                input.type = "text";
                input.name = "myGalleryDelete[]";
                input.value = file;
                input.style.display = 'none';
                document.getElementById('custom_form').appendChild(input); // put it into the DOM
            });

            //
            // Media functions
            //
            $('.mygallery').click(function (e) {
                var imageClicked = document.getElementById(e.target.id);
                openGalleryPreview(imageClicked);
            });

            const imageCurrentRotation = {};
            $(".listImage").each(function (index, element) {
                var fid = $(this).attr('data-fid');
                imageCurrentRotation[fid] = 0;
            });

            const imageAttachment = {};
            $(".mygallery").each(function (index, element) {
                var fid = $(this).attr('data-fid');
                var attach = $(this).attr('data-attach');
                //console.log(attach + " > " + fid);
                imageAttachment[attach] = fid;
            });

            // FUNCTION TO ROTATE IMAGE USING CSS
            function rotateImage(id, fid, direction) {
                var clickedImage = document.getElementById(id);
                //console.log("rotate " + direction);
                rotation = imageCurrentRotation[fid];
                if (direction == 'clockwise') {
                    // ENSURE ANG RANGE OF 0 TO 359 WITH "%" OPERATOR
                    rotation = (rotation + 90) % 360;
                    imageCurrentRotation[fid] = rotation;
                    clickedImage.style.transform = `rotate(${rotation}deg)`;
                } else if (direction == 'counterclockwise') {
                    rotation = (rotation - 90) % 360;
                    imageCurrentRotation[fid] = rotation;
                    clickedImage.style.transform = `rotate(${rotation}deg)`;
                }
                //displayCurrentRotations();
            }

            function displayCurrentRotations() {
                for (var key in imageCurrentRotation) {
                    console.log(key + ": " + imageCurrentRotation[key]);
                }
            };

            displayCurrentRotations();
        })
        ;

        // Force datepicker to not be able to select dates after today
        $('.bs-datetime').datetimepicker({
            endDate: new Date(),
            format: 'dd/mm/yyyy hh:ii',
        });

        $('.date-picker').datepicker({
            autoclose: true,
            clearBtn: true,
            format: 'dd/mm/yyyy',
        });
    </script>
@stop