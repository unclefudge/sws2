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
                            <a class="btn btn-circle green btn-outline btn-sm" href="/supervisor/checklist" data-original-title="Current">Current Week</a>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <h3>Past Weeks</h3>
                        <div class="row">
                            <div class="col-md-12">
                                <i class="fa fa-star font-yellow-saffron"></i> Weekly completed &nbsp; &nbsp; &nbsp;
                                <i class="fa fa-star font-green"></i> Day completed &nbsp; &nbsp; &nbsp;
                                <i class="fa fa-star-half-o"></i> Day part completed &nbsp; &nbsp; &nbsp;
                                <i class="fa fa-star-o font-red"></i> Day none completed &nbsp; &nbsp; &nbsp;
                                <i class="fa fa-check-circle font-green"></i> Manager Signed &nbsp; &nbsp; &nbsp;
                                <i class="fa fa-check-circle"></i> Supervisor Signed &nbsp; &nbsp; &nbsp;
                                <i class="fa fa-times-circle font-red"></i> Unsigned &nbsp; &nbsp; &nbsp;
                            </div>
                        </div>
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th width="10%"> Week</th>
                                <th> Checklist Summary</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($data as $week => $summary)
                                <?php $date = Carbon\Carbon::createFromDate($week); ?>
                                <tr>
                                    <td><a href="/supervisor/checklist/past/{{$week}}">{{ $date->format('d/m/Y') }}</a></td>
                                    <td>{!! $summary !!}</td>
                                </tr>
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