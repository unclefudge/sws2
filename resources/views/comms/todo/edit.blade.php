@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/todo/">Todo</a><i class="fa fa-circle"></i></li>
        <li><span>Edit Todo</span></li>
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
                            <span class="caption-subject font-green-haze bold uppercase"> Todo item</span>
                            <span class="caption-helper"> - ID: {{ $todo->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model($todo, ['method' => 'PATCH', 'action' => ['Comms\TodoController@update', $todo->id], 'files' => true]) !!}
                        @include('form-error')

                        {!! Form::hidden('company_id', Auth::user()->company_id, ['class' => 'form-control', 'id' => 'company_id']) !!}
                        {!! Form::hidden('type_id', $todo->type_id, ['class' => 'form-control', 'id' => 'type_id']) !!}

                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                        {!! Form::label('name', 'Name', ['class' => 'control-label']) !!}
                                        @if (in_array($todo->type, ['incident prevent']) && Auth::user()->hasAnyRole2('whs-manager|mgt-general-manager|web-admin'))
                                            {!! Form::text('name', null, ['class' => 'form-control']) !!}
                                        @else
                                            {!! Form::text('name', null, ['class' => 'form-control', 'readonly']) !!}
                                        @endif
                                        {!! fieldErrorMessage('name', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-3 ">
                                    <div class="form-group {!! fieldHasError('due_at', $errors) !!}">
                                        {!! Form::label('due_at', 'Due Date', ['class' => 'control-label']) !!}
                                        <div class="input-group input-medium date date-picker" data-date-format="dd/mm/yyyy" data-date-start-date="+0d" data-date-reset>
                                            {{--}}<input type="text" class="form-control" readonly style="background:#FFF" id="due_at" name="due_at">--}}
                                            {!! Form::text('due_at', ($todo->due_at) ? $todo->due_at->format('d/m/Y') : '', ['class' => 'form-control form-control-inline',
                                            'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                                            <span class="input-group-btn">
                                                <button class="btn default date-reset" type="button" id="date-reset">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                                <button class="btn default" type="button">
                                                    <i class="fa fa-calendar"></i>
                                                </button>
                                            </span>
                                        </div>
                                        {{--}}
                                        <div class="input-group date date-picker">
                                            {!! Form::text('expiry', ($doc->expiry) ? $doc->expiry->format('d/m/Y') : '', ['class' => 'form-control form-control-inline',
                                            'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                                            <span class="input-group-btn"><button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button></span>
                                        </div> --}}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        {!! Form::label('type', 'Type', ['class' => 'control-label']) !!}
                                        {!! Form::text('type', $todo->type, ['class' => 'form-control', 'readonly']) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('info', $errors) !!}">
                                        {!! Form::label('info', 'Description of what to do', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('info', null, ['rows' => '4', 'class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('info', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- Assigned Users --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('user_list', $errors) !!}">
                                        {!! Form::label('user_list', 'Assigned to', ['class' => 'control-label']) !!}
                                        {!! Form::select('user_list', Auth::user()->company->usersSelect('ALL', 1),
                                             ($todo->assignedTo()) ? $todo->assignedTo()->pluck('id')->toArray() : null, ['class' => 'form-control select2', 'name' => 'user_list[]', 'multiple' => 'multiple', 'width' => '100%']) !!}
                                        {!! fieldErrorMessage('user_list', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions right">
                                @if ($todo->type == 'incident')
                                    <a href="/site/incident/{{ $type_id }}" class="btn default"> Back</a>
                                @else
                                    <a href="/todo" class="btn default"> Back</a>
                                @endif
                                <button type="submit" class="btn green">Submit</button>
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
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script>
    $.ajaxSetup({
        headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
    });

    $(document).ready(function () {
        /* Select2 */
        $("#user_list").select2({
            placeholder: "Select",
            width: '100%',
        });

        $("#date-reset").click(function () {
            $('#due_at').val('');
        })
    });
</script>
@stop

