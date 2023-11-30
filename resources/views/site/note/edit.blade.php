@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/{{$note->site_id}}/notes">Site Notes</a><i class="fa fa-circle"></i></li>
        <li><span>View</span></li>
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
        });
    </script>
@stop

