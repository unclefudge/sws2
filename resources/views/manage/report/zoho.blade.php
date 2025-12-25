@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Zoho Import Log</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Zoho Import Log</span>
                            <span class="caption-helper"> 11:30am daily</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th> Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($files as $file)
                                    <?php
                                    $pass = false;
                                    $jobs_complete = strpos(file_get_contents(storage_path("app/log/zoho/$file")), "ALL DONE - ZOHO IMPORT JOBS COMPLETE");
                                    $contacts_complete = strpos(file_get_contents(storage_path("app/log/zoho/$file")), "ALL DONE - ZOHO IMPORT CONTACTS COMPLETE");
                                    if ($jobs_complete && $contacts_complete)
                                        $pass = true;
                                    ?>
                                <tr>
                                    <td>
                                        <div class="text-center">
                                            @if ($pass)
                                                <i class="fa fa-check font-green"></i>
                                            @else
                                                <i class="fa fa-times font-red"></i>
                                            @endif
                                        </div>
                                    </td>
                                    <td><a href="{{ storage_path('app/log/zoho/'.$file) }}" target="_blank">{!! substr($file, 6, 2) !!}/{!! substr($file, 4, 2) !!}/{!! substr($file, 2, 2) !!}</a></td>
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
@stop


@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
@stop