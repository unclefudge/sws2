<div>
    @if ($message)
        <div class="alert alert-success" style="padding:8px 12px">{{ $message }}</div>
    @endif

    <div class="row" style="margin-bottom:15px">
        <div class="col-md-3">
            <div wire:ignore>
                <select class="form-control bs-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('reason', $el.value)">
                    @foreach ($reasons as $reasonId => $reasonName)
                        <option value="{{ $reasonId }}" {{ (string)$reason === (string)$reasonId ? 'selected' : '' }}>{{ $reasonName }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-2">
            @if ($reason === '1')
                <label class="mt-checkbox mt-checkbox-outline" style="margin-top:7px">Resolved
                    <input type="checkbox" wire:model.live="status">
                    <span></span>
                </label>
            @endif
        </div>

        <div class="col-md-5 col-md-offset-2">
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                <input type="text" class="form-control" wire:model.live.debounce.350ms="search" placeholder="Search compliance">
            </div>
        </div>
    </div>

    <div class="clearfix" style="margin-bottom:10px">
        @if ($reason === '' && !$status)
            <h4 class="font-red pull-left" style="margin:5px 0 0">Not Logged in Users</h4>
        @endif

        <div class="pull-right">
            @if ($records->total() > 10)
                <span wire:ignore style="display:inline-block; vertical-align:middle">
                    <select class="form-control bs-select input-sm" data-width="110px" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('perPage', Number($el.value))">
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}" {{ $perPage === $option ? 'selected' : '' }}>View {{ $option }}</option>
                        @endforeach
                    </select>
                </span>
            @endif
        </div>
    </div>

    <div wire:loading.delay class="font-grey-salsa" style="margin-bottom:8px">
        <i class="fa fa-spinner fa-pulse"></i> Updating...
    </div>

    @if ($records->count())
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-nohover order-column">
                <thead>
                <tr class="mytable-header">
                    <th style="width:10%">
                        <a href="#" class="mytable-header-link" wire:click.prevent="sortBy('date')">Date</a>
                        @if ($sortKey === 'date')<i class="fa fa-caret-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>@endif
                    </th>
                    <th style="width:20%">
                        <a href="#" class="mytable-header-link" wire:click.prevent="sortBy('site_name')">Site</a>
                        @if ($sortKey === 'site_name')<i class="fa fa-caret-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>@endif
                    </th>
                    <th>
                        <a href="#" class="mytable-header-link" wire:click.prevent="sortBy('user_name')">Name</a>
                        @if ($sortKey === 'user_name')<i class="fa fa-caret-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>@endif
                    </th>
                    <th>
                        <a href="#" class="mytable-header-link" wire:click.prevent="sortBy('user_company')">Company</a>
                        @if ($sortKey === 'user_company')<i class="fa fa-caret-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>@endif
                    </th>
                    <th style="width:20%">
                        <a href="#" class="mytable-header-link" wire:click.prevent="sortBy('site_supers')">Supervisor</a>
                        @if ($sortKey === 'site_supers')<i class="fa fa-caret-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>@endif
                    </th>
                    @if ($canEdit)
                        <th style="width:90px">Actions</th>
                    @endif
                </tr>
                </thead>
                <tbody>
                @foreach ($records as $comply)
                    @php
                        $rowClass = $comply->user_nc > 4 ? 'font-red' : ($comply->user_nc > 2 ? 'font-yellow-gold' : '');
                    @endphp
                    <tr class="{{ $rowClass }}" wire:key="compliance-row-{{ $comply->id }}">
                        <td>{{ $comply->date?->format('d/m/Y') }}</td>
                        <td>{{ $comply->site_name }}</td>
                        <td>{{ $comply->user_name }}</td>
                        <td>{{ $comply->user_company }}</td>
                        <td>{{ $comply->site_supers }}</td>
                        @if ($canEdit)
                            <td>
                                <button type="button" wire:click="openEdit({{ $comply->id }})" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom">
                                    <i class="fa fa-pencil"></i> <span class="hidden-xs hidden-sm">Edit</span>
                                </button>
                            </td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        @if ($records->hasPages())
            @php
                $currentPage = $records->currentPage();
                $lastPage = $records->lastPage();
                $pageFrom = max(1, $currentPage - 2);
                $pageTo = min($lastPage, $currentPage + 2);
            @endphp

            <div class="row" style="margin-top:10px; margin-bottom:20px">
                <div class="col-sm-6">
                    <div class="dataTables_info" style="padding-top:8px">Showing {{ $records->firstItem() }} to {{ $records->lastItem() }} of {{ $records->total() }} records</div>
                </div>
                <div class="col-sm-6 text-right">
                    <ul class="pagination" style="margin:0">
                        <li class="{{ $records->onFirstPage() ? 'disabled' : '' }}">
                            @if ($records->onFirstPage())
                                <span>&laquo;</span>
                            @else
                                <a href="#" wire:click.prevent="previousPage('compliancePage')">&laquo;</a>
                            @endif
                        </li>

                        @if ($pageFrom > 1)
                            <li><a href="#" wire:click.prevent="gotoPage(1, 'compliancePage')">1</a></li>
                            @if ($pageFrom > 2)
                                <li class="disabled"><span>…</span></li>
                            @endif
                        @endif

                        @for ($page = $pageFrom; $page <= $pageTo; $page++)
                            <li class="{{ $page === $currentPage ? 'active' : '' }}">
                                @if ($page === $currentPage)
                                    <span>{{ $page }}</span>
                                @else
                                    <a href="#" wire:click.prevent="gotoPage({{ $page }}, 'compliancePage')">{{ $page }}</a>
                                @endif
                            </li>
                        @endfor

                        @if ($pageTo < $lastPage)
                            @if ($pageTo < $lastPage - 1)
                                <li class="disabled"><span>…</span></li>
                            @endif
                            <li><a href="#" wire:click.prevent="gotoPage({{ $lastPage }}, 'compliancePage')">{{ $lastPage }}</a></li>
                        @endif

                        <li class="{{ $records->hasMorePages() ? '' : 'disabled' }}">
                            @if ($records->hasMorePages())
                                <a href="#" wire:click.prevent="nextPage('compliancePage')">&raquo;</a>
                            @else
                                <span>&raquo;</span>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        @endif
    @else
        <div class="font-grey-silver" style="padding:15px 0">No compliance records found.</div>
    @endif

    <x-ui.modal :show="$showEditModal" title="Edit Compliance" close-action="closeEdit" max-width="600px">
        <h3 style="margin:0">{{ $editUserName }}<br>
            <small style="color:#666">{{ $editCompanyName }}</small>
        </h3>

        <hr style="margin:10px 0">

        <div class="row" style="line-height:2">
            <div class="col-xs-3"><b>Date:</b></div>
            <div class="col-xs-9">{{ $editDate }}</div>
            <div class="col-xs-3"><b>Site:</b></div>
            <div class="col-xs-9">{{ $editSiteName }} <small class="font-grey-silver">({{ $editSiteId }})</small></div>
        </div>

        <div class="form-group" style="margin-top:12px">
            <label class="control-label">Reason</label>
            <div wire:ignore wire:key="compliance-edit-reason-{{ $editingId }}">
                <select class="form-control bs-select" data-width="100%" data-container="body" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('editReason', $el.value)">
                    @foreach ($reasons as $reasonId => $reasonName)
                        <option value="{{ $reasonId }}" {{ (string)$editReason === (string)$reasonId ? 'selected' : '' }}>{{ $reasonName }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if ($editReason === '1' && $editStatus)
            <div class="font-red" style="margin-bottom:10px"><b>RESOLVED {{ $editResolvedAt }}</b></div>
        @endif

        @if ($editUserNc > 2)
            <div x-data="{ expanded: false }">
                <div class="font-red" style="margin-bottom:6px">
                    <b>Non Compliant Dates ({{ $editUserNc }})</b>
                </div>

                <div class="row" style="margin-left:-4px; margin-right:-4px; margin-bottom:4px">
                    @foreach ($editUserNcDates as $date)
                        <div class="col-xs-6 col-sm-3" style="padding-left:4px; padding-right:4px; margin-bottom:6px" @if ($loop->index >= 8) x-show="expanded" x-cloak @endif>
                            <div style="border:1px solid #d9dde3; padding:5px 8px; background:#fafafa">{{ $date }}</div>
                        </div>
                    @endforeach
                </div>

                @if ($editUserNc > 8)
                    <div style="margin-bottom:10px">
                        <a href="#" x-on:click.prevent="expanded = !expanded" x-text="expanded ? 'Show less' : 'Show all (' + {{ $editUserNc }} + ')'"></a>
                    </div>
                @endif
            </div>
        @endif

        <div class="form-group {{ $errors->has('editNotes') ? 'has-error' : '' }}">
            <label class="control-label">
                Notes
                @if ($editReason === '1' && !$editStatus)
                    <span class="font-red">*required to resolve</span>
                @endif
            </label>
            <textarea wire:model="editNotes" rows="4" class="form-control" style="padding:10px" placeholder="Enter notes"></textarea>
            @error('editNotes')<span class="help-block">{{ $message }}</span>@enderror
        </div>

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeEdit">Cancel</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="save" wire:loading.attr="disabled" wire:target="save">Save</button>
            @if ($editReason === '1' && !$editStatus && trim($editNotes) !== '')
                <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="resolve" wire:loading.attr="disabled" wire:target="resolve">Resolve</button>
            @endif
        </x-slot>
    </x-ui.modal>

    <x-ui.modal :show="$showSameCompanyModal" title="Confirm Multiple Contractors" close-action="closeEdit" max-width="560px" footer-align="center">
        <p>There are other contractors from the same company that also didn't log in on {{ $editDate }} at {{ $editSiteName }}.</p>

        @if ($sameCompanyNames)
            <p><b>{{ implode(', ', $sameCompanyNames) }}</b></p>
        @endif

        <p>Would you like to save them all with the same reason <b>{{ $sameReasonName }}</b>?</p>

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="saveSameCompany(false)" wire:loading.attr="disabled">No</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="saveSameCompany(true)" wire:loading.attr="disabled">Yes</button>
        </x-slot>
    </x-ui.modal>
</div>
