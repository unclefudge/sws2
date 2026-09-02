<div x-on:sws-toastr.stop="toastr[$event.detail.type]($event.detail.message)">
    <style>
        .qa-items-table tbody tr:nth-child(odd) > td { background: #fff !important; }
        .qa-items-table tbody tr:nth-child(even) > td { background: #f6f6f6 !important; }
    </style>

    @if ($rows->isNotEmpty())
        <table class="table table-bordered table-nohover order-column qa-items-table">
            <thead>
            <tr class="mytable-header">
                <th style="width:5%"></th>
                <th>Maintenance Item</th>
                <th style="width:15%">Checked Date</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($rows as $row)
                <tr class="{{ $row['status'] === -1 ? 'font-grey-silver' : '' }}" wire:key="qa-item-{{ $row['id'] }}">
                    <td class="text-center" style="padding-top:15px">
                        @if ($qa->master)
                            {{ $row['order'] }}.
                        @elseif ($row['status'] === -1)
                            N/A
                        @elseif ($row['sign_by'])
                            <i class="fa fa-check-square-o font-green" style="font-size:20px; padding-top:5px"></i>
                        @else
                            <i class="fa fa-square-o font-red" style="font-size:20px; padding-top:5px"></i>
                        @endif
                    </td>

                    <td style="padding-top:15px">
                        {{ $row['name'] }} <span class="font-grey-silver">({{ $row['task_code'] }})</span>

                        @if ($row['done_by'])
                            <div>
                                @if ($row['status'] === 0)
                                    <small>
                                        @if ($row['can_open_company'])
                                            <a href="#" wire:click.prevent="openCompany({{ $row['id'] }})">{{ $row['done_by_company'] }} (licence. {{ $row['done_by_licence'] }})</a>
                                        @else
                                            {{ $row['done_by_company'] }} (licence. {{ $row['done_by_licence'] }})
                                        @endif
                                    </small>
                                @elseif ($row['status'] === 1)
                                    <small>
                                        {{ $row['done_by_company'] }} (licence. {{ $row['done_by_licence'] }})
                                        @if ($row['can_open_company'])
                                            &nbsp;<a href="#" wire:click.prevent="openCompany({{ $row['id'] }})"><i class="fa fa-pencil-square-o font-blue"> Edit</i></a>
                                        @endif
                                    </small>
                                @endif
                            </div>
                        @else
                            <div>
                                @if (!$qa->master && !$row['super'] && $row['status'] === 0 && $row['can_open_company'])
                                    <small><a href="#" wire:click.prevent="openCompany({{ $row['id'] }})">Assign company</a></small>
                                @elseif (!$qa->master && $row['super'] && $row['status'] === 0)
                                    <small>To be completed by Supervisor</small>
                                @elseif (!$qa->master && $row['super'] && $row['status'] === 1)
                                    <small>{{ $row['sign_by_name'] }}</small>
                                @endif
                            </div>
                        @endif
                    </td>

                    <td>
                        @if (!$qa->master)
                            @if ($row['sign_by'])
                                {{ $row['sign_at']?->format('d/m/Y') }}<br>{{ $row['sign_by_name'] }}
                                @if ((int)$qa->status !== 0 && !$qa->isSigned())
                                    <a href="#" wire:click.prevent="resetStatus({{ $row['id'] }})"><i class="fa fa-times font-red"></i></a>
                                @endif
                            @elseif ($canUpdateStatus)
                                <div wire:ignore>
                                    <select class="form-control bs-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="if ($el.value) $wire.updateStatus({{ $row['id'] }}, $el.value)">
                                        <option value="">Select Action</option>
                                        @if ($row['done_by'] || $row['super'])
                                            <option value="1">Sign Off</option>
                                        @endif
                                        <option value="-1">Mark N/A</option>
                                    </select>
                                </div>
                            @endif
                        @else
                            <div class="text-center">
                                @if ($row['super'])
                                    <i class="fa fa-check-square-o" style="font-size:20px; padding-top:5px"></i>
                                @else
                                    <i class="fa fa-square-o" style="font-size:20px; padding-top:5px"></i>
                                @endif
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <x-ui.modal :show="$showCompanyModal" title="Update Item Company" close-action="closeCompanyModal">
        <p><b>{{ $editingItemName }}</b></p>

        <div class="row" style="padding-bottom:10px">
            <div class="col-md-7">
                <div class="form-group {{ $errors->has('doneBy') ? 'has-error' : '' }}">
                    <label class="control-label">Completed by</label>
                    <select class="form-control" wire:model.live="doneBy">
                        <option value="">Select company</option>
                        @foreach ($companyOptions as $companyId => $companyName)
                            <option value="{{ $companyId }}">{{ $companyName }}</option>
                        @endforeach
                    </select>
                    @error('doneBy')<span class="help-block">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="col-md-5">
                <div class="form-group {{ $errors->has('doneByAll') ? 'has-error' : '' }}">
                    <label class="control-label">Assign to all unassigned</label>
                    <select class="form-control" wire:model="doneByAll">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                    @error('doneByAll')<span class="help-block">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        @if ((string)$doneBy === '1')
            <div class="row" style="padding-bottom:10px">
                <div class="col-md-7">
                    <div class="form-group {{ $errors->has('doneByOther') ? 'has-error' : '' }}">
                        <label class="control-label">Specify other company</label>
                        <input type="text" class="form-control" wire:model.live="doneByOther">
                        @error('doneByOther')<span class="help-block">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
        @endif

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeCompanyModal">No</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="saveCompany" wire:loading.attr="disabled" wire:target="saveCompany" @disabled(!$doneBy || ((string)$doneBy === '1' && trim($doneByOther) === ''))>Save</button>
        </x-slot>
    </x-ui.modal>
</div>
