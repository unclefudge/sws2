@extends('layout')
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/supervisor/checklist">Supervisor Checklist</a><i class="fa fa-circle"></i></li>
        <li>Past Weeks</li>
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
                            <span class="caption-subject bold uppercase font-green-haze">Weekly Supervisor Checklist</span>
                        </div>
                        <div class="actions">
                            <a class="btn btn-circle green btn-outline btn-sm" href="/supervisor/checklist" data-original-title="Past">Current Week</a>
                            <a class="btn btn-circle green btn-outline btn-sm" href="/supervisor/checklist/past" data-original-title="Past">Past Weeks</a>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <h3>Weekending: {{ $fri->format('j F, Y') }}</h3>
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th> Supervisor</th>
                                <th width="7%"> Mon</th>
                                <th width="7%"> Tue</th>
                                <th width="7%"> Wed</th>
                                <th width="7%"> Thu</th>
                                <th width="7%"> Fri</th>
                                <th width="20%"> Signed by</th>
                                <th width="7%"></th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach ($checklists as $checklist)
                                @if (in_array($checklist->super_id, $supervisors))
                                    <tr>
                                        <td>{{$checklist->supervisor->name}}</td>
                                        <td id="d-{{$checklist->id}}-1">{!! $checklist->dayIcon(1) !!}</td>
                                        <td id="d-{{$checklist->id}}-2">{!! $checklist->dayIcon(2) !!}</td>
                                        <td id="d-{{$checklist->id}}-3">{!! $checklist->dayIcon(3) !!}</td>
                                        <td id="d-{{$checklist->id}}-4">{!! $checklist->dayIcon(4) !!}</td>
                                        <td id="d-{{$checklist->id}}-5">{!! $checklist->dayIcon(5) !!}</td>
                                        <td>
                                            {!! $checklist->signed_by_field !!}
                                        </td>
                                        <td>
                                            <a href="/supervisor/checklist/{{$checklist->id}}/weekly" class="btn blue btn-sm sbold uppercase">Weekly</a>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
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

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
        });

        $(document).ready(function () {
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