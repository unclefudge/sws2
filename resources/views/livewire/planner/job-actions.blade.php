<div class="planner-job-actions">
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
        <div class="planner-job-notice" x-data x-init="setTimeout(() => $wire.dismissNotice(), 5000)"><i class="fa fa-check-circle"></i> {{ $noticeMessage }} <button type="button" wire:click="dismissNotice" aria-label="Close message">&times;</button></div>
    @endif

    @if ($showModal)
        <div class="planner-job-modal" wire:key="planner-job-action-{{ $action }}" wire:click.self="closeModal">
            <div class="planner-job-dialog">
                <div class="planner-job-header">
                    <div><small>{{ $action === 'add' ? 'CREATE PLANNER' : ($action === 'move' ? 'MOVE PLANNER' : 'ALLOCATE SITE') }}</small><h2>{{ $action === 'add' ? 'Add Job Start' : ($action === 'move' ? 'Move Job Start' : 'Allocate Job') }}</h2></div>
                    <button type="button" wire:click="closeModal" aria-label="Close"><i class="fa fa-times"></i></button>
                </div>
                <div class="planner-job-body">
                    @if ($actionError)<div class="alert alert-danger">{{ $actionError }}</div>@endif

                    @if ($action === 'add')
                        <div class="planner-job-warning"><strong>This creates the complete Job Start schedule.</strong><br>Start Job and the 22 linked preset tasks will be added together.</div>
                    @elseif ($action === 'move')
                        <div class="planner-job-warning"><strong>Move the whole linked schedule unless you deliberately choose otherwise.</strong><br>All preset tasks keep their existing spacing.</div>
                    @else
                        <p class="planner-job-help">Choose the site and the supervisor who will manage it.</p>
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

                    <div class="planner-job-footer">
                        <button type="button" class="btn default" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn blue" wire:click="saveAction" wire:loading.attr="disabled" wire:target="saveAction"><i class="fa {{ $action === 'allocate' ? 'fa-user' : ($action === 'move' ? 'fa-exchange' : 'fa-plus') }}"></i> {{ $action === 'add' ? 'Add Job Start schedule' : ($action === 'move' ? 'Move Job Start' : 'Allocate Job') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <x-ui.confirm-modal :show="$showMoveConfirmModal" title="Move Job Start schedule?" :confirm-label="($moveConfirmation['scope'] ?? 'linked') === 'only' ? 'Yes, move marker' : 'Yes, move schedule'" confirm-action="confirmMoveJobStart" close-action="closeMoveConfirmModal">
        @if ($moveConfirmation)
            <div>Check this move before the planner is updated.</div>
            <div class="sws-confirm-item"><strong>{{ $moveConfirmation['site'] }}</strong><br>{{ $moveConfirmation['from'] }} &rarr; {{ $moveConfirmation['to'] }}</div>
            @if ($moveConfirmation['scope'] === 'linked')
                <div class="note note-warning" style="margin:14px 0 0"><strong>The complete linked schedule will move.</strong><br>Preset spacing is preserved where possible. If the supervisor pre-start check would fall before today, it is placed on the first available workday instead.</div>
            @else
                <div class="note note-warning" style="margin:14px 0 0"><strong>Only the Job Start marker will move.</strong><br>All other linked preset tasks will stay on their current dates.</div>
            @endif
        @endif
    </x-ui.confirm-modal>

    @once
        <style>
            [x-cloak]{display:none!important}.planner-job-menu{position:relative;clear:both;padding-top:8px;text-align:right}.planner-job-dropdown{position:absolute;z-index:1050;right:0;top:52px;width:190px;background:#fff;border:1px solid #d7dce2;box-shadow:0 5px 14px rgba(0,0,0,.18);text-align:left}.planner-job-dropdown:before{content:"";position:absolute;right:17px;top:-8px;width:14px;height:14px;background:#fff;border-left:1px solid #d7dce2;border-top:1px solid #d7dce2;transform:rotate(45deg)}.planner-job-dropdown button{position:relative;display:block;width:100%;padding:11px 15px;border:0;background:#fff;text-align:left;color:#333}.planner-job-dropdown button:hover{background:#f0f3f6}.planner-job-modal{position:fixed;z-index:10060;inset:0;display:flex;align-items:flex-start;justify-content:center;padding:24px;background:rgba(25,35,45,.64);overflow:auto}.planner-job-dialog{width:min(820px,100%);margin:auto;background:#fff;border:0!important;border-radius:16px!important;box-shadow:0 18px 50px rgba(0,0,0,.3);overflow:hidden}.planner-job-header{display:flex;justify-content:space-between;align-items:flex-start;padding:24px 30px;background:#465463;color:#fff;border-radius:16px 16px 0 0}.planner-job-header small{display:block;font-weight:700}.planner-job-header h2{margin:4px 0 0;color:#fff;font-size:32px}.planner-job-header>button{width:48px;height:48px;border:0;background:#5b6876;color:#fff;font-size:18px}.planner-job-body{padding:28px 32px}.planner-job-warning{margin-bottom:24px;padding:16px 18px;background:#fff8dc;border-left:4px solid #f1c40f;color:#655b33}.planner-job-help{margin-bottom:22px}.planner-job-body .form-group{margin-bottom:20px}.planner-job-datepicker .form-control,.planner-job-datepicker .btn{height:46px}.planner-job-footer{display:flex;justify-content:flex-end;gap:8px;margin-top:26px;padding-top:20px;border-top:1px solid #e6e9ed}.planner-job-footer .btn{min-height:44px}.planner-job-notice{position:fixed;z-index:10100;top:28px;left:50%;transform:translateX(-50%);max-width:650px;padding:15px 18px;background:#27ae60;color:#fff;box-shadow:0 8px 24px rgba(0,0,0,.25);font-size:16px}.planner-job-notice button{float:right;margin-left:20px;border:0;background:transparent;color:#fff;font-size:22px;line-height:18px}.datepicker{z-index:10090!important}@media(max-width:767px){.planner-job-modal{padding:8px}.planner-job-header,.planner-job-body{padding:20px}.planner-job-header h2{font-size:25px}.planner-job-footer{flex-direction:column-reverse}.planner-job-footer .btn{width:100%}}
        </style>
    @endonce
</div>
