<div>
    @once
        <style>
            .safety-tip-table {
                margin-bottom:0;
            }

            .safety-tip-table > tbody > tr > td {
                background:#fff !important;
                vertical-align:middle !important;
            }

            .safety-tip-table > tbody > tr:hover > td {
                background:#f8fafb !important;
            }

            .safety-tip-active {
                width:38px;
                text-align:center;
            }

            .safety-tip-dot {
                border:0;
                background:transparent;
                padding:4px;
                line-height:1;
                cursor:default;
            }

            button.safety-tip-dot {
                cursor:pointer;
            }

            button.safety-tip-dot:hover,
            button.safety-tip-dot:focus {
                outline:0;
                transform:scale(1.08);
            }

            .safety-tip-dot-active {
                color:#36c6d3;
            }

            .safety-tip-dot-inactive {
                color:#dfe4e8;
            }

            .safety-tip-title {
                font-weight:600;
                color:#4b555f;
            }

            .safety-tip-body {
                color:#65717b;
                white-space:pre-line;
            }

            .safety-tip-current {
                margin-left:7px;
                color:#8b969f;
                font-size:12px;
                font-weight:400;
            }

            .safety-tip-actions {
                white-space:nowrap;
                text-align:center;
            }
        </style>
    @endonce

    @if ($message)
        <div class="alert alert-success" style="padding:8px 12px">{{ $message }}</div>
    @endif

    <div class="portlet light">
        <div class="portlet-title">
            <div class="caption">
                <i class="icon-layers"></i>
                <span class="caption-subject font-green-haze bold uppercase">Safety Tips</span>
            </div>

            <div class="actions">
                @if ($canAdd)
                    <button type="button" class="btn btn-circle green btn-outline btn-sm" wire:click="openAdd">Add</button>
                @endif
            </div>
        </div>

        <div class="portlet-body">
            @if ($tips->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-bordered table-nohover order-column safety-tip-table">
                        <thead>
                        <tr class="mytable-header">
                            <th style="width:45px"></th>
                            <th style="width:22%">Title</th>
                            <th>Tip</th>
                            <th style="width:145px">Last Published</th>
                            <th style="width:135px">Actions</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach ($tips as $tip)
                            @php
                                $canEdit = Auth::user()->allowed2('edit.safetytip', $tip);
                                $canDelete = Auth::user()->allowed2('del.safetytip', $tip);
                                $canPublish = $canEdit && $canDelete;
                            @endphp

                            <tr wire:key="safety-tip-{{ $tip->id }}">
                                <td class="safety-tip-active">
                                    @if ((int)$tip->status === 1)
                                        <span class="safety-tip-dot safety-tip-dot-active" title="Current published Safety Tip">
                                            <i class="fa fa-circle"></i>
                                        </span>
                                    @elseif ($canPublish)
                                        <button type="button" class="safety-tip-dot safety-tip-dot-inactive" wire:click="setActive({{ $tip->id }})" title="Publish this Safety Tip">
                                            <i class="fa fa-circle"></i>
                                        </button>
                                    @else
                                        <span class="safety-tip-dot safety-tip-dot-inactive">
                                            <i class="fa fa-circle"></i>
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <span class="safety-tip-title">{{ $tip->title }}</span>
                                    @if ((int)$tip->status === 1)
                                        <span class="safety-tip-current">Current</span>
                                    @endif
                                </td>

                                <td class="safety-tip-body">{{ $tip->body }}</td>

                                <td>
                                    {{ $tip->last_published ? $tip->last_published->format('d/m/y') : '—' }}
                                </td>

                                <td class="safety-tip-actions">
                                    @if ($canEdit)
                                        <button type="button" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom" wire:click="openEdit({{ $tip->id }})">
                                            <i class="fa fa-pencil"></i> Edit
                                        </button>
                                    @endif

                                    @if ($canDelete)
                                        <button type="button" class="btn dark btn-xs sbold margin-bottom" wire:click="confirmDelete({{ $tip->id }})" title="Delete Safety Tip">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="font-grey-silver" style="padding:10px 0">No Safety Tips.</div>
            @endif
        </div>
    </div>

    <x-ui.modal :show="$showTipModal" :title="$editingId ? 'Edit Safety Tip' : 'Add Safety Tip'" close-action="closeModals" max-width="650px">
        <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
            <label class="control-label">Title</label>
            <input type="text" class="form-control" wire:model="title" placeholder="Enter title">
            @error('title')<span class="help-block">{{ $message }}</span>@enderror
        </div>

        <div class="form-group {{ $errors->has('body') ? 'has-error' : '' }}">
            <label class="control-label">Tip</label>
            <textarea class="form-control" wire:model="body" rows="5" placeholder="Enter tip" style="padding:10px 12px"></textarea>
            @error('body')<span class="help-block">{{ $message }}</span>@enderror
        </div>

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Cancel</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                {{ $editingId ? 'Save' : 'Create' }}
            </button>
        </x-slot>
    </x-ui.modal>

    <x-ui.confirm-modal :show="$showDeleteModal" title="Delete Safety Tip" close-action="closeModals" confirm-action="deleteConfirmed" confirm-label="Yes, delete it">
        <div>
            Are you sure you want to permanently delete this Safety Tip?
            <div class="sws-confirm-item">{{ $deletingTitle }}</div>
        </div>
    </x-ui.confirm-modal>
</div>
