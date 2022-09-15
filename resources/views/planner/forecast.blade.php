@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('view.weekly.planner'))
            <li><a href="/planner/weekly">Weekly Planner</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Forecast Planner</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze">Forecast Planner</span>
                        </div>
                        <div class="actions">
                            <img src="/img/col-blue-C5D1EC.png"> Job Start &nbsp; &nbsp; <img src="/img/col-green-B5E2CD.png"> Active &nbsp; &nbsp; <img src="/img/col-orange-FDD7B1.png"> Prac Completion
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered order-column" id="table_list">
                            <!--
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th> Site Name</th>
                                <th width="7%"> Supervisor</th>
                                <th width="5%"> {{ $months[0] }}</th>
                                <th width="5%"> {{ $months[1] }}</th>
                                <th width="5%"> {{ $months[2] }}</th>
                                <th width="5%"> {{ $months[3] }}</th>
                                <th width="5%"> {{ $months[4] }}</th>
                                <th width="5%"> {{ $months[5] }}</th>
                            </tr>
                            </thead> -->

                            <tbody>
                            @foreach($data as $row)
                                @if ( $row['site_id'])
                                    <tr>
                                        <td width="5%">
                                            <div class="text-center"><a href="/planner/site/{{ $row['site_id'] }}"><i class="fa fa-search"></i></a></div>
                                        </td>
                                        <td>{{ $row['site_name'] }} </td>
                                        {{--<td>{{ $row['super_initials'] }}</td>--}}

                                        {{-- Loop through for each month --}}
                                        @for ($i = 0; $i < 6; $i++)
                                            @if ($row["m$i"] == 'Active')
                                                <td width="5%" style="background: #B5E2CD;"></td>
                                            @elseif ($row["m$i"] == 'START')
                                                <td width="5%" style="background: #C5D1EC;">
                                                    <?php $split = explode(' ', $row['job_start_day']); ?>
                                                    {{ $split[0] }}<sup> {{ $split[1] }}</sup>
                                                </td>
                                            @elseif ($row["m$i"] == 'PRAC')
                                                <td width="5%" style="background: #FDD7B1;">
                                                    <?php $split = explode(' ', $row['prac_complete_day']); ?>
                                                    {{ $split[0] }}<sup> {{ $split[1] }}</sup>
                                                </td>
                                            @else
                                                <td width="5%"></td>
                                            @endif
                                        @endfor
                                    </tr>
                                @else
                                    <tr style="background: #f0f6fa">
                                        <td colspan="2"><b> &nbsp; {{ $row['site_name'] }}</b></td>
                                        <th width="5%"> {{ $months[0] }}</th>
                                        <th width="5%"> {{ $months[1] }}</th>
                                        <th width="5%"> {{ $months[2] }}</th>
                                        <th width="5%"> {{ $months[3] }}</th>
                                        <th width="5%"> {{ $months[4] }}</th>
                                        <th width="5%"> {{ $months[5] }}</th>
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
    <!-- END PAGE CONTENT INNER -->
@stop


@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script type="text/javascript">

    $(document).ready(function () {


    });

</script>
@stop