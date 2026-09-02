<div class="planner-job-actions" x-on:sws-toastr.stop="toastr[$event.detail.type]($event.detail.message)">
    @include('livewire.planner.partials.sticky-controls')
    @if ($showMenu && $canEdit)
        <div class="planner-job-menu" x-data="{ open: false }" x-on:click.outside="open = false">
            <button type="button" class="btn btn-circle green" x-on:click="open = !open" aria-haspopup="true" x-bind:aria-expanded="open"><i class="fa fa-cog"></i> Actions <i class="fa fa-angle-down"></i></button>
            <div class="planner-job-dropdown" x-show="open" x-cloak>
                @if ($canManage)<button type="button" wire:click="openAction('add')" x-on:click="open = false">Add Job Start</button>@endif
                <button type="button" wire:click="openAction('move')" x-on:click="open = false">Move Job Start</button>
                @if ($canManage)<button type="button" wire:click="openAction('allocate')" x-on:click="open = false">Allocate Job</button>@endif
            </div>
        </div>
    @endif

    @if ($noticeMessage)
        <span class="sr-only" role="status" wire:key="planner-job-notice-{{ md5($noticeMessage) }}" x-data x-init="toastr.success(@js($noticeMessage)); setTimeout(() => $wire.dismissNotice(), 5000)">{{ $noticeMessage }}</span>
    @endif

    <x-ui.modal :show="$showModal" :title="$action === 'add' ? 'Add Job Start' : ($action === 'move' ? 'Move Job Start' : 'Allocate Job')" close-action="closeModal" max-width="820px" class="planner-job-action-modal">
        @if ($showModal)
                    <p class="font-grey-salsa bold uppercase">{{ $action === 'add' ? 'Create planner' : ($action === 'move' ? 'Move planner' : 'Allocate site') }}</p>
                    @if ($actionError)<div class="alert alert-danger">{{ $actionError }}</div>@endif

                    @if ($action === 'add')
                        <div class="note note-warning"><strong>This creates the complete Job Start schedule.</strong><br>Start Job and the 22 linked preset tasks will be added together.</div>
                    @elseif ($action === 'move')
                        <div class="note note-warning"><strong>Move the whole linked schedule unless you deliberately choose otherwise.</strong><br>All preset tasks keep their existing spacing.</div>
                    @else
                        <p class="help-block">Choose the site and the supervisor who will manage it.</p>
                    @endif

                    <div wire:ignore wire:key="planner-job-site-{{ $action }}-{{ count($siteOptions) }}">
                        <x-form.select name="plannerJobSite" label="Site" placeholder="Select site" :value="$selectedSiteId" data-width="100%" data-live-search="true" data-container="body" data-size="8" x-init="if ($.fn.selectpicker && !$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('selectedSiteId', $el.value)">
                            @foreach ($siteOptions as $site)<option value="{{ $site['id'] }}" @selected((int)$selectedSiteId === (int)$site['id'])>{{ $site['name'] }}</option>@endforeach
                        </x-form.select>
                    </div>

                    @if (in_array($action, ['add', 'move'], true))
                        <div wire:ignore wire:key="planner-job-date-{{ $action }}-{{ $jobDate }}" x-data x-init="
                            $nextTick(() => {
                                const picker = $($refs.picker);
                                if (!$.fn.datepicker) return;
                                if (picker.data('datepicker')) picker.datepicker('destroy');
                                picker.datepicker({ rtl: typeof App !== 'undefined' ? App.isRTL() : false, orientation:'auto', autoclose:true, container:'body', format:'dd/mm/yyyy', startDate:'today', daysOfWeekDisabled:[0,6], datesDisabled:@js($publicHolidayDates) });
                                @if ($jobDate) const parts = @js($jobDate).split('-'); if (parts.length === 3) picker.datepicker('update', parts[2] + '/' + parts[1] + '/' + parts[0]); @endif
                                picker.off('changeDate.plannerJob').on('changeDate.plannerJob', event => {
                                    if (!event.date) return;
                                    const pad = value => String(value).padStart(2, '0');
                                    $wire.set('jobDate', event.date.getFullYear() + '-' + pad(event.date.getMonth() + 1) + '-' + pad(event.date.getDate()));
                                });
                            });
                        ">
                            <label for="plannerJobDate" class="control-label">{{ $action === 'add' ? 'Job Start date' : 'Move Job Start to' }}</label>
                            <div class="input-group date planner-job-datepicker" x-ref="picker"><input type="text" id="plannerJobDate" class="form-control" placeholder="Select date" readonly><span class="input-group-btn"><button type="button" class="btn default date-set" aria-label="Choose date"><i class="fa fa-calendar"></i></button></span></div>
                        </div>
                    @endif

                    @if ($action === 'move')
                        <div wire:ignore wire:key="planner-job-scope-{{ $moveScope }}">
                            <x-form.select name="plannerJobScope" label="Which tasks?" :value="$moveScope" data-width="100%" data-container="body" x-init="if ($.fn.selectpicker && !$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('moveScope', $el.value)">
                                <option value="linked" @selected($moveScope === 'linked')>Job Start and all linked tasks</option>
                                <option value="only" @selected($moveScope === 'only')>Only the Job Start marker</option>
                            </x-form.select>
                        </div>
                    @elseif ($action === 'allocate')
                        <div wire:ignore wire:key="planner-job-supervisor-{{ count($supervisorOptions) }}">
                            <x-form.select name="plannerJobSupervisor" label="Supervisor" placeholder="Select supervisor" :value="$selectedSupervisorId" data-width="100%" data-live-search="true" data-container="body" data-size="8" x-init="if ($.fn.selectpicker && !$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('selectedSupervisorId', $el.value)">
                                @foreach ($supervisorOptions as $supervisor)<option value="{{ $supervisor['id'] }}" @selected((int)$selectedSupervisorId === (int)$supervisor['id'])>{{ $supervisor['name'] }}</option>@endforeach
                            </x-form.select>
                        </div>
                    @endif

        @endif
        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModal">Cancel</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="saveAction" wire:loading.attr="disabled" wire:target="saveAction"><i class="fa {{ $action === 'allocate' ? 'fa-user' : ($action === 'move' ? 'fa-exchange' : 'fa-plus') }}"></i> {{ $action === 'add' ? 'Add Job Start schedule' : ($action === 'move' ? 'Move Job Start' : 'Allocate Job') }}</button>
        </x-slot>
    </x-ui.modal>

    <x-ui.confirm-modal :show="$showMoveConfirmModal" title="Move Job Start schedule?" :confirm-label="($moveConfirmation['scope'] ?? 'linked') === 'only' ? 'Yes, move marker' : 'Yes, move schedule'" confirm-action="confirmMoveJobStart" close-action="closeMoveConfirmModal">
        @if ($moveConfirmation)
            <div>Check this move before the planner is updated.</div>
            <div class="sws-confirm-item"><strong>{{ $moveConfirmation['site'] }}</strong><br>{{ $moveConfirmation['from'] }} &rarr; {{ $moveConfirmation['to'] }}</div>
            @if ($moveConfirmation['scope'] === 'linked')
                <div class="note note-warning planner-confirm-warning"><strong>The complete linked schedule will move.</strong><br>Preset spacing is preserved where possible. If the supervisor pre-start check would fall before today, it is placed on the first available workday instead.</div>
            @else
                <div class="note note-warning planner-confirm-warning"><strong>Only the Job Start marker will move.</strong><br>All other linked preset tasks will stay on their current dates.</div>
            @endif
        @endif
    </x-ui.confirm-modal>
</div>
