@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Outstanding Quality Assurance</span></li>
    </ul>
    @stop

    @section('content')

            <!-- BEGIN PAGE CONTENT INNER -->
    <div class="page-content-inner">
        {!! Form::model('OutstandingQAPDF', ['action' => 'Misc\ReportController@OutstandingQAPDF', 'class' => 'horizontal-form']) !!}
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Outstanding Quality Assurance</span>
                        </div>
                        <div class="actions">
                            <button type="submit" class="btn btn-circle btn-outline btn-sm green" id="view_pdf"> View PDF</button>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th> Site</th>
                                <th> Name</th>
                                <th> Supervisor</th>
                                <th width="10%"> Uppdated</th>
                                <th width="10%"> Completed</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($supers as $super)
                                @foreach ($qas as $qa)
                                    @if ($qa->site->supervisorName == $super)
                                        <?php
                                        $total = $qa->items()->count();
                                        $completed = $qa->itemsCompleted()->count();
                                        $pending = '';
                                        if ($total == $completed && $total != 0) {
                                            if (!$qa->supervisor_sign_by)
                                                $pending = ' - Pending Supervisor';
                                            elseif (!$qa->manager_sign_by)
                                                $pending = ' - Pending Manager';
                                        }
                                        ?>
                                        <tr>
                                            <td>{{ $qa->site->name }}</td>
                                            <td>{{ $qa->name }}</td>
                                            <td>{{ $qa->site->supervisorName }}</td>
                                            <td>{{ $qa->updated_at->format('d/m/Y') }}</td>
                                            <td>{{ $completed }} / {{ $total }} {!! $pending !!}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            @endforeach
                            </tbody>
                        </table>

                        <hr>
                        <a href="/manage/report" class="btn default pull-right">Back</a><br><br>
                    </div>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
    <!-- loading Spinner -->
    <div style="background-color: #FFF; padding: 20px; display: none" id="spinner">
        <div class="loadSpinnerOverlay">
            <div class="loadSpinner"><i class="fa fa-spinner fa-pulse fa-2x fa-fw margin-bottom"></i> Loading...</div>
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
        //$('#view_pdf').click(function (e) {
        $('form').submit(function (e) {
            $('#spinner').show();
            return true;
        });
    });
</script>
@stop