@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/foc">FOC Requirements</a><i class="fa fa-circle"></i></li>
        <li><span>Settings</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner sws-settings">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light">
                    <div class="portlet-title">
                        <div class="caption font-dark"><i class="icon-layers"></i><span class="caption-subject bold uppercase font-green-haze"> Site FOC Settings</span></div>
                    </div>
                    <div class="portlet-body">
                        <h3 class="font-green-haze">Categories <span class="pull-right"><button type="button" class="btn btn-circle btn-outline btn-sm green" data-toggle="modal" data-target="#modal_create_category"><i class="fa fa-plus"></i> Add option</button></span></h3>
                        <hr class="field-hr">

                        <div class="row hidden-sm hidden-xs">
                            <div class="col-md-1">#</div>
                            <div class="col-md-9"><strong>Name</strong></div>
                            <div class="col-md-2"></div>
                        </div>

                        <div class="sortable-list" id="foc-category-list">
                            @forelse ($cats as $cat)
                                <div class="sortable-item" data-category-id="{{ $cat->id }}" style="border-bottom: 1px solid #eee">
                                    <div class="row sortable-option-row">
                                        <div class="col-md-1 form-control-static">
                                            <span class="btn btn-link btn-xs font-grey-salsa sortable-handle" draggable="true" role="button" tabindex="0" title="Drag to reorder" aria-label="Drag {{ $cat->name }}"><i class="fa fa-bars"></i></span>
                                            <span data-sort-number>{{ $loop->iteration }}</span>
                                        </div>
                                        <div class="col-md-9"><span class="form-control-static">{{ $cat->name }}</span></div>
                                        <div class="col-md-2 text-right">
                                            <button type="button" class="btn blue btn-xs btn-outline sbold uppercase btn-edit-category" data-category-id="{{ $cat->id }}" data-category-name="{{ $cat->name }}"><i class="fa fa-pencil"></i> Edit</button>
                                            <button type="button" class="btn btn-link {{ $cat->in_use ? 'font-red' : 'font-dark' }} btn-remove-category" data-category-id="{{ $cat->id }}" data-category-name="{{ $cat->name }}" data-in-use="{{ $cat->in_use ? 1 : 0 }}"
                                                    title="{{ $cat->in_use ? 'Archive category' : 'Delete category' }}" aria-label="{{ $cat->in_use ? 'Archive' : 'Delete' }} {{ $cat->name }}"><i class="fa fa-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted">No active FOC categories.</div>
                            @endforelse
                        </div>

                        <br>
                        <div class="form-actions right"><a href="/site/foc" class="btn default">Back</a></div>
                    </div>
                </div>
            </div>
        </div>

        <x-ui.bootstrap-modal id="modal_create_category" title="Add FOC category" max-width="560px">
            <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteFocController::class, 'storeCategory']) }}" id="create-category-form">
                @csrf
                <x-form.hidden name="_category_form" value="create"/>
                <x-form.input name="create_name" id="create-category-name" label="Name" :value="old('_category_form') === 'create' ? old('create_name') : ''"/>
            </form>

            <x-slot name="footer">
                <button type="button" class="sws-modal-btn sws-modal-btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" form="create-category-form" class="sws-modal-btn sws-modal-btn-primary">Add category</button>
            </x-slot>
        </x-ui.bootstrap-modal>

        <x-ui.bootstrap-modal id="modal_edit_category" title="Edit FOC category" max-width="560px">
            <form method="POST" action="{{ url('/site/foc/settings/categories/0') }}" id="edit-category-form">
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

        <x-ui.bootstrap-modal id="modal_remove_category" title="Remove FOC category?" max-width="520px" footer-align="center">
            <div class="sws-confirm-content">
                <p class="sws-confirm-text" id="remove-category-message"></p>
                <span class="sws-confirm-item" id="remove-category-name"></span>
            </div>

            <x-slot name="footer">
                <button type="button" class="sws-modal-btn sws-modal-btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="sws-modal-btn sws-modal-btn-danger" id="confirm-remove-category">Remove category</button>
            </x-slot>
        </x-ui.bootstrap-modal>
    </div>
@stop

@section('page-level-scripts')
    <script type="text/javascript">
        $(function () {
            var categoryBaseUrl = @js(url('/site/foc/settings/categories'));
            var reorderUrl = @js(url('/site/foc/settings/categories/reorder'));
            var csrfToken = @js(csrf_token());
            var draggedItem = null;
            var pendingRemoveId = null;
            var pendingRemoveInUse = false;
            var categoryList = document.getElementById('foc-category-list');

            function openEditCategory(id, name) {
                $('#edit-category-id').val(id);
                $('#edit-category-name').val(name);
                $('#edit-category-form').attr('action', categoryBaseUrl + '/' + id);
                $('#modal_edit_category').modal('show');
            }

            function updateNumbers() {
                $('#foc-category-list [data-sort-number]').each(function (index) {
                    $(this).text(index + 1);
                });
            }

            function saveOrder() {
                var categoryIds = $('#foc-category-list > .sortable-item[data-category-id]').map(function () {
                    return Number($(this).attr('data-category-id'));
                }).get();

                $.ajax({url: reorderUrl, type: 'POST', dataType: 'json', data: {_token: csrfToken, category_ids: categoryIds}})
                    .done(function (data) {
                        toastr.success(data.message || 'Updated category order');
                    })
                    .fail(function (xhr) {
                        var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to update category order';
                        toastr.error(message);
                        window.setTimeout(function () {
                            window.location.reload();
                        }, 800);
                    });
            }

            if (categoryList) {
                categoryList.addEventListener('dragstart', function (event) {
                    var handle = event.target.closest('.sortable-handle');
                    if (!handle) return;
                    draggedItem = handle.closest('.sortable-item[data-category-id]');
                    draggedItem.style.opacity = '.45';
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', draggedItem.dataset.categoryId);
                });

                categoryList.addEventListener('dragover', function (event) {
                    if (!draggedItem) return;
                    event.preventDefault();
                    var target = event.target.closest('.sortable-item[data-category-id]');
                    if (!target || target === draggedItem) return;
                    var targetBox = target.getBoundingClientRect();
                    target.parentNode.insertBefore(draggedItem, event.clientY < targetBox.top + targetBox.height / 2 ? target : target.nextSibling);
                });

                categoryList.addEventListener('drop', function (event) {
                    if (!draggedItem) return;
                    event.preventDefault();
                    draggedItem.style.opacity = '';
                    draggedItem = null;
                    updateNumbers();
                    saveOrder();
                });

                categoryList.addEventListener('dragend', function () {
                    if (draggedItem) draggedItem.style.opacity = '';
                    draggedItem = null;
                });
            }

            $('.btn-edit-category').on('click', function () {
                openEditCategory($(this).attr('data-category-id'), $(this).attr('data-category-name'));
            });

            $('.btn-remove-category').on('click', function () {
                pendingRemoveId = $(this).attr('data-category-id');
                pendingRemoveInUse = $(this).attr('data-in-use') === '1';
                $('#remove-category-name').text($(this).attr('data-category-name'));
                $('#remove-category-message').text(pendingRemoveInUse ? 'This category is already used. It will be archived for new items while existing FOC items are retained.' : 'This category is not currently used and will be permanently deleted.');
                $('#confirm-remove-category').text(pendingRemoveInUse ? 'Archive category' : 'Delete category');
                $('#modal_remove_category').modal('show');
            });

            $('#confirm-remove-category').on('click', function () {
                if (!pendingRemoveId) return;
                var button = $(this);
                var originalLabel = button.text();
                button.prop('disabled', true).text(pendingRemoveInUse ? 'Archiving...' : 'Deleting...');

                $.ajax({url: categoryBaseUrl + '/' + pendingRemoveId, type: 'DELETE', dataType: 'json', data: {_token: csrfToken}})
                    .done(function (data) {
                        $('#modal_remove_category').modal('hide');
                        toastr.success(data.message || (pendingRemoveInUse ? 'Archived FOC category' : 'Deleted FOC category'));
                        $('#foc-category-list > .sortable-item[data-category-id="' + pendingRemoveId + '"]').remove();
                        updateNumbers();
                    })
                    .fail(function (xhr) {
                        var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to remove FOC category';
                        toastr.error(message);
                    })
                    .always(function () {
                        button.prop('disabled', false).text(originalLabel);
                    });
            });

            $('#modal_remove_category').on('hidden.bs.modal', function () {
                pendingRemoveId = null;
                pendingRemoveInUse = false;
                $('#remove-category-name, #remove-category-message').text('');
            });

            @if (old('_category_form') === 'create')
            $('#modal_create_category').modal('show');
            @elseif (old('_category_form') === 'edit')
            openEditCategory(@js(old('_category_id')), @js(old('edit_name')));
            @endif
        });
    </script>
@stop
