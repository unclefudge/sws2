@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/settings">Settings</a><i class="fa fa-circle"></i></li>
        <li><span>Role Management</span></li>
    </ul>
    @stop

    @section('content')

            <!-- BEGIN PAGE CONTENT INNER -->
    <div class="page-content-inner">
        <div class="note note-warning">
            <p>Sets the default permissions associated with various 'roles'.</p>
            <ul>
                <li>These are used to determine what users have access to view/update in the system.</li>
            </ul>
            @if (Auth::user()->company->subscription > 1)
                <p><b>'Child Role'</b> is a role that is allowed to be assigned to a 'Child Company'.<br>
                    <b>'Child First'</b> refers to the very first user created after a 'Child Company' is setup.<br>
                    <b>'Child Other'</b> refers to any other user the 'Child Company' creates themselves.
                </p>
            @endif
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Roles</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->hasPermission2('edit.settings'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/settings/role/create" data-original-title="Add">Add</a>
                            @endif
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th width="25%"> Name</th>
                                <th> Description</th>
                                <th width="10%">Child Role</th>
                                <th width="10%">Child First</th>
                                <th width="10%">Child Other</th>
                                <th width="5%"></th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach($roles as $role)
                                @if ($role->company_id == Auth::user()->company_id)
                                    <tr>
                                        <td>
                                            <div class="text-center"><a href="/settings/role/{{ $role->id }}/edit"><i class="fa fa-search"></i></a></div>
                                        </td>
                                        <td>{{ $role->name }}</td>
                                        <td>{{ $role->description }}</td>

                                        <td class="text-center" width="10%">
                                            @if ($role->external)
                                                @if (Auth::user()->allowed2('edit.settings', $role))
                                                    <a href="/settings/role/child-role/{{ $role->id }}"><i class="fa fa-check-square-o font-dark" style="font-size: 20px; padding-top: 5px"></i></a>
                                                @else
                                                    <i class="fa fa-check-square-o font-dark" style="font-size: 20px; padding-top: 5px"></i>
                                                @endif
                                            @elseif (Auth::user()->allowed2('edit.settings', $role) )
                                                <a href="/settings/role/child-role/{{ $role->id }}"><i class="fa fa-square-o " style="font-size: 20px; padding-top: 5px"></i></a>
                                            @endif
                                        </td>
                                        <td class="text-center" width="10%">
                                            @if ($role->external)
                                                @if ($role->child == "primary")
                                                    <i class="fa fa-check-square-o" style="font-size: 20px; padding-top: 5px"></i>
                                                @elseif (Auth::user()->allowed2('edit.settings', $role) )
                                                    <a href="/settings/role/child-primary/{{ $role->id }}"><i class="fa fa-square-o " style="font-size: 20px; padding-top: 5px"></i></a>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="text-center" width="10%">
                                            @if ($role->external)
                                                @if ($role->child == "default")
                                                    <i class="fa fa-check-square-o" style="font-size: 20px; padding-top: 5px"></i>
                                                @elseif (Auth::user()->allowed2('edit.settings', $role) )
                                                    <a href="/settings/role/child-default/{{ $role->id }}"><i class="fa fa-square-o " style="font-size: 20px; padding-top: 5px"></i></a>
                                                @endif
                                            @endif
                                        </td>
                                        <td><button class="btn dark btn-xs sbold uppercase margin-bottom btn-delete " data-id="{{ $role->id }}" data-name="{{ $role->name }}"><i class="fa fa-trash"></i></button></td>
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
    $.ajaxSetup({
        headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
    });

    $(document).ready(function () {


        $('.btn-delete').click(function (e) {
            e.preventDefault();
            var url = "/settings/role/"+$(this).data('id');
            var name = $(this).data('name');

            swal({
                title: "Are you sure?",
                text: "The role <b>" + name + "</b> will be deleted and the permissions associated with it will be removed from all users.<br><br><span class='font-red'><i class='fa fa-warning'></i> You will not be able to undo this action!</span>",
                showCancelButton: true,
                cancelButtonColor: "#555555",
                confirmButtonColor: "#E7505A",
                confirmButtonText: "Yes, delete it!",
                allowOutsideClick: true,
                html: true,
            }, function () {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    dataType: 'json',
                    data: {method: '_DELETE', submit: true},
                    success: function (data) {
                        toastr.error('Deleted role');
                    },
                }).always(function (data) {
                    location.reload();
                });
            });
        });
    });
</script>
@stop