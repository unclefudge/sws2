@extends('layout')

@section('pagetitle')
    <div class="page-title">
        <h1>Dashboard
            <small>information</small>
        </h1>
    </div>
@stop

@section('content')
    {{-- Safety Tip --}}
    @if (Auth::user()->company->reportsTo()->currentSafetytip())
        <div class="row">
            <div class="col-md-12">
                <div class="widget-thumb widget-bg-color-green margin-bottom-20">
                    <h4 class="widget-thumb-heading font-white text-uppercase">{{ Auth::user()->company->reportsTo()->currentSafetytip()->title }}
                        <span class="pull-right" style="color: #cbd4e0;
    font-size: 26px"> <i class="fa fa-comment-o font-white"></i></span>
                    </h4>
                    <i class="widget-thumb-icon bg-white font-dark fa fa-check pull-left"
                       style="height: 40px; width:40px; line-height: 25px; font-size: 30px; padding: 10px 5px"></i>
                    <div class="font-grey-steel"
                         style="min-height: 35px">{{ Auth::user()->company->reportsTo()->currentSafetytip()->body }}</div>
                </div>
            </div>
        </div>
    @endif

    {{-- Site Checkin --}}
    <div class="row">
        @if (Session::has('siteID'))
            <div class="col-md-6 col-sm-6">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-map-marker font-dark"></i>
                            <span class="caption-subject font-dark bold uppercase">{{ $worksite->name }}</span>
                            <span class="caption-helper">{{ $worksite->address }}, {{ $worksite->suburb }}</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row">
                            <div class="col-xs-8">
                                @if($worksite->isUserOnsite(Auth::user()->id))
                                    <span>Checked in {{ $worksite->isUserOnsite(Auth::user()->id)->date->format('g:i A') }}</span>
                                @else
                                    <span class="font-red">You have not checked in</span>
                                @endif
                            </div>
                            <div class="margin-bottom-10 visible-sm visible-xs"></div>
                            <div class="col-xs-4">
                                <a href="{{ url('/logout') }}" class="btn btn-lg default hidden-sm hidden-xs"></i> Log out </a>
                                <a href="{{ url('/logout') }}" class="btn btn-sm default visible-sm visible-xs" style="margin-top: -15px"></i> Log out </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="col-md-6 col-sm-6 hidden-sm hidden-xs">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-map-marker font-dark"></i>
                            <span class="caption-subject font-dark bold uppercase">Job SITE CHECK-IN</span>
                            <span class="caption-helper">required for job site entry</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row">
                            <div class="col-xs-12 text-center">
                                Every worker MUST check in and acknowledge the work health and safety requirements before entering any worksite.<br><br>
                            </div>
                            <div class="col-xs-12 text-center">
                                <a href="/checkin" class="btn btn-lg dark hidden-sm hidden-xs"></i> Site Check-in </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 visible-sm visible-xs">
                <a href="/checkin" class="btn btn-lg dark center-block" style="margin-bottom: 5px"></i> Site Check-in </a>
                <div style="margin: 0px; padding: 0px; font-size: 6px">&nbsp;</div>
            </div>

        @endif
        @if (Auth::user()->hasPermission2('add.site.accident') || Auth::user()->hasPermission2('add.site.hazard') || Auth::user()->hasPermission2('add.site.asbestos'))
            <div class="col-md-6 col-sm-6 hidden-sm hidden-xs">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-medkit font-dark"></i>
                            <span class="caption-subject font-dark bold uppercase">Safety Report</span>
                            <span class="caption-helper">Safety is everyone's responsibility</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row">
                            <div class="col-md-6">
                                @if (Auth::user()->hasPermission2('add.site.accident'))
                                    <a href="/site/accident/create" class="btn btn-lg red center-block"></i> Report Accident </a>
                                @endif
                            </div>
                            <div class="margin-bottom-10 visible-sm visible-xs"></div>
                            <div class="col-md-6">
                                @if (Auth::user()->hasPermission2('add.site.hazard'))
                                    <a href="/site/hazard/create" class="btn btn-lg blue center-block"></i> Report Hazard </a>
                                @endif
                            </div>
                        </div>
                        @if (Auth::user()->hasPermission2('add.site.asbestos'))
                            <div class="row" style="margin-top: 10px">
                                <div class="col-md-12">
                                    <a href="/site/asbestos/create" class="btn btn-lg green center-block"></i> Lodge Asbestos Notification </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 visible-sm visible-xs">
                <a href="/site/accident/create" class="btn btn-lg red center-block"></i> Report Accident </a>
                <div style="margin: 0px; padding: 0px; font-size: 6px">&nbsp;</div>
                @if (Session::has('siteID'))
                    <a href="/site/hazard/create" class="btn btn-lg blue center-block" style="margin-bottom: 5px"></i> Lodge Safety Issue </a>
                @endif
                <div style="margin: 0px; padding: 0px; font-size: 6px">&nbsp;</div>
            </div>
        @endif
    </div>

    <!-- Outstanding Safety Hazards -->
    @if (Session::has('siteID') && $worksite->hasHazardsOpen())
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-life-ring font-dark"></i>
                            <span class="caption-subject font-dark bold uppercase">Current Safety Hazards</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-striped table-bordered table-hover order-column">
                                    <thead>
                                    <tr class="mytable-header">
                                        <th width="5%"> #</th>
                                        <th width="10%"> Date</th>
                                        <th> Safety Concern</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($worksite->hazardsOpen() as $issue)
                                        <tr>
                                            <td>
                                                <div class="text-center"><a href="/site/hazard/{{ $issue->id }}"><i class="fa fa-search"></i> </a></div>
                                            </td>
                                            <td>{{ $issue->created_at->format('d/m/Y') }}</td>
                                            <td>{{ $issue->reason }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6 col-sm-6">
            <div class="portlet light tasks-widget ">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-list-alt font-dark"></i>
                        <span class="caption-subject font-dark bold uppercase">Outstanding Tasks</span>
                        <span class="caption-helper hidden-sm hidden-xs">Please complete these tasks</span>
                    </div>
                    <div class="actions">
                        <a class="btn btn-circle btn-icon-only btn-default fullscreen" href="javascript:;" data-original-title="" title=""> </a>
                    </div>
                </div>
                <div class="portlet-body">
                    <div class="scroller">
                        <ul class="feeds">
                            {{-- Update User profile --}}
                            @if(!Auth::user()->email)
                                <li>
                                    <a href="/user/{{ Auth::user()->username }}/settings" class="task-title">
                                        <div class="col1">
                                            <div class="cont">
                                                <div class="cont-col1">
                                                    <div class="label label-sm label-warning">
                                                        <i class="fa fa-user"></i>
                                                    </div>
                                                </div>
                                                <div class="cont-col2">
                                                    <div class="desc"> Please update your personal details in profile</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col2">
                                            <div class="date"></div>
                                        </div>
                                    </a>
                                </li>
                            @endif

                            {{-- Outstanding ToDoo Tasks for user --}}
                            @foreach (TODO_TYPES AS $todo_type => $todo_name)
                                @if (Auth::user()->todoType($todo_type, 1)->count())
                                    <h4>{{$todo_name}}</h4>
                                    @foreach(Auth::user()->todoType($todo_type, 1) as $todo)
                                        @include('pages/_home-todo')
                                    @endforeach
                                @endif
                            @endforeach

                            {{-- Company Docs --}}
                            @if (Auth::user()->todoType('company doc', 1)->count() || Auth::user()->todoType('company ptc', 1)->count() || Auth::user()->todoType('company privacy', 1)->count())
                                <h4>Company Documents</h4>
                                @foreach(Auth::user()->todoType('company doc', 1) as $todo)
                                    @include('pages/_home-todo')
                                @endforeach
                                @foreach(Auth::user()->todoType('company ptc', 1) as $todo)
                                    @include('pages/_home-todo')
                                @endforeach
                                @foreach(Auth::user()->todoType('company privacy', 1) as $todo)
                                    @include('pages/_home-todo')
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Safety Issues --}}
            <div class="portlet light tasks-widget ">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-list-alt font-dark"></i>
                        <span class="caption-subject font-dark bold uppercase">Unresolved Site Safety Issues</span>
                    </div>
                    <div class="actions">
                        <a class="btn btn-circle btn-icon-only btn-default fullscreen" href="javascript:;" data-original-title="" title=""> </a>
                    </div>
                </div>
                <div class="portlet-body">
                    <div class="scroller">
                        <ul class="feeds">
                            {{-- Open Site Accidents for CC admin/super --}}
                            <?php $count = 0 ?>
                            @foreach(App\Models\Site\SiteAccident::where('status', '1')->get() as $doc)
                                @if(Auth::user()->allowed2('view.site.accident', $doc))
                                    <?php $count ++ ?>
                                    @if ($count == 1)
                                        <h4>Accidents</h4>
                                    @endif
                                    <li>
                                        <a href="/site/accident/{{ $doc->id }}" class="task-title">
                                            <div class="col1">
                                                <div class="cont">
                                                    <div class="cont-col1">
                                                        <div class="label label-sm label-danger">
                                                            <i class="fa fa-medkit"></i>
                                                        </div>
                                                    </div>
                                                    <div class="cont-col2">
                                                        <div class="desc"> Unresolved accident on @ {{ $doc->site->name }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col2">
                                                <div class="date"> {{ $doc->date->format('d/m/Y') }}</div>
                                            </div>
                                        </a>
                                    </li>
                                @endif
                            @endforeach

                            {{-- Open Site Hazards + Taskfor CC admin/super --}}
                            <?php $count = 0 ?>
                            @foreach(App\Models\Site\SiteHazard::where('status', '1')->get() as $doc)
                                @if(Auth::user()->allowed2('view.site.hazard', $doc))
                                    <?php $count ++ ?>
                                    @if ($count == 1)
                                        <h4>Site Hazards</h4>
                                    @endif
                                    <li>
                                        <a href="/site/hazard/{{ $doc->id }}" class="task-title">
                                            <div class="col1">
                                                <div class="cont">
                                                    <div class="cont-col1">
                                                        <div class="label label-sm label-info">
                                                            <i class="fa fa-medkit"></i>
                                                        </div>
                                                    </div>
                                                    <div class="cont-col2">
                                                        <div class="desc"> Unresolved issue on @ {{ $doc->site->name }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col2">
                                                <div class="date"> {{ $doc->created_at->format('d/m/Y') }}</div>
                                            </div>
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                            @foreach(Auth::user()->todoType('hazard', 1) as $todo)
                                <?php $count ++ ?>
                                @if ($count == 1)
                                    <h4>Site Hazards</h4>
                                @endif
                                <li>
                                    <a href="{{ $todo->url() }}" class="task-title">
                                        <div class="col1">
                                            <div class="cont">
                                                <div class="cont-col1">
                                                    <div class="label label-sm label-success">
                                                        <i class="fa fa-bookmark"></i>
                                                    </div>
                                                </div>
                                                <div class="cont-col2">
                                                    <div class="desc"> {{ $todo->name }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col2">
                                            <div class="date"> {!! ($todo->due_at) ? $todo->due_at->format('d/m/Y') : '-'!!} </div>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Equipment --}}
            @if (Session::has('siteID') && Auth::user()->hasPermission2('view.equipment'))
                <div class="portlet light tasks-widget ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list-alt font-dark"></i>
                            <span class="caption-subject font-dark bold uppercase">Onsite Equipment</span>
                        </div>
                        <div class="actions">
                            @if (Auth::user()->hasPermission2('view.equipment.stocktake'))
                                <a class="btn btn-circle btn-outline btn-default" href="/equipment/stocktake/{{ ($worksite->equipmentLocation) ? $worksite->equipmentLocation->id : 0 }}" data-original-title="" title=""> Stocktake</a>
                            @endif
                            <a class="btn btn-circle btn-icon-only btn-default fullscreen" href="javascript:;" data-original-title="" title=""> </a>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="scroller">
                            @if (count($worksite->equipmentItems()))
                                <ul class="feeds">
                                    @foreach($worksite->equipmentItems() as $item)
                                        <li>
                                            <a href="/equipment/transfer/{{ $item->id }}" class="task-title">
                                                <div class="col1">
                                                    <div class="cont">
                                                        <div class="cont-col1">
                                                            {{ $item->qty }}
                                                        </div>
                                                        <div class="cont-col2">
                                                            <div class="desc"> {{ $item->equipment->name }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col2">
                                                    <div class="date"> {!! (Auth::user()->allowed2('edit.equipment', $item)) ? '<a href="/equipment/' . $item->id . '/transfer" class="btn default btn-xs sbold uppercase margin-bottom">Transfer</a>' : '' !!}</div>
                                                </div>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                No equipment on site
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-6 col-sm-6">
            <div class="portlet light portlet-fit">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-file-text-o font-dark"></i>
                        <span class="caption-subject font-dark bold uppercase">Job Site Documents</span>
                    </div>
                    <div class="actions">
                        <a class="btn btn-circle btn-icon-only btn-default fullscreen" href="javascript:;" data-original-title="" title=""> </a>
                    </div>
                </div>
                <div class="portlet-body">
                    <div class="panel-group accordion" id="accordion3">
                        @if (Session::has('siteID'))
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse" data-parent="#accordion3" href="#collapse_3_1"> Risk Assessments </a>
                                    </h4>
                                </div>
                                <div id="collapse_3_1" class="panel-collapse collapse">
                                    <div class="panel-body" style="height:200px; overflow-y:auto;">
                                        <div class="mt-element-list">
                                            <div class="mt-list-container list-simple" style="border: none; margin: 0px; padding: 0px">
                                                <ul class="feeds">
                                                    @if ($worksite->docsOfType('RISK')->first())
                                                        @foreach($worksite->docsOfType('RISK') as $doc)
                                                            <li>
                                                                <a href="{{ $doc->attachmentUrl }}" class="task-title">
                                                                    <div class="col1">
                                                                        <div class="cont">
                                                                            <div class="cont-col1">
                                                                                <div class="label label-sm label-default">
                                                                                    <i class="fa fa-file-text-o"></i>
                                                                                </div>
                                                                            </div>
                                                                            <div class="cont-col2">
                                                                                <div class="desc"> {{ $doc->name }}</div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    @else
                                                        <li class="mt-list-item" style="padding: 10px 0px">
                                                            <div class="list-icon-container"></div>
                                                            <div class="list-item-content">No current risk assessments for this site</div>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse" data-parent="#accordion3" href="#collapse_3_2"> Hazardous Materials </a>
                                    </h4>
                                </div>
                                <div id="collapse_3_2" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <div class="mt-element-list">
                                            <div class="mt-list-container list-simple" style="border: none;  margin: 0px; padding: 0px">
                                                <ul class="feeds">
                                                    @if ($worksite->docsOfType('HAZ')->first())
                                                        @foreach($worksite->docsOfType('HAZ') as $doc)
                                                            <li>
                                                                <a href="{{ $doc->attachmentUrl }}" class="task-title">
                                                                    <div class="col1">
                                                                        <div class="cont">
                                                                            <div class="cont-col1">
                                                                                <div class="label label-sm label-default">
                                                                                    <i class="fa fa-file-text-o"></i>
                                                                                </div>
                                                                            </div>
                                                                            <div class="cont-col2">
                                                                                <div class="desc"> {{ $doc->name }}</div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    @else
                                                        <li class="mt-list-item" style="padding: 10px 0px">
                                                            <div class="list-icon-container"></div>
                                                            <div class="list-item-content">No current hazardous materials report for this site</div>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse" data-parent="#accordion3" href="#collapse_3_3"> Plans </a>
                                    </h4>
                                </div>
                                <div id="collapse_3_3" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <div class="mt-element-list">
                                            <div class="mt-list-container list-simple" style="border: none;  margin: 0px; padding: 0px">
                                                <ul class="feeds">
                                                    @if ($worksite->docsOfType('PLAN')->first())
                                                        @foreach($worksite->docsOfType('PLAN') as $doc)
                                                            <li>
                                                                <a href="{{ $doc->attachmentUrl }}" class="task-title">
                                                                    <div class="col1">
                                                                        <div class="cont">
                                                                            <div class="cont-col1">
                                                                                <div class="label label-sm label-default">
                                                                                    <i class="fa fa-file-text-o"></i>
                                                                                </div>
                                                                            </div>
                                                                            <div class="cont-col2">
                                                                                <div class="desc"> {{ $doc->name }}</div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    @else
                                                        <li class="mt-list-item" style="padding: 10px 0px">
                                                            <div class="list-icon-container"></div>
                                                            <div class="list-item-content">No current plans for this site</div>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <h5>Associated Documents</h5>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse" data-parent="#accordion3" href="#collapse_3_4"> Safe Work Method
                                        Statements </a>
                                </h4>
                            </div>
                            <div id="collapse_3_4" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <div class="mt-element-list">
                                        <div class="mt-list-container list-simple" style="border: none;  margin: 0px; padding: 0px">
                                            <ul class="feeds">
                                                @if (Auth::user()->company->wmsdocs->first())
                                                    @foreach(Auth::user()->company->wmsdocs as $doc)
                                                        @if($doc->status == 1)
                                                            <li>
                                                                <a href="{{ $doc->attachmentUrl }}" class="task-title">
                                                                    <div class="col1">
                                                                        <div class="cont">
                                                                            <div class="cont-col1">
                                                                                <div class="label label-sm label-default">
                                                                                    <i class="fa fa-file-text-o"></i>
                                                                                </div>
                                                                            </div>
                                                                            <div class="cont-col2">
                                                                                <div class="desc"> {{ $doc->name }}</div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <li class="mt-list-item" style="padding: 10px 0px">
                                                        <div class="list-icon-container"></div>
                                                        <div class="list-item-content">No Safe Work Method Statements</div>
                                                    </li>
                                                @endif

                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-sm-6">

        </div>
    </div>
@stop

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script type="text/javascript">

    var site_id = $('#site_id').val();

    var table1 = $('#table1').DataTable({
        processing: true,
        serverSide: true,
        bLengthChange: false,
        bFilter: false,
        paging: false,
        ajax: {
            'url': '{!! url('safety/doc/dt/risk') !!}',
            'type': 'GET',
            'data': function (d) {
                d.site_id = 181; //$('#site_id').val();
            }
        },
        columns: [
            {data: 'id', name: 'id', orderable: false, searchable: false},
            {data: 'name', name: 'name', orderable: false, searchable: false},
        ],
        order: [
            [1, "asc"]
        ]
    });

    $('#site_id').change(function () {
        table1.ajax.reload();
    });
</script>
@stop