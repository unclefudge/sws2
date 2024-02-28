@extends('layout')

@section('pagetitle')
    <div class="page-title">
        <h1><i class="fa fa-ticket"></i> Support Tickets</h1>
    </div>
@stop
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/support/ticket">Support Tickets</a><i class="fa fa-circle"></i></li>
        <li><span>Create ticket</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-pencil "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Create Support Ticket</span>
                        </div>
                        <div class="actions">
                            <a href="" class="btn btn-circle btn-icon-only btn-default collapse"> </a>
                            <a href="javascript:;" class="btn btn-circle btn-icon-only btn-default fullscreen"> </a>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('support_ticket', ['action' => ['Support\SupportTicketController@store'], 'files' => true]) !!}
                        @include('form-error')

                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                        {!! Form::label('name', 'Ticket Name', ['class' => 'control-label']) !!}
                                        {!! Form::text('name', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('name', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    @if (Auth::user()->isCC() && Auth::user()->hasPermission2('edit.user.security'))
                                        <div class="form-group {!! fieldHasError('type', $errors) !!}">
                                            {!! Form::label('type', 'Type', ['class' => 'control-label']) !!}
                                            {!! Form::select('type', ['0' => 'Support Ticket', '1' => 'Development Upgrade'],
                                                 '0', ['class' => 'form-control bs-select']) !!}
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-2 pull-right">
                                    <div class="form-group {!! fieldHasError('priority', $errors) !!}">
                                        {!! Form::label('priority', 'Priority', ['class' => 'control-label']) !!}
                                        {!! Form::select('priority', ['0' => 'None', '1' => 'Low', '2' => 'Medium', '3' =>'High', '4' =>'In Progress'],
                                             '0', ['class' => 'form-control bs-select']) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('summary', $errors) !!}">
                                        {!! Form::label('summary', 'Description', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('summary', null, ['rows' => '8', 'class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('summary', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <h5>Attachments</h5>
                                    <input type="file" class="filepond" name="filepond[]" multiple/>
                                </div>
                            </div>

                            <div class="form-actions right">
                                <a href="/support/ticket" class="btn default"> Back</a>
                                <button type="submit" class="btn green" id="submit">Submit</button>
                            </div>
                        </div> <!--/form-body-->
                        {!! Form::close() !!}
                        <!-- END FORM-->
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css"/>   {{-- Filepond --}}
    {{--}}<link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>--}}
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/js/filepond-basic.js" type="text/javascript"></script>
    <script>
    </script>
@stop

