@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Pending Company Documents</span></li>
    </ul>
@stop

@section('content')

    <!-- BEGIN PAGE CONTENT INNER -->
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Pending Company Documents</span>
                        </div>
                        <div class="actions">
                            {{--}}
                            <a href="/manage/report/missing_company_info_csv" class="btn btn-circle btn-outline btn-sm green" id="view_pdf"> Download CSV</a>
                        --}}
                        </div>
                    </div>
                    <div class="portlet-body">
                        <h4>Pending {{ count($pending) }}</h4>
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th style="width: 5%"> #</th>
                                <th> Company</th>
                                <th> Document</th>
                                <th style="width: 10%"> Last Updated</th>
                                <th style="width: 10%"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($pending as $doc)
                                    <?php
                                    $todos = App\Models\Comms\Todo::where('type', 'company doc')->where('type_id', $doc->id)->get();
                                    $task = "";
                                    if ($todos) {
                                        foreach ($todos as $todo)
                                            $task .= ($todo->status) ? "<br>ToDo : " . $todo->assignedToBySBC() : "<br>ToDo: Closed by " . $todo->doneBY->name; // . " :" . $todo->id;
                                    }
                                    ?>
                                <tr>
                                    <td>
                                        <div class="text-center"><a href="{{$doc->attachment_url}}" target="_blank"><i class="fa fa-file-text-o"></i></a></div>
                                    </td>
                                    <td>{{ $doc->company->name}} {!! $task !!}</td>
                                    <td>{{ $doc->name}}</td>
                                    <td>{{ $doc->updated_at->format('d/m/Y')}}</td>
                                    <td><a href="/company/{{$doc->for_company_id}}/doc/{{$doc->id}}/edit" target="_blank" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <hr>
                        <a href="/manage/report" class="btn default pull-right">Back</a><br><br>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END PAGE CONTENT INNER -->
@stop


@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
@stop