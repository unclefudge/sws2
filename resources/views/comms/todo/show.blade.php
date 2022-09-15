@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/todo/">Todo</a><i class="fa fa-circle"></i></li>
        <li><span> Todo item</span></li>
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
                        {!! Form::model($todo, ['method' => 'PATCH', 'action' => ['Comms\TodoController@update', $todo->id], 'files' => true, 'id' => 'todo_form']) !!}
                        @include('form-error')

                        {!! Form::hidden('status', $todo->status, ['class' => 'form-control', 'id' => 'status']) !!}
                        {!! Form::hidden('delete_attachment', 0, ['class' => 'form-control', 'id' => 'delete_attachment']) !!}

                        <div class="form-body">
                            @if(!$todo->status)
                                <div class="row">
                                    <div class="col-md-12">
                                        <h3 class="pull-right font-red uppercase" style="margin:0 0 10px;">Completed {!! ($todo->done_at) ? $todo->done_at->format('d/m/Y') : '' !!}</h3>
                                    </div>
                                </div>
                            @endif
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        {!! Form::label('s_name', 'Name', ['class' => 'control-label']) !!}
                                        {!! Form::text('s_name', $todo->name, ['class' => 'form-control', 'readonly']) !!}
                                    </div>
                                </div>
                                <div class="col-md-1"></div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('s_due_at', 'Due Date', ['class' => 'control-label']) !!}
                                        {!! Form::text('s_due_at', ($todo->due_at) ? $todo->due_at->format('d/m/Y') : 'none', ['class' => 'form-control', 'readonly']) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- Description + Comment --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        {!! Form::label('s_info', 'Description of what to do', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('s_info', $todo->info, ['rows' => '4', 'class' => 'form-control', 'readonly']) !!}
                                    </div>
                                </div>
                                @if ($todo->type == 'equipment' && $todo->location && count($todo->location->items))
                                    <div class="col-md-12">
                                        List of equipment to tranfer:<br>
                                        <ul>
                                            @foreach ($todo->location->items as $item)
                                                <li>({{ $item->qty }}) {{ $item->item_name }}</li>
                                            @endforeach
                                        </ul>
                                        <br>
                                        <b>Assigned to:</b> {!! ($todo->type == 'equipment') ? $todo->assignedToCompanyBySBC() : $todo->assignedToBySBC() !!}<br>
                                        <br><br>
                                    </div>
                                @endif
                                <div class="col-md-12">
                                    <div class="form-group">
                                        {!! Form::label('comments', 'Comments', ['class' => 'control-label']) !!}
                                        @if ($todo->status)
                                            {!! Form::textarea('comments', $todo->comments, ['rows' => '4', 'class' => 'form-control']) !!}
                                        @else
                                            {!! Form::textarea('s_comments', $todo->comments, ['rows' => '4', 'class' => 'form-control', 'readonly']) !!}
                                        @endif
                                    </div>
                                    <div class="pull-right">Created by: {{ ($todo->createdBy) ? $todo->createdBy->name : 'SafeWorksite' }}</div>
                                    <br><br>
                                </div>
                            </div>

                            @if($todo->type == 'hazard' || $todo->type == 'accident' || $todo->type == 'incident')
                                @if ($todo->attachment_url) {{-- && file_exists(public_path($todo->attachment_url) --}}
                                <div class="row" id="attachment_div">
                                    <div class="col-md-3">
                                        <div>
                                            <a href="{{ $todo->attachment_url }}" class="html5lightbox " title="{{ $todo->name }}" data-lityXXX>
                                                <img src="{{ $todo->attachment_url }}" class="thumbnail img-responsive img-thumbnail"></a>
                                        </div>
                                        @if ($todo->status && $todo->attachment)
                                            <button class="btn default" id="delete">Delete image</button><br><br>
                                        @endif
                                    </div>
                                </div>
                                @endif
                                @if ($todo->status)
                                    <div class="row" id="uploadfile_div" style="@if ($todo->status && $todo->attachment) display:none @endif">
                                        <div class="col-md-6">
                                            <div class="form-group {!! fieldHasError('singlefile', $errors) !!}">
                                                <label class="control-label">Select File</label>
                                                <input id="singlefile" name="singlefile" type="file" class="file-loading">
                                                {!! fieldErrorMessage('singlefile', $errors) !!}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            {{-- List of Users Task assigned to--}}
                            @if($todo->assignedTo()->count())
                                <div class="row">
                                    <div class="col-md-12">
                                        <p><b>ToDo task can be completed by any of the following user(s):</b></p>
                                        @if (Auth::user()->id == $todo->created_by && $todo->assignedToBySBC())
                                            <p>{!! $todo->openedBySBC() !!}</p>
                                        @elseif ($todo->assignedToBySBC())
                                            <p>{!! $todo->assignedToBySBC() !!}</p>
                                        @endif
                                        @if(!$todo->status)
                                            <p class="font-red">COMPLETED BY: {!! ($todo->done_by) ? \App\User::find($todo->done_by)->fullname : 'unknown'!!}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="form-actions right">
                                @if($todo->type == 'maintenance')
                                    <a href="/site/maintenance/{{$todo->type_id}}" class="btn green">View Maintenance Request</a>
                                @endif
                                @if($todo->type == 'toolbox')
                                    <a href="/safety/doc/toolbox2/{{$todo->type_id}}" class="btn green">View Toolbox Talk</a>
                                @endif
                                @if($todo->type == 'qa')
                                    <a href="/site/qa/{{$todo->type_id}}" class="btn green">View QA Report</a>
                                @endif
                                @if($todo->type == 'hazard')
                                    <?php $hazard = \App\Models\Site\SiteHazard::find($todo->type_id) ?>
                                    @if (Auth::user()->allowed2('view.site.hazard', $hazard))
                                        <a href="/site/hazard/{{$todo->type_id}}" class="btn dark">View Site Hazard</a>
                                    @endif
                                @endif
                                @if($todo->type == 'accident')
                                    <?php $hazard = \App\Models\Site\SiteAccident::find($todo->type_id) ?>
                                    @if (Auth::user()->allowed2('view.site.accident', $hazard))
                                        <a href="/site/accident/{{$todo->type_id}}" class="btn dark">View Site Accident</a>
                                    @endif
                                @endif
                                @if($todo->type == 'incident')
                                    <?php $incident = \App\Models\Site\Incident\SiteIncident::find($todo->type_id) ?>
                                    @if (Auth::user()->allowed2('view.site.incident', $incident))
                                        <a href="/site/incident/{{$todo->type_id}}" class="btn dark">View Site Incident</a>
                                    @endif
                                @endif
                                @if($todo->type == 'incident prevent')
                                    <?php $incident = \App\Models\Site\Incident\SiteIncident::find($todo->type_id) ?>
                                    @if (Auth::user()->allowed2('view.site.incident', $incident))
                                        <a href="/site/incident/{{$todo->type_id}}" class="btn dark">View Site Incident</a>
                                    @endif
                                    @if ($todo->status && Auth::user()->allowed2('edit.site.incident', $incident))
                                        <a href="/todo/{{$todo->id}}/edit" class="btn red">Edit Task</a>
                                    @endif
                                @endif
                                @if($todo->type == 'swms')
                                    <a href="/safety/doc/wms/{{ $todo->type_id }}" class="btn dark">View expired SWMS</a>
                                    <a href="/safety/doc/wms/{{ $todo->type_id }}/replace" class="btn blue">Make new SWMS</a>
                                @endif
                                @if($todo->type == 'inspection_electrical')
                                    <a href="/site/inspection/electrical/{{ $todo->type_id }}" class="btn dark">View Report</a>
                                @endif
                                @if($todo->type == 'inspection_plumbing')
                                    <a href="/site/inspection/plumbing/{{ $todo->type_id }}" class="btn dark">View Report</a>
                                @endif
                                @if($todo->type == 'project supply')
                                    <a href="/site/supply/{{ $todo->type_id }}/edit" class="btn blue">Update Supply Info</a>
                                @endif
                                    @if($todo->type == 'extension')
                                        <a href="/site/extension" class="btn blue">View Contract Time Extensions</a>
                                    @endif
                                @if($todo->type == 'company doc')
                                    <?php $doc = \App\Models\Company\CompanyDoc::find($todo->type_id) ?>
                                    <a href="/company/{{ $doc->for_company_id }}/doc/{{ $doc->id }}/edit" class="btn dark">View Document</a>
                                @endif
                                @if($todo->type == 'company ptc')
                                    <?php $doc = \App\Models\Company\CompanyDocPeriodTrade::find($todo->type_id) ?>
                                    <a href="/company/{{ $doc->for_company_id }}/doc/period-trade-contract/{{ $doc->id }}" class="btn dark">View Document</a>
                                @endif
                                @if($todo->type == 'company privacy')
                                    <a href="/company/{{ Auth::user()->company_id }}/doc/privacy-policy/create" class="btn dark">View Document</a>
                                @endif
                                @if($todo->type == 'user doc')
                                    <?php $doc = \App\Models\User\UserDoc::find($todo->type_id) ?>
                                    <a href="/user/{{ $doc->user_id }}/doc/{{ $doc->id }}/edit" class="btn dark">View Document</a>
                                @endif
                                @if($todo->type == 'equipment')
                                    @if ($todo->status)
                                        <a href="{{ URL::previous() }}" class="btn default"> Back</a>
                                    @endif
                                    @if($todo->status && Auth::user()->allowed2('edit.todo', $todo))
                                        <button class="btn green" id="save">Save</button>
                                        @if ($todo->created_by == Auth::user()->id)
                                            <a href="/equipment/{{ $todo->type_id }}/transfer-cancel" class="btn dark"> Cancel Transfer</a>
                                        @endif
                                        <a href="/equipment/{{ $todo->type_id }}/transfer-verify" class="btn blue"> Verify Transfer</a>
                                    @endif
                                @endif
                                @if($todo->status && Auth::user()->allowed2('edit.todo', $todo) && in_array($todo->type, ['general', 'hazard', 'accident', 'incident', 'incident prevent', 'incident review']))
                                    <button class="btn green" id="save">Save</button>
                                    <button class="btn blue" id="close">Mark Complete</button>
                                @endif
                                @if(!$todo->status && ($todo->type == 'general' || (in_array($todo->type, ['hazard', 'accident', 'incident', 'incident prevent']) && Auth::user()->allowed2('edit.todo', $todo))))
                                    <button class="btn green" id="open">Re-open Task</button>
                                @endif
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
            <!--<link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>-->
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
    <script type="text/javascript">var html5lightbox_options = {watermark: "", watermarklink: ""};</script>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/js/libs/fileinput.min.js"></script>
    <script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
    @stop

    @section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
            <!--<script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>-->
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script>
        $.ajaxSetup({
            headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
        });

        $(document).ready(function () {
            /* Bootstrap Fileinput */
            $("#singlefile").fileinput({
                showUpload: false,
                allowedFileExtensions: ["jpg", "png", "gif", "jpeg"],
                browseClass: "btn blue",
                browseLabel: "Browse",
                browseIcon: "<i class=\"fa fa-folder-open\"></i> ",
                //removeClass: "btn btn-danger",
                removeLabel: "",
                removeIcon: "<i class=\"fa fa-trash\"></i> ",
                uploadClass: "btn btn-info",
            });
        });

        $("#delete").click(function (e) {
            e.preventDefault();
            $('#delete_attachment').val(1);
            $('#uploadfile_div').show();
            $('#attachment_div').hide();
        });

        $("#open").click(function (e) {
            e.preventDefault();
            $('#status').val(1);
            $("#todo_form").submit();
        });

        $("#close").click(function (e) {
            e.preventDefault();
            $('#status').val(0);
            $("#todo_form").submit();
        });

    </script>

@stop

