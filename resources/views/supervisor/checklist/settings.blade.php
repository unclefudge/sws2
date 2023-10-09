@extends('layout')
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/supervisor/checklist">Supervisor Checklist</a><i class="fa fa-circle"></i></li>
        <li>Settings</li>
    </ul>
@stop

@section('content')

    <div class="page-content-inner">
        {{-- Reports --}}
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze">Weekly Supervisor Checklist Settings</span>
                        </div>
                        <div class="actions">
                            <a class="btn btn-circle green btn-outline btn-sm" href="/supervisor/checklist" data-original-title="Current">Current Week</a>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        {!! Form::model('SuperChecklistSettings', ['method' => 'POST', 'action' => ['Misc\SuperChecklistController@updateSettings'], 'class' => 'horizontal-form', 'files' => true]) !!}

                        @include('form-error')

                        <div class="form-body">
                            <h3>Supervisors To Complete Checklist</h3>
                            <hr class="field-hr">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('super_list', $errors) !!}">
                                        {!! Form::label('super_list', 'Supervisor(s)', ['class' => 'control-label']) !!}
                                        {!! Form::select('super_list', Auth::user()->company->supervisorsSelect(), $super_list, ['class' => 'form-control bs-select', 'name' => 'super_list[]', 'title' => 'Select one or more supervisors', 'multiple']) !!}
                                        {!! fieldErrorMessage('super_list', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions right">
                                <a href="/supervisor/checklist" class="btn default"> Back</a>
                                <button type="submit" class="btn green"> Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script type="text/javascript">
        $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

        $(document).ready(function () {
            //$("#super_list").select2({placeholder: "Select one or more", width: '100%'});

            $(".editChecklist").click(function (e) {
                e.preventDefault(e);
                var event_id = e.target.id.split('-');
                var check_id = event_id[1];
                var day = event_id[2];
                //alert('d:'+day+' c:'+check_id);

                window.location.href = "/supervisor/checklist/" + check_id + "/" + day;
            });

        });

    </script>
@stop