@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Users With Specific Permission</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Users with Specific Permission</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="note" style="background-color: #e1e5ec; border-color: #acb5c3">
                            <div class="row">
                                <div class="col-md-2"><h3>Filter by</h3></div>
                                <div class="col-md-4">
                                    {!! Form::label('permission', 'Permission type', ['class' => 'control-label']) !!}
                                    {!! Form::select('permission', $permission_list, $type, ['class' => 'form-control bs-select']) !!}
                                </div>
                            </div>
                        </div>
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th> Name</th>
                                <th> Company</th>
                                <th width="10%"> View</th>
                                <th width="10%"> Edit</th>
                                <th width="5%"> Add</th>
                                <th width="5%"> Del</th>
                                <th width="5%"> Sig</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $levels = ['99' => 'All', '50' => 'Our Company', '40' => 'Supervisor for', '30' => 'Planned for', '20' => 'Own Company', '10' => 'Individual Only', '1' => 'Y'];
                            $cid = Auth::user()->company_id;
                            $pid = Auth::user()->company->reportsTo()->id;
                            ?>
                            @foreach($users as $user)
                                <?php
                                $view = $user->permissionLevel("view.$type", $cid);
                                $edit = $user->permissionLevel("edit.$type", $cid);
                                $add = $user->permissionLevel("add.$type", $cid);
                                $del = $user->permissionLevel("del.$type", $cid);
                                $sig = $user->permissionLevel("sig.$type", $cid);
                                ?>
                                @if ($view || $edit || $add || $del || $sig)
                                <tr @if(!$user->status) class="font-red" @endif>
                                    <td>
                                        <div class="text-center"><a href="/user/{{$user->id}}/security"><i class="fa fa-search"></i></a></div>
                                    </td>
                                    <td>{{ $user->fullname }}</td>
                                    <td>{{ $user->company->name_alias }}</td>
                                    <td>{{ ($view) ? $levels[$view] : '' }}</td>
                                    <td>{{ ($edit) ? $levels[$edit] : '' }}</td>
                                    <td>{{ ($add) ? $levels[$add] : '' }}</td>
                                    <td>{{ ($del) ? $levels[$del] : '' }}</td>
                                    <td>{{ ($sig) ? $levels[$sig] : '' }}</td>
                                </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="loadSpinnerOverlay" id="spinner" style="display: none">
                            <div class="loadSpinner"><i class="fa fa-spinner fa-pulse fa-2x fa-fw margin-bottom"></i> Loading...</div>
                        </div>
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
        // Location
        $("#permission").change(function () {
            $("#spinner").show();
            window.location.href = "/manage/report/users_with_permission/" + $("#permission").val();
        });
    });
</script>
@stop