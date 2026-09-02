@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('site.maintenance'))
            <li><a href="/site/maintenance">Maintenance Register</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Categories</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Maintenance Request Categories</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->hasPermission2('add.site.maintenance'))
                                <button type="button" class="btn btn-circle green btn-outline btn-sm" data-toggle="modal" data-target="#modal_create_category"><i class="fa fa-plus"></i> Add</button>
                            @endif
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th> Name</th>
                                <th style="width:10%"></th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <x-ui.bootstrap-modal id="modal_create_category" title="Add maintenance category" max-width="560px">
            <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteMaintenanceCategoryController::class, 'store']) }}" id="create-category-form">
                @csrf
                <x-form.hidden name="_category_form" value="create"/>
                <x-form.input name="create_name" id="create-category-name" label="Name" :value="old('_category_form') === 'create' ? old('create_name') : ''"/>
            </form>

            <x-slot name="footer">
                <button type="button" class="sws-modal-btn sws-modal-btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" form="create-category-form" class="sws-modal-btn sws-modal-btn-primary">Add category</button>
            </x-slot>
        </x-ui.bootstrap-modal>

        <x-ui.bootstrap-modal id="modal_edit_category" title="Edit maintenance category" max-width="560px">
            <form method="POST" action="{{ url('/site/maintenance/category/0') }}" id="edit-category-form">
                @csrf
                @method('PATCH')
                <x-form.hidden name="_category_form" value="edit"/>
                <x-form.hidden name="_category_id" id="edit-category-id" :value="old('_category_id')"/>
                <x-form.input name="edit_name" id="edit-category-name" label="Name" :value="old('_category_form') === 'edit' ? old('edit_name') : ''"/>
            </form>

            <x-slot name="footer">
                <button type="button" class="sws-modal-btn sws-modal-btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" form="edit-category-form" class="sws-modal-btn sws-modal-btn-primary">Save changes</button>
            </x-slot>
        </x-ui.bootstrap-modal>

        <x-ui.bootstrap-modal id="modal_delete_category" title="Delete category?" max-width="520px" footer-align="center">
            <div class="sws-confirm-content">
                <p class="sws-confirm-text">This category will be permanently deleted.</p>
                <span class="sws-confirm-item" id="delete-category-name"></span>
            </div>

            <x-slot name="footer">
                <button type="button" class="sws-modal-btn sws-modal-btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="sws-modal-btn sws-modal-btn-danger" id="confirm-delete-category">Delete category</button>
            </x-slot>
        </x-ui.bootstrap-modal>
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
        $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

        var categoryBaseUrl = @js(url('/site/maintenance/category'));
        var pendingDeleteUrl = null;
        var status1 = $('#status1').val();
        var table1 = $('#table1').DataTable({
            pageLength: 100,
            processing: true,
            serverSide: true,
            ajax: {
                'url': '{!! url('site/categories/maintenance/dt/main_cats') !!}',
                'type': 'GET',
                'data': function (d) {
                    d.status = $('#status1').val();
                }
            },
            columns: [
                {data: 'name', name: 'name'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            order: [
                [0, "asc"]
            ]
        });

        $('select#status1').change(function () {
            table1.ajax.reload();
        });

        function openEditCategory(id, name) {
            $('#edit-category-id').val(id);
            $('#edit-category-name').val(name);
            $('#edit-category-form').attr('action', categoryBaseUrl + '/' + id);
            $('#modal_edit_category').modal('show');
        }

        table1.on('click', '.btn-edit-category', function (e) {
            e.preventDefault();
            openEditCategory($(this).attr('data-category-id'), $(this).attr('data-category-name'));
        });

        table1.on('click', '.btn-delete[data-remote]', function (e) {
            e.preventDefault();
            pendingDeleteUrl = $(this).attr('data-remote');
            $('#delete-category-name').text($(this).attr('data-name'));
            $('#modal_delete_category').modal('show');
        });

        $('#confirm-delete-category').on('click', function () {
            if (!pendingDeleteUrl) return;

            var button = $(this);
            var deleteUrl = pendingDeleteUrl;
            button.prop('disabled', true).text('Deleting...');

            $.ajax({url: deleteUrl, type: 'DELETE', dataType: 'json', data: {method: '_DELETE', submit: true}})
                .done(function (data) {
                    $('#modal_delete_category').modal('hide');
                    toastr.success(data.message || 'Deleted category');
                    table1.draw(false);
                })
                .fail(function (xhr) {
                    var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to delete category';
                    toastr.error(message);
                })
                .always(function () {
                    button.prop('disabled', false).text('Delete category');
                });
        });

        $('#modal_delete_category').on('hidden.bs.modal', function () {
            pendingDeleteUrl = null;
            $('#delete-category-name').text('');
        });

        @if (old('_category_form', session('maintenance_category_modal')) === 'create')
        $('#modal_create_category').modal('show');
        @elseif (old('_category_form', session('maintenance_category_modal')) === 'edit')
        openEditCategory(@js(old('_category_id', data_get(session('maintenance_category'), 'id'))), @js(old('edit_name', data_get(session('maintenance_category'), 'name'))));
        @endif
    </script>
@stop
