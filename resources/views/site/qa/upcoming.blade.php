@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Quality Assurance</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        {{-- var_dump(session()->all()) --}}
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Upcoming QA Reports</span>
                        </div>
                        <div class="actions">
                            @if (Auth::user()->hasAnyRole2('mgt-general-manager|web-admin'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/qa" data-original-title="QA">Current QA Reports</a>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        @if (Auth::user()->permissionLevel('view.site.qa', 3) == 99)
                            <input type="hidden" id="supervisor_sel" value="1">
                            <div class="col-md-4">
                                {!! Form::select('supervisor', ['all' => 'Active sites'] + Auth::user()->company->reportsTo()->supervisorsSelect(), $super_id, ['class' => 'form-control bs-select', 'id' => 'supervisor']) !!}
                            </div>
                        @else
                            <input type="hidden" id="supervisor_sel" value="0">
                        @endif

                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th> Site</th>
                                <th> Supervisor</th>
                                <th style="width:10%"> No#</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($sites as $site)
                                <tr>
                                    <td><a class="toggleExtra" data-site_id="{{ $site->id }}">{{ $site->name }}</a></td>
                                    <td>{{ $site->supervisor }}</td>
                                    <td></td>
                                </tr>
                                <tr id="extrainfo-{{ $site->id }}" style="display: none">
                                    <td colspan="4" style="background: #333; color: #fff">
                                        <b>Upcoming + Potential QA's</b>
                                        <div style="background: #fff; color:#636b6f;  padding: 15px">
                                            <table style="width:100%">
                                                <tr style="font-weight: bold">
                                                    <td style="width:15%">Date</td>
                                                    <td>QA Name</td>
                                                    <td style="width:20%">Trigger(s)</td>
                                                </tr>
                                                @foreach ($site->qas as $qa)
                                                    <tr>
                                                        <td>{{$qa['date']}}</td>
                                                        <td><a class="triggerQA" data-qid="{{$qa['template']}}" data-name="{{$qa['name']}}" data-site="{{$site->name}}" data-site_id="{{$site->id}}">{{$qa['name'] }}</a></td>
                                                        <td>{{$qa['task']}}</td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                                <tr style="display: none">
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END PAGE CONTENT INNER -->
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

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script type="text/javascript">

        $("#supervisor").change(function (e) {
            window.location.href = "/site/qa/upcoming/" + $("#supervisor").val();
        });

        $(".toggleExtra").click(function (e) {
            var event_id = e.target.id.split('-');
            var site_id = event_id[1];
            var site_id = $(this).data('site_id');

            $("#extrainfo-" + site_id).toggle();
        });

        $(".triggerQA").click(function (e) {
            e.preventDefault();
            var url = "/site/qa/trigger/" + $(this).data('qid') + "/" + $(this).data('site_id');
            var id = $(this).data('id');
            var name = $(this).data('name');
            var site = $(this).data('site');
            swal({
                title: "Create QA",
                text: "<b>" + name + "</b><br>" + site,
                showCancelButton: true,
                cancelButtonColor: "#555555",
                confirmButtonColor: "#32c5d2",
                confirmButtonText: "Create",
                allowOutsideClick: true,
                html: true,
            }, function () {
                window.location.href = url;
            });
        });
    </script>
@stop