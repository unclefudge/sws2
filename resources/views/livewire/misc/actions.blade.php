<div x-on:sws-toastr.stop="toastr[$event.detail.type]($event.detail.message)">
    <div class="row">
        <div class="col-md-12">
            <h4 class="clearfix" style="margin-bottom: 10px">
                Notes

                <div class="pull-right">
                    @if ($actions->total() > 10)
                        <span wire:ignore style="display:inline-block; margin-right:8px; vertical-align:middle">
                            <select class="form-control bs-select input-sm" data-width="110px" x-init="if ($.fn.selectpicker && !$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('perPage', Number($el.value))">
                                @foreach ($perPageOptions as $option)
                                    <option value="{{ $option }}" {{ $perPage === $option ? 'selected' : '' }}>View {{ $option }}</option>
                                @endforeach
                            </select>
                        </span>
                    @endif

                    @if ($allowAdd)
                        <button type="button" wire:click="add" class="btn btn-circle green btn-outline btn-sm">Add</button>
                    @endif
                </div>
            </h4>
            <hr style="padding: 0; margin: 0 0 10px 0">

                @if ($actions->isNotEmpty())
                    <table class="table table-striped table-bordered table-nohover order-column">
                        <thead>
                        <tr class="mytable-header">
                            <th style="width:10%">Date</th>
                            <th>Action</th>
                            <th style="width:20%">Name</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach ($actions as $action)
                            <tr wire:key="action-{{ $action->id }}">
                                <td>{{ $action->created_at->format('d/m/y') }}</td>
                                <td>{{ $action->action }}</td>
                                <td>{{ $action->user?->fullname ?? $action->user?->full_name ?? 'Unknown' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    @if ($actions->hasPages())
                        @php
                            $currentPage = $actions->currentPage();
                            $lastPage = $actions->lastPage();
                            $pageFrom = max(1, $currentPage - 2);
                            $pageTo = min($lastPage, $currentPage + 2);
                        @endphp

                        <div class="row" style="margin-top:10px; margin-bottom:20px">
                            <div class="col-sm-6">
                                <div class="dataTables_info" style="padding-top:8px">Showing {{ $actions->firstItem() }} to {{ $actions->lastItem() }} of {{ $actions->total() }} notes</div>
                            </div>
                            <div class="col-sm-6 text-right">
                                <ul class="pagination" style="margin:0">
                                    <li class="{{ $actions->onFirstPage() ? 'disabled' : '' }}">
                                        @if ($actions->onFirstPage())
                                            <span>&laquo;</span>
                                        @else
                                            <a href="#" wire:click.prevent="previousPage('{{ $pageName }}')">&laquo;</a>
                                        @endif
                                    </li>

                                    @if ($pageFrom > 1)
                                        <li><a href="#" wire:click.prevent="gotoPage(1, '{{ $pageName }}')">1</a></li>
                                        @if ($pageFrom > 2)
                                            <li class="disabled"><span>…</span></li>
                                        @endif
                                    @endif

                                    @for ($page = $pageFrom; $page <= $pageTo; $page++)
                                        <li class="{{ $page === $currentPage ? 'active' : '' }}">
                                            @if ($page === $currentPage)
                                                <span>{{ $page }}</span>
                                            @else
                                                <a href="#" wire:click.prevent="gotoPage({{ $page }}, '{{ $pageName }}')">{{ $page }}</a>
                                            @endif
                                        </li>
                                    @endfor

                                    @if ($pageTo < $lastPage)
                                        @if ($pageTo < $lastPage - 1)
                                            <li class="disabled"><span>…</span></li>
                                        @endif
                                        <li><a href="#" wire:click.prevent="gotoPage({{ $lastPage }}, '{{ $pageName }}')">{{ $lastPage }}</a></li>
                                    @endif

                                    <li class="{{ $actions->hasMorePages() ? '' : 'disabled' }}">
                                        @if ($actions->hasMorePages())
                                            <a href="#" wire:click.prevent="nextPage('{{ $pageName }}')">&raquo;</a>
                                        @else
                                            <span>&raquo;</span>
                                        @endif
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="font-grey-silver">No notes.</div>
                @endif

        </div>
    </div>


    {{-- Add Note Modal --}}
    <x-ui.modal :show="$showModal" title="Add Note" close-action="close">
        <x-form.textarea name="note" label="Description" rows="4" wire:model="note" placeholder="Enter note description"/>

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="close">Cancel</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="save" wire:loading.attr="disabled" wire:target="save">Create</button>
        </x-slot>
    </x-ui.modal>
</div>
