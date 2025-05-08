@php($notes_label = 'Note')
@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/note">Site Notes</a><i class="fa fa-circle"></i></li>
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

                            {{-- Costing fields --}}
                            @if ($note->category_id == '15')
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            {!! Form::label('costing_extra_credit', 'Credit / Extra', ['class' => 'control-label']) !!}
                                            {!! Form::text('costing_extra_credit', $note->costing_extra_credit, ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            {!! Form::label('costing_item', 'New item / In Lieu of', ['class' => 'control-label']) !!}
                                            {!! Form::text('costing_item', $note->costing_item, ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            {!! Form::label('costing_priority', 'Priority', ['class' => 'control-label']) !!}
                                            {!! Form::text('costing_priority', $note->costing_priority, ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            {!! Form::label('costing_room', 'Room', ['class' => 'control-label']) !!}
                                            {!! Form::text('costing_room', $note->costing_room, ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            {!! Form::label('costing_location', 'Location', ['class' => 'control-label', 'readonly']) !!}
                                            {!! Form::text('costing_location', $note->costing_location, ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                    </div>
                                </div>
                                @php($notes_label = 'Description')
                            @endif

                            {{-- 16. Approved Variation, 19. For Issue to Client,  20. TBA Site Variations, 93. Wet Calls  --}}
                            @if (in_array($note->category_id, [16, 19, 20, 93]))
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('variation_name', 'Variation Name', ['class' => 'control-label']) !!}
                                            {!! Form::text('variation_name', $note->variation_name, ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('variation_name', 'Variation Description', ['class' => 'control-label']) !!}
                                            {{--}}{!! Form::text('variation_info', $note->variation_info, ['class' => 'form-control', 'readonly']) !!}--}}
                                            {!! Form::textarea('variation_info', $note->variation_info, ['rows' => 5, 'class' => 'form-control', 'readonly']) !!}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- 16. + 19. additional fields --}}
                            @if (in_array($note->category_id, [16, 19, 93]))
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="variation_net" class="control-label">Net Cost <span class="font-grey-silver">(Admin use only)</span> </label>
                                            {!! Form::text('variation_net',  $note->variation_net, ['class' => 'form-control', 'readonly']) !!}
                                            {!! fieldErrorMessage('variation_net', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            {!! Form::label('variation_cost', 'Gross Cost (incl GST + 20% margin)', ['class' => 'control-label']) !!}
                                            {!! Form::text('variation_cost', $note->variation_cost, ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                    </div>
                                    @if (in_array($note->category_id, [16, 19]))
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                {!! Form::label('costing_extra_credit', 'Credit / Extra', ['class' => 'control-label']) !!}
                                                {!! Form::text('costing_extra_credit', $note->costing_extra_credit, ['class' => 'form-control', 'readonly']) !!}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="variation_days" class="control-label">Total Extension Days (discussed with Client) Description <span class="font-grey-silver">(Admin use only)</span> </label>
                                            {!! Form::text('variation_days', $note->variation_days, ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12"><b>Cost Centres & Item Details:</b></div>
                                </div>
                                {{-- Cost centre & Details --}}
                                @foreach ($note->costs as $cost)
                                    <div class="row">
                                        <div class="col-md-3">{{$cost->category->name}}</div>
                                        <div class="col-md-9">{{$cost->details}}</div>
                                    </div>
                                @endforeach
                                <br>
                                @php($notes_label = 'Note (Admin use only)')
                            @endif

                            {{--  Prac Completion Fields --}}
                            @if (in_array($note->category_id, ['89']))
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="prac_notified" class="control-label"> Prac Notified
                                                <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                                   data-content="Date you will be delivering letters. Please make sure you have given 7 working days notice"> <i class="fa fa-question-circle font-grey-silver"></i>
                                                </a>
                                            </label>
                                            {!! Form::text('prac_notified', ($note->prac_notified) ? $note->prac_notified->format('d/m/Y') : '', ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="prac_meeting_date" class="control-label"> Prac Meeting Date
                                                <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                                   data-content="Date you will be holding the Prac Meeting with the Client."> <i class="fa fa-question-circle font-grey-silver"></i>
                                                </a>
                                            </label>
                                            {!! Form::text('prac_notified', ($note->prac_meeting) ? $note->prac_meeting->format('d/m/Y') : '', ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="prac_meeting_time" class="control-label"> Prac Meeting Time
                                                <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                                   data-content="Time you will be holding the Prac Meeting with the Client."> <i class="fa fa-question-circle font-grey-silver"></i>
                                                </a>
                                            </label>
                                            {!! Form::text('prac_notified', ($note->prac_meeting) ? $note->prac_meeting->format('h:i A') : '', ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Early Occupation Fields --}}
                            @if (in_array($note->category_id, ['94']))
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('occupation_date', $errors) !!}">
                                            <label for="occupation_date" class="control-label"> Date of Occupancy
                                                <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                                   data-content="Date client took occupancy"> <i class="fa fa-question-circle font-grey-silver"></i>
                                                </a>
                                            </label>
                                            {!! Form::text('occupation_date', ($note->occupation_date) ? $note->occupation_date->format('d/m/Y') : '', ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        {!! Form::label('occupation_area', 'Areas Client has taken Occupation of', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('occupation_area', $note->occupation_area, ['class' => 'form-control', 'readonly']) !!}
                                        <br><br>
                                    </div>
                                </div>
                            @endif

                            {{-- Response Required --}}
                            @if (in_array($note->category_id, ['12, 13, 14']))
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            {!! Form::label('response_req', 'Response Required', ['class' => 'control-label']) !!}
                                            {!! Form::text('response_req', ($note->response_req) ? 'Yes' : 'No - FYI only', ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Notes --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <b>{{ $notes_label }}:</b><br>
                                    {!! nl2br($note->notes) !!}
                                </div>
                            </div>
                        </div>

                        {{-- Attachments --}}
                        <h5><b>Attachments:</b></h5>
                        @if ($note->attachments()->count())
                            <hr style="margin: 10px 0px; padding: 0px;">
                            {{-- Image attachments --}}
                            <div class="row" style="margin: 0">
                                @foreach ($note->attachments() as $attachment)
                                    @if ($attachment->type == 'image' && file_exists(public_path($attachment->url)))
                                        <div style="width: 60px; float: left; padding-right: 5px">
                                            <a href="{{ $attachment->url }}" target="_blank" class="html5lightbox" title="{{ $attachment->name }}" data-lity>
                                                <img src="{{ $attachment->url }}" class="thumbnail img-responsive img-thumbnail"></a>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            {{-- File attachments  --}}
                            <div class="row" style="margin: 0">
                                @foreach ($note->attachments() as $attachment)
                                    @if ($attachment->type == 'file' && file_exists(public_path($attachment->url)))
                                        <i class="fa fa-file-text-o"></i> &nbsp; <a href="{{ $attachment->url }}" target="_blank"> {{ $attachment->name }}</a>
                                        <br>
                                    @endif
                                @endforeach
                            </div>
                            <br>
                        @else
                            None
                        @endif


                        {{-- Reply --}}
                        @if ($note->extraNotes->count())
                            <h5><b>Replies:</b></h5>
                            <hr style="margin: 10px 0px; padding: 0px;">
                            <table class="table table-striped table-bordered">
                                <tr class="mytable-header text-bold">
                                    <td>Date</td>
                                    <td>Reply</td>
                                </tr>
                                @foreach ($note->extraNotes->sortByDesc('created_at') as $extra)
                                    <tr>
                                        <td>{{$extra->created_at->format('d/m/Y')}}</td>
                                        <td>{!! nl2br($extra->notes) !!}</td>
                                    </tr>
                                @endforeach
                            </table>
                        @endif


                        <br><br>
                        <div class="form-actions right">
                            <a href="{!! url()->previous() !!}" class="btn default"> Back</a>
                            @if (in_array($note->category_id, [19,20]))
                                <a href="/site/{{$note->id}}/notes/convert" class="btn green"> Copy & Create To Approved Site Variation </a>
                            @endif
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
        });
    </script>
@stop

