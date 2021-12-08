@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('view.weekly.planner'))
            <li><a href="/planner/weekly">Weekly Planner</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Up and Coming Projects</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze">Up and Coming Projects</span>
                        </div>
                        <div class="actions">
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th width="5%"> Site</th>
                                <th> Name</th>
                                <th> Job Start</th>
                                <th> Council Approval</th>
                                <th> Construction Certificate</th>
                                <th> Pre Contruction</th>
                                <th> Contracts Sent<br><span class="font-red">(Admin Data)</span></th>
                                <th><span class="font-red">Contracts Signed</span></th>
                                <th><span class="font-red">Deposit Paid</span></th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach($site_list as $site_id)
                                <?php $site = \App\Models\Site\Site::find($site_id) ?>
                                <tr>
                                    <td><div class="text-center"><a href="/planner/preconstruction/{{ $site->id }}"><i class="fa fa-search"></i></a></div></td>
                                    <td>{{ $site->code }}</td>
                                    <td>{{ $site->name }}</td>
                                    <td>{{ ($site->JobStart) ? $site->JobStart->format('d/m/Y') : '' }}</td>
                                    <td>{{ ($site->JobFirstTaskOfType(576)) ? $site->JobFirstTaskOfType(576)->format('d/m/Y') : ''  }}</td>
                                    <td>{{ ($site->JobFirstTaskOfType(582)) ? $site->JobFirstTaskOfType(582)->format('d/m/Y') : ''  }}</td>
                                    <td>{{ ($site->JobFirstTaskOfType(264)) ? $site->JobFirstTaskOfType(264)->format('d/m/Y') : ''  }}</td>
                                    <td>
                                        {{ ($site->JobFirstTaskOfType(578)) ? $site->JobFirstTaskOfType(578)->format('d/m/Y')."<br>" : ''  }}
                                        {!! ($site->contract_sent) ? "<span class='font-red'>".$site->contract_sent->format('d/m/Y')."</span>" : '' !!}
                                    </td>
                                    <td>
                                        {!! ($site->contract_signed) ? "<span class='font-red'>".$site->contract_signed->format('d/m/Y')."</span>" : '' !!}
                                    </td>
                                    <td>
                                        {!! ($site->deposit_paid) ? "<span class='font-red'>".$site->deposit_paid->format('d/m/Y')."</span>" : '' !!}
                                    </td>
                                </tr>
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
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script type="text/javascript">

    $(document).ready(function () {

    });
</script>
@stop