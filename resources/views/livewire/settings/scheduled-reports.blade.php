<div class="client-scheduled-reports">
    <style>
        .client-scheduled-reports .csr-intro { margin:0 0 18px; color:#69747f; }
        .client-scheduled-reports .csr-flash { margin-bottom:15px; padding:12px 14px; border-left:4px solid #36a866; background:#e8f6ed; color:#267747; }
        .client-scheduled-reports .csr-flash-error { border-color:#e7505a; background:#fde7e9; color:#b83e48; }
        .client-scheduled-reports .csr-tools { display:flex; align-items:center; gap:8px; margin-bottom:14px; }
        .client-scheduled-reports .csr-search { flex:1; min-width:220px; }
        .client-scheduled-reports .csr-list { border:1px solid #dfe5e9; }
        .client-scheduled-reports .csr-row { display:grid; grid-template-columns:minmax(220px,1.4fr) minmax(150px,.8fr) minmax(260px,1.4fr) auto; gap:16px; align-items:center; padding:15px; border-top:1px solid #e7ebee; }
        .client-scheduled-reports .csr-row:first-child { border-top:0; }
        .client-scheduled-reports .csr-row-disabled > :not(.csr-actions) { opacity:.55; }
        .client-scheduled-reports .csr-name { color:#46515f; font-size:15px; font-weight:600; }
        .client-scheduled-reports .csr-description, .client-scheduled-reports .csr-recipients { margin-top:4px; color:#7a858f; font-size:12px; }
        .client-scheduled-reports .csr-state { display:inline-block; margin-left:7px; padding:3px 8px; border-radius:11px; font-size:10px; font-weight:700; text-transform:uppercase; }
        .client-scheduled-reports .csr-state-off { background:#f0f1f2; color:#747d85; }
        .client-scheduled-reports .csr-btn { padding:8px 12px; border:1px solid #ccd4da; border-radius:3px; background:#fff; color:#53606c; font-weight:600; }
        .client-scheduled-reports .csr-btn-primary { border-color:#36c6d3; background:#36c6d3; color:#fff; }
        .client-scheduled-reports .csr-btn-edit { border-color:#3598dc; color:#2785c2; }
        .client-scheduled-reports .csr-btn-edit:hover, .client-scheduled-reports .csr-btn-edit:focus { background:#3598dc; color:#fff; }
        .client-scheduled-reports .csr-sort-toggle { min-width:105px; white-space:nowrap; }
        .client-scheduled-reports .csr-sort-toggle.is-day { border-color:#36c6d3; background:#e8f8fa; color:#279aa5; }
        .client-scheduled-reports .csr-actions { display:flex; align-items:center; justify-content:flex-end; gap:8px; }
        .client-scheduled-reports .csr-bell { display:inline-flex; width:34px; height:34px; align-items:center; justify-content:center; padding:0; border:0; background:transparent; font-size:15px; }
        .client-scheduled-reports .csr-bell-enabled { color:#36c6d3; }
        .client-scheduled-reports .csr-bell-disabled { color:#e7505a; }
        .client-scheduled-reports .csr-bell:hover, .client-scheduled-reports .csr-bell:focus { background:#f1f4f6; outline:0; }
        .client-scheduled-reports .csr-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; }
        .client-scheduled-reports .csr-span-2 { grid-column:span 2; }
        .client-scheduled-reports label { color:#46515f; }
        .client-scheduled-reports .form-control { min-height:42px; border:1px solid #c9d2dc; border-radius:0; box-shadow:none; }
        .client-scheduled-reports .form-control:focus { border-color:#36c6d3; box-shadow:0 0 0 1px rgba(54,198,211,.15); }
        .client-scheduled-reports .csr-select-host .bootstrap-select { width:100% !important; }
        .client-scheduled-reports .csr-select-host .bootstrap-select > .dropdown-toggle { min-height:42px; border:1px solid #c9d2dc !important; border-radius:0; background:#fff !important; outline:0 !important; box-shadow:none !important; }
        .client-scheduled-reports .csr-select-host .bootstrap-select.open > .dropdown-toggle,
        .client-scheduled-reports .csr-select-host .bootstrap-select > .dropdown-toggle:focus { border-color:#36c6d3 !important; outline:0 !important; box-shadow:none !important; }
        .client-scheduled-reports .csr-select-host .bootstrap-select .dropdown-menu { z-index:100060; }
        .client-scheduled-reports .csr-status-toggle { position:relative; display:inline-block; flex:0 0 auto; margin:0; cursor:pointer; }
        .client-scheduled-reports .csr-status-toggle > input { position:absolute; width:1px; height:1px; opacity:0; }
        .client-scheduled-reports .csr-status-track { display:grid; grid-template-columns:1fr 1fr; width:190px; overflow:hidden; border:1px solid #ccd3d8; border-radius:5px; background:#edf0f2; }
        .client-scheduled-reports .csr-status-track span { padding:9px 13px; color:#7b858d; font-size:12px; font-weight:700; text-align:center; text-transform:uppercase; transition:background .12s ease,color .12s ease; }
        .client-scheduled-reports .csr-status-toggle > input:not(:checked) + .csr-status-track .csr-status-disabled { background:#e7505a; color:#fff; }
        .client-scheduled-reports .csr-status-toggle > input:checked + .csr-status-track .csr-status-enabled { background:#26a65b; color:#fff; }
        .client-scheduled-reports .csr-status-toggle > input:focus + .csr-status-track { box-shadow:0 0 0 3px rgba(54,198,211,.18); }
        .client-scheduled-reports .csr-days { display:inline-flex; width:100%; }
        .client-scheduled-reports .csr-days label { position:relative; flex:1; min-width:0; margin:0; cursor:pointer; }
        .client-scheduled-reports .csr-days input { position:absolute; width:1px; height:1px; opacity:0; }
        .client-scheduled-reports .csr-days span { display:flex; min-height:42px; align-items:center; justify-content:center; margin-left:-1px; padding:9px 6px; border:1px solid #ccd3d8; background:#e8ebed; color:#68737d; font-size:12px; font-weight:700; text-align:center; transition:background .12s ease,color .12s ease,border-color .12s ease; }
        .client-scheduled-reports .csr-days label:first-child span { margin-left:0; border-radius:5px 0 0 5px; }
        .client-scheduled-reports .csr-days label:last-child span { border-radius:0 5px 5px 0; }
        .client-scheduled-reports .csr-days input:checked + span { position:relative; z-index:1; border-color:#46515f; background:#46515f; color:#fff; }
        .client-scheduled-reports .csr-days input:focus + span { position:relative; z-index:2; box-shadow:0 0 0 3px rgba(54,198,211,.18); }
        .client-scheduled-reports .csr-dynamic { margin:16px 0; padding:13px 15px; border-left:4px solid #4f94c8; background:#edf5fb; }
        .client-scheduled-reports .csr-dynamic-row + .csr-dynamic-row { margin-top:7px; }
        .client-scheduled-reports .csr-dynamic small { display:block; color:#6e7e8b; }
        .client-scheduled-reports .csr-rules { margin-top:15px; }
        .client-scheduled-reports .csr-rule-actions { display:flex; align-items:center; justify-content:space-between; gap:15px; margin-top:11px; }
        .client-scheduled-reports .csr-rule { display:grid; grid-template-columns:90px 120px minmax(280px,1fr) auto; gap:8px; align-items:start; margin-top:9px; }
        .client-scheduled-reports .csr-user-select .select2-container { width:100% !important; }
        .client-scheduled-reports .csr-user-select .select2-selection--multiple { min-height:42px; border:1px solid #c9d2dc; border-radius:0; }
        .client-scheduled-reports .help-block, .client-scheduled-reports .csr-errors { color:#e7505a; font-size:12px; font-weight:600; }
        .client-scheduled-reports .csr-help { margin-top:6px; color:#7a858f; font-size:12px; }
        .client-scheduled-reports .csr-footer { display:flex; justify-content:flex-end; gap:9px; }
        .client-scheduled-reports .sws-modal-card { border:0; }
        .client-scheduled-reports .sws-modal-header { padding:18px 64px 18px 22px; background:#46515f; border-bottom:0; }
        .client-scheduled-reports .sws-modal-title, .client-scheduled-reports .sws-modal-close { color:#fff; }
        .client-scheduled-reports .sws-modal-close { top:16px; right:22px; width:38px; height:38px; border-radius:0; background:rgba(255,255,255,.12); font-size:20px; line-height:38px; }
        .client-scheduled-reports .sws-modal-close:hover, .client-scheduled-reports .sws-modal-close:focus { background:rgba(255,255,255,.22); color:#fff; }
        @media(max-width:800px) {
            .client-scheduled-reports .csr-row, .client-scheduled-reports .csr-rule { grid-template-columns:1fr; }
            .client-scheduled-reports .csr-tools { align-items:stretch; flex-direction:column; }
            .client-scheduled-reports .csr-search { width:100%; }
            .client-scheduled-reports .csr-actions { justify-content:flex-end; }
            .client-scheduled-reports .csr-grid { grid-template-columns:1fr; }
            .client-scheduled-reports .csr-span-2 { grid-column:auto; }
            .client-scheduled-reports .csr-rule-actions { align-items:stretch; flex-direction:column; }
            .client-scheduled-reports .csr-rule-actions .csr-status-toggle { align-self:flex-end; }
        }
    </style>

    <p class="csr-intro">Choose when each report runs and who receives it. SafeWorksite controls the run time and any report-specific recipients such as a site supervisor.</p>

    @if(session()->has('scheduled-reports-success'))
        <div class="csr-flash"><i class="fa fa-check-circle"></i> {{ session('scheduled-reports-success') }}</div>
    @endif
    @if(session()->has('scheduled-reports-error'))
        <div class="csr-flash csr-flash-error"><i class="fa fa-exclamation-triangle"></i> {{ session('scheduled-reports-error') }}</div>
    @endif

    <div class="csr-tools">
        <input type="search" class="form-control csr-search" placeholder="Search report, schedule or recipient" wire:model.live.debounce.300ms="reportSearch">
        <button type="button" class="csr-btn csr-sort-toggle {{ $reportSort === 'day' ? 'is-day' : '' }}" wire:click="toggleReportSort"
                title="Switch to {{ $reportSort === 'day' ? 'name' : 'day/schedule' }} order" aria-label="Currently sorted by {{ $reportSort === 'day' ? 'day and schedule' : 'name' }}; switch order">
            <i class="fa {{ $reportSort === 'day' ? 'fa-calendar' : 'fa-sort-alpha-asc' }}"></i> {{ $reportSort === 'day' ? 'Day order' : 'Name order' }}
        </button>
    </div>

    <div class="csr-list">
        @forelse($reports as $report)
            <div class="csr-row {{ !$report['enabled'] ? 'csr-row-disabled' : '' }}" wire:key="client-report-{{ $report['id'] }}">
                <div>
                    <div class="csr-name">
                        {{ $report['name'] }}
                        @unless($report['enabled'])<span class="csr-state csr-state-off">Disabled</span>@endunless
                    </div>
                    @if($report['description'])<div class="csr-description">{{ $report['description'] }}</div>@endif
                </div>
                <div><strong>{{ $report['schedule'] }}</strong></div>
                <div><strong>Recipients</strong><div class="csr-recipients">{{ $report['recipients'] }}</div></div>
                <div class="csr-actions">
                    <button type="button" class="csr-bell {{ $report['enabled'] ? 'csr-bell-enabled' : 'csr-bell-disabled' }}" wire:click="toggleReportEnabled({{ $report['id'] }})" wire:loading.attr="disabled" wire:target="toggleReportEnabled({{ $report['id'] }})"
                            title="{{ $report['enabled'] ? 'Disable' : 'Enable' }} {{ $report['name'] }}" aria-label="{{ $report['enabled'] ? 'Disable' : 'Enable' }} {{ $report['name'] }}">
                        <i class="fa {{ $report['enabled'] ? 'fa-bell' : 'fa-bell-slash' }}"></i>
                    </button>
                    <button type="button" class="csr-btn csr-btn-edit" wire:click="editReport({{ $report['id'] }})"><i class="fa fa-pencil"></i> Edit</button>
                </div>
            </div>
        @empty
            <div style="padding:18px" class="text-muted">
                {{ trim($reportSearch) !== '' ? 'No scheduled reports match your search.' : 'No converted reports are available yet. Reports appear here after their scheduled handler is installed and synchronised.' }}
            </div>
        @endforelse
    </div>

    <x-ui.modal :show="$showEditor" title="Scheduled report settings" close-action="closeEditor" max-width="900px" class="client-scheduled-reports-modal">
        @if($showEditor)
            <div class="csr-grid">
                <div class="csr-span-2"><h4 style="margin:0">{{ $reportName }}</h4></div>

                <div>
                    <label>Frequency</label>
                    <div class="csr-select-host" wire:key="client-report-frequency-{{ $definitionId }}-{{ $scheduleType }}" wire:ignore>
                        <select class="form-control bs-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('scheduleType', $el.value)">
                            <option value="daily" @selected($scheduleType === 'daily')>Daily</option>
                            <option value="weekdays" @selected($scheduleType === 'weekdays')>Every weekday</option>
                            <option value="weekly" @selected($scheduleType === 'weekly')>Weekly</option>
                            <option value="fortnightly" @selected($scheduleType === 'fortnightly')>Fortnightly</option>
                            <option value="monthly_nth_weekday" @selected($scheduleType === 'monthly_nth_weekday')>Monthly — selected weekday</option>
                            <option value="monthly_last_weekday" @selected($scheduleType === 'monthly_last_weekday')>Monthly — last weekday</option>
                            <option value="monthly_day" @selected($scheduleType === 'monthly_day')>Monthly — selected date</option>
                            <option value="quarterly" @selected($scheduleType === 'quarterly')>Quarterly</option>
                        </select>
                    </div>
                    @error('scheduleType')<span class="help-block">{{ $message }}</span>@enderror
                </div>

                @if($scheduleType === 'weekly')
                    <div>
                        <label>Run on</label>
                        <div class="csr-days" role="group" aria-label="Run on weekdays">
                            @foreach([1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri'] as $value => $label)
                                <label><input type="checkbox" value="{{ $value }}" wire:model="weekdays"><span>{{ $label }}</span></label>
                            @endforeach
                        </div>
                        @error('weekdays')<span class="help-block">{{ $message }}</span>@enderror
                    </div>
                @elseif(in_array($scheduleType, ['fortnightly','monthly_nth_weekday','monthly_last_weekday'], true))
                    <div>
                        <label>Day</label>
                        <div class="csr-select-host" wire:key="client-report-weekday-{{ $definitionId }}-{{ $weekday }}" wire:ignore>
                            <select class="form-control bs-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('weekday', Number($el.value))">
                                @foreach([1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'] as $value => $label)
                                    <option value="{{ $value }}" @selected((int)$weekday === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @elseif(in_array($scheduleType, ['monthly_day','quarterly'], true))
                    <div>
                        <label>Day of month</label>
                        <div class="csr-select-host" wire:key="client-report-month-day-{{ $definitionId }}-{{ $day }}" wire:ignore>
                            <select class="form-control bs-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('day', Number($el.value))">
                                @foreach(range(1,28) as $value)<option value="{{ $value }}" @selected((int)$day === $value)>{{ $value }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                @endif

                @if($scheduleType === 'monthly_nth_weekday')
                    <div>
                        <label>Occurrence</label>
                        <div class="csr-select-host" wire:key="client-report-occurrence-{{ $definitionId }}-{{ $occurrence }}" wire:ignore>
                            <select class="form-control bs-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('occurrence', Number($el.value))">
                                <option value="1" @selected((int)$occurrence === 1)>First</option><option value="2" @selected((int)$occurrence === 2)>Second</option><option value="3" @selected((int)$occurrence === 3)>Third</option><option value="4" @selected((int)$occurrence === 4)>Fourth</option><option value="5" @selected((int)$occurrence === 5)>Fifth</option>
                            </select>
                        </div>
                    </div>
                @endif
            </div>

            @if($dynamicRecipients)
                <div class="csr-dynamic">
                    <strong>Recipients selected automatically by this report</strong>
                    @foreach($dynamicRecipients as $recipient)
                        <div class="csr-dynamic-row">
                            <span class="label label-info">{{ strtoupper($recipient['delivery']) }}</span> {{ $recipient['label'] }}
                            @if($recipient['description'])<small>{{ $recipient['description'] }}</small>@endif
                        </div>
                    @endforeach
                    <div class="csr-help">These rules are protected because they depend on the records in each report. Add management recipients below; they are also used as the fallback when an automatic recipient cannot be found.</div>
                </div>
            @endif

            <div class="csr-rules">
                <strong>Additional recipients</strong>
                <div class="csr-help">Use User for Cape Cod staff or Email for an external address. Each email address has its own validated rule.</div>
                @error('recipientRules')<div class="csr-errors">{{ $message }}</div>@enderror

                @foreach($recipientRules as $index => $rule)
                    <div class="csr-rule" wire:key="client-recipient-rule-{{ $index }}-{{ $rule['source_type'] }}">
                        <div class="csr-select-host" wire:key="client-recipient-delivery-{{ $definitionId }}-{{ $index }}-{{ $rule['delivery_type'] }}" wire:ignore>
                            <select class="form-control bs-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('recipientRules.{{ $index }}.delivery_type', $el.value)">
                                <option value="to" @selected($rule['delivery_type'] === 'to')>To</option><option value="cc" @selected($rule['delivery_type'] === 'cc')>CC</option>
                            </select>
                        </div>
                        <div class="csr-select-host" wire:key="client-recipient-source-{{ $definitionId }}-{{ $index }}-{{ $rule['source_type'] }}" wire:ignore>
                            <select class="form-control bs-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('recipientRules.{{ $index }}.source_type', $el.value)">
                                <option value="user" @selected($rule['source_type'] === 'user')>User</option><option value="manual" @selected($rule['source_type'] === 'manual')>Email</option>
                            </select>
                        </div>
                        @if($rule['source_type'] === 'user')
                            <div class="csr-user-select" wire:ignore>
                                <select multiple class="form-control" style="width:100%"
                                        x-init="const parent = $($el).closest('.sws-modal-card'); $($el).select2({width:'100%', placeholder:'Select one or more users', dropdownParent:parent.length ? parent : $(document.body)}).on('change', function(){ $wire.set('recipientRules.{{ $index }}.source_value', $(this).val() || []); })">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" @selected(in_array((string)$user->id, array_map('strval', $rule['source_value'] ?? []), true))>{{ $user->fullname }} ({{ $user->company?->name_alias ?? 'Unknown company' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="email" class="form-control" wire:model="recipientRules.{{ $index }}.source_value" placeholder="person@example.com">
                        @endif
                        <button type="button" class="csr-btn font-red" title="Remove" wire:click="removeRecipientRule({{ $index }})"><i class="fa fa-trash"></i></button>
                        @error("recipientRules.$index.source_value")<span class="help-block" style="grid-column:3">{{ $message }}</span>@enderror
                    </div>
                @endforeach

                <div class="csr-rule-actions">
                    <button type="button" class="csr-btn" wire:click="addRecipientRule"><i class="fa fa-plus"></i> Add recipient</button>
                    <label class="csr-status-toggle">
                        <input type="checkbox" wire:model="enabled" aria-label="Enable scheduled report">
                        <span class="csr-status-track"><span class="csr-status-disabled">Disabled</span><span class="csr-status-enabled">Enabled</span></span>
                    </label>
                </div>
            </div>

            @error('report')<div class="csr-errors" style="margin-top:12px">{{ $message }}</div>@enderror
        @endif

        <x-slot name="footer">
            <div class="csr-footer">
                <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeEditor">Cancel</button>
                <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="saveReport">Save report</button>
            </div>
        </x-slot>
    </x-ui.modal>
</div>
