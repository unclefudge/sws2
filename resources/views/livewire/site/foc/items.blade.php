<div>
    <h4 style="margin-bottom: 15px">
        FOC Completion Items

        <div class="pull-right">
            <select wire:model.live="filter" class="form-control input-sm" style="width: 130px; display: inline-block; margin-right: 10px">
                <option value="all">All</option>
                <option value="completed">Completed</option>
                <option value="outstanding">Outstanding</option>
            </select>

            @if ($canAdd)
                <button type="button" class="btn btn-circle green btn-outline btn-sm" wire:click="openAdd">Add</button>
                <a href="/site/foc/{{ $foc->id }}/additems" class="btn btn-circle green btn-outline btn-sm" style="margin-left: 5px">Add Multiple</a>
            @endif
        </div>
    </h4>

    <hr style="padding: 0; margin: 0 0 10px 0">

    @if ($message)
        <div class="alert alert-success" style="padding: 8px 12px;">
            {{ $message }}
        </div>
    @endif

    @forelse ($categories as $category)
        @php
            $categoryItems = $items->where('category_id', $category->id);
        @endphp

        @if ($categoryItems->isNotEmpty())
            <table class="table table-striped table-bordered table-nohover order-column">
                <thead>
                <tr class="mytable-header">
                    <th style="width:5%"></th>
                    <th>{{ $category->name }}</th>
                    <th style="width:18%">Completed</th>

                    @if ($canEdit || $canDelete)
                        <th style="width:12%"></th>
                    @endif
                </tr>
                </thead>

                <tbody>
                @foreach ($categoryItems as $item)
                    <tr wire:key="foc-item-{{ $item->id }}">
                        <td class="text-center" style="padding-top: 15px">
                            @if ($item->sign_by)
                                <i class="fa fa-check-square-o font-green" style="font-size: 20px; padding-top: 5px"></i>
                            @else
                                <i class="fa fa-square-o font-red" style="font-size: 20px; padding-top: 5px"></i>
                            @endif
                        </td>

                        <td style="padding-top: 15px;">
                            {{ $item->name }}
                        </td>

                        <td>
                            @if ($item->sign_by)
                                {{ $item->sign_at?->format('d/m/Y') ?? '-' }}
                                <br>

                                {{ $signers->get($item->sign_by)?->full_name ?? 'Unknown' }}

                                @if ((int) $foc->status !== 0 && $canComplete)
                                    <button type="button" class="btn btn-link btn-xs" style="padding: 0 0 0 6px" wire:click="reopen({{ $item->id }})" title="Mark incomplete">
                                        <i class="fa fa-times font-red"></i>
                                    </button>
                                @endif
                            @else
                                @if ($canComplete)
                                    <button type="button" class="btn green btn-xs btn-outline" wire:click="markComplete({{ $item->id }})" wire:loading.attr="disabled" wire:target="markComplete({{ $item->id }})">
                                        Mark Complete
                                    </button>
                                @else
                                    <span class="font-grey-silver">Incomplete</span>
                                @endif
                            @endif
                        </td>

                        @if ($canEdit || $canDelete)
                            <td>
                                @if ($canEdit)
                                    <button type="button" class="btn btn-xs btn-outline blue" wire:click="openEdit({{ $item->id }})">
                                        <i class="fa fa-pencil"></i> Edit
                                    </button>
                                @endif

                                @if ($canDelete)
                                    <button type="button" class="btn btn-xs dark" wire:click="confirmDelete({{ $item->id }})">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    @empty
        <div class="font-grey-silver">No FOC item categories are configured.</div>
    @endforelse

    @if ($items->isEmpty() && $categories->isNotEmpty())
        <div class="font-grey-silver">
            {{ $filter === 'all' ? 'No FOC completion items.' : 'No ' . $filter . ' FOC completion items.' }}
        </div>
    @endif


    {{-- Add Item Modal --}}
    <x-ui.modal
            :show="$showAddModal"
            title="Add FOC Item"
            close-action="closeModals"
    >
        <div class="row" style="padding-bottom: 18px">
            <div class="col-md-6">
                <label class="control-label">Category</label>

                <select wire:model="categoryId" class="form-control">
                    <option value="">Select category</option>

                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                @error('categoryId')
                <span class="help-block font-red">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <label class="control-label">Item Description</label>

                <textarea wire:model="itemName" rows="4" class="form-control" placeholder="Specific details of FOC item"></textarea>

                @error('itemName')
                <span class="help-block font-red">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Cancel</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="saveAdd" wire:loading.attr="disabled" wire:target="saveAdd">Add Item</button>
        </x-slot>
    </x-ui.modal>

    {{-- Edit Item Modal --}}
    <x-ui.modal :show="$showEditModal" title="Edit FOC Item" close-action="closeModals">
        <div class="row" style="padding-bottom: 18px">
            <div class="col-md-12">
                <label class="control-label">Item Description</label>
                <textarea wire:model="itemName" rows="4" class="form-control"></textarea>

                @error('itemName')
                <span class="help-block font-red">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <label class="control-label">Category</label>
                <select wire:model="categoryId" class="form-control">
                    <option value="">Select category</option>

                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                @error('categoryId')
                <span class="help-block font-red">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Cancel</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="saveEdit" wire:loading.attr="disabled" wire:target="saveEdit">Save Changes</button>
        </x-slot>
    </x-ui.modal>

    {{-- Delete Item Modal --}}
    <x-ui.confirm-modal :show="$showDeleteModal" close-action="closeModals" confirm-action="deleteItem" confirm-label="Yes, delete it">
        <div><h3><strong>Are you sure?</strong></h3></div>
        <div>This item will be permanently deleted.</div>

        <div class="sws-confirm-item">
            {{ $itemName }}
        </div>
    </x-ui.confirm-modal>
</div>
