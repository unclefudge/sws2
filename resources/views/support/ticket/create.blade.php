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
                            <span class="caption-subject font-green-haze bold uppercase">Create Support Ticket</span>
                        </div>
                        <div class="actions">
                            <a href="" class="btn btn-circle btn-icon-only btn-default collapse"> </a>
                            <a href="javascript:;" class="btn btn-circle btn-icon-only btn-default fullscreen"> </a>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([\App\Http\Controllers\Support\SupportTicketController::class, 'store']) }}" enctype="multipart/form-data">
                            @csrf
                            @include('form-error')

                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-form.input name="name" label="Ticket Name"/>
                                    </div>
                                    <div class="col-md-2">
                                        @if (Auth::user()->isCC() && Auth::user()->hasPermission2('edit.user.security'))
                                            <x-form.select name="type" label="Type" :options="['0' => 'Support Ticket', '1' => 'Development Upgrade']" value="0"/>
                                        @endif
                                    </div>
                                    <div class="col-md-2 pull-right">
                                        <x-form.select name="priority" label="Priority" :options="['0' => 'None', '1' => 'Low', '2' => 'Medium', '3' => 'High', '4' => 'In Progress']" value="0"/>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="summary" label="Description" rows="8"/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5>Attachments</h5>
                                        <x-form.filepond name="filepond[]" label="Attachments" :multiple="true"/>
                                    </div>
                                </div>

                                <div class="form-actions right">
                                    <a href="/support/ticket" class="btn default"> Back</a>
                                    <button type="submit" class="btn green" id="submit">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


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

