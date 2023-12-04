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
                            <span class="caption-subject font-green-haze bold uppercase">Site Note</span>
                            <span class="caption-helper">ID: {{ $note->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="form-body">
                            <div class="row">
                                {{-- Site --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('site_id', 'Site', ['class' => 'control-label']) !!}
                                        {!! Form::text('site_id', $note->site->name, ['class' => 'form-control', 'readonly', 'id' => 'site_id']) !!}
                                    </div>
                                </div>
                                {{-- Category --}}
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('category_id', 'Category', ['class' => 'control-label']) !!}
                                        {!! Form::text('category_id', ($note->category_id) ? $note->category->name : 'none', ['class' => 'form-control', 'readonly', 'id' => 'category_id']) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- Variation fields --}}
                            @if ($note->category_id == '16')
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            {!! Form::label('variation_name', 'Variation Name', ['class' => 'control-label']) !!}
                                            {!! Form::text('variation_name', $note->variation_name, ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="form-group">
                                            {!! Form::label('variation_info', 'Variation Description', ['class' => 'control-label']) !!}
                                            {!! Form::text('variation_info', $note->variation_info, ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            {!! Form::label('variation_cost', 'Gross Cost (incl GST + 20% margin)', ['class' => 'control-label']) !!}
                                            {!! Form::text('variation_cost', $note->variation_cost, ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            {!! Form::label('variation_days', 'Total Extension Days (discussed with Client) Description', ['class' => 'control-label', 'readonly']) !!}
                                            {!! Form::text('variation_days', $note->variation_days, ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Notes --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <b>Notes:</b><br>
                                    {!! nl2br($note->notes) !!}
                                </div>
                            </div>
                        </div>

                        {{-- Attachments --}}
                        <h5>Attachments:</h5>
                        @if ($note->attachments()->count())
                            <hr style="margin: 10px 0px; padding: 0px;">
                            {{-- Image attachments --}}
                            <div class="row" style="margin: 0">
                                @foreach ($note->attachments() as $attachment)
                                    @if ($attachment->type == 'image' && file_exists(public_path($attachment->url)))
                                        <div style="width: 60px; float: left; padding-right: 5px">
                                            <a href="{{ $attachment->url }}" target="_blank" class="html5lightbox"
                                               title="{{ $attachment->name }}" data-lity>
                                                <img src="{{ $attachment->url }}"
                                                     class="thumbnail img-responsive img-thumbnail"></a>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            {{-- File attachments  --}}
                            <div class="row" style="margin: 0">
                                @foreach ($note->attachments() as $attachment)
                                    @if ($attachment->type == 'file' && file_exists(public_path($attachment->url)))
                                        <i class="fa fa-file-text-o"></i> &nbsp; <a href="{{ $attachment->url }}"
                                                                                    target="_blank"> {{ $attachment->name }}</a>
                                        <br>
                                    @endif
                                @endforeach
                            </div>
                            <br>
                        @else
                            None
                        @endif

                        {{-- Attachments --}}
                        {{--}}
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Upload Attachments</h5>
                                <input type="file" class="filepond" name="filepond[]" multiple/><br><br>
                            </div>
                        </div>--}}


                        <br><br>
                        <div class="form-actions right">
                            <a href="{!! url()->previous() !!}" class="btn default"> Back</a>
                            {{--}}<button type="submit" class="btn green"> Save</button>--}}
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
        });
    </script>
@stop

