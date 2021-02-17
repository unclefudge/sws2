@extends('layout')

@section('pagetitle')
    <div class="page-title">
        <h1><i class="fa fa-users"></i> New Users</h1>
    </div>
@stop

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
                        <div class="actions">
                            <a class="btn btn-circle btn-icon-only btn-default fullscreen" href="javascript:;"></a>
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
                                <th width="5%"> View</th>
                                <th width="5%"> Edit</th>
                                <th width="5%"> Add</th>
                                <th width="5%"> Del</th>
                                <th width="5%"> Sig</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($users as $user_id => $permissions)
                                <?php $user = App\User::find($user_id) ?>
                                <tr @if(!$user->status) class="font-red" @endif>
                                    <td><div class="text-center"><a href="/user/{{$user->id}}/security"><i class="fa fa-search"></i></a></div></td>
                                    <td>{{ $user->fullname }}</td>
                                    <td>{{ $user->company->name_alias }}</td>
                                    <td>{{ ($user->hasPermission2("view.$type")) ? 'Y' : 'N' }}</td>
                                    <td>{{ ($user->hasPermission2("edit.$type")) ? 'Y' : 'N' }}</td>
                                    <td>{{ ($user->hasPermission2("add.$type")) ? 'Y' : 'N' }}</td>
                                    <td>{{ ($user->hasPermission2("del.$type")) ? 'Y' : 'N' }}</td>
                                    <td>{{ ($user->hasPermission2("sig.$type")) ? 'Y' : 'N' }}</td>
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
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
@stop