<div class="scheduled-ops" wire:poll.15s>
    <style>
        .scheduled-ops .ops-title-row { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:18px; }
        .scheduled-ops .ops-title-row h2 { margin:0; color:#46515f; font-weight:600; }
        .scheduled-ops .ops-mode { padding:7px 12px; border-radius:16px; font-size:12px; font-weight:700; text-transform:uppercase; }
        .scheduled-ops .ops-mode-legacy { background:#fff4d4; color:#8a6d1f; }
        .scheduled-ops .ops-mode-shadow { background:#e9f2fb; color:#3977a8; }
        .scheduled-ops .ops-mode-live { background:#e5f6ec; color:#267747; }
        .scheduled-ops .ops-banner { margin-bottom:20px; padding:14px 17px; border-left:4px solid #36c6d3; background:#f4f8fa; color:#5d6874; }
        .scheduled-ops .ops-heartbeat { display:block; margin-top:7px; font-size:12px; }
        .scheduled-ops .ops-heartbeat-ok { color:#267747; }
        .scheduled-ops .ops-heartbeat-warning { color:#b83e48; font-weight:600; }
        .scheduled-ops .ops-stats { display:grid; grid-template-columns:repeat(4, minmax(130px,1fr)); gap:12px; margin-bottom:22px; }
        .scheduled-ops .ops-stat { padding:15px 17px; border:1px solid #e3e7ea; border-radius:7px; background:#fff; }
        .scheduled-ops .ops-stat strong { display:block; color:#35404b; font-size:25px; line-height:1; }
        .scheduled-ops .ops-stat span { display:block; margin-top:7px; color:#7a858f; font-size:12px; text-transform:uppercase; }
        .scheduled-ops .ops-tabs { display:flex; gap:5px; margin-bottom:18px; border-bottom:1px solid #e2e6e9; }
        .scheduled-ops .ops-tab-tools { display:flex; justify-content:flex-end; gap:8px; margin:-8px 0 14px; }
        .scheduled-ops .ops-tab { padding:11px 18px; border:0; border-bottom:3px solid transparent; background:transparent; color:#6a747e; font-weight:600; }
        .scheduled-ops .ops-tab.active { border-color:#36c6d3; color:#2b9faa; }
        .scheduled-ops .ops-filters { display:grid; grid-template-columns:minmax(200px,2fr) minmax(150px,1fr) minmax(150px,1fr); gap:10px; margin-bottom:15px; }
        .scheduled-ops .form-control { height:40px; border-color:#d4dade; border-radius:5px; box-shadow:none; }
        .scheduled-ops .ops-table-wrap { overflow-x:auto; border:1px solid #e2e6e9; border-radius:7px; }
        .scheduled-ops .ops-table { width:100%; margin:0; }
        .scheduled-ops .ops-table th { padding:11px 12px; background:#edf4f9; color:#46515f; white-space:nowrap; }
        .scheduled-ops .ops-table td { padding:11px 12px; border-top:1px solid #e8ebed; color:#5d6873; vertical-align:middle; }
        .scheduled-ops .ops-name { color:#35404b; font-weight:600; }
        .scheduled-ops .ops-key { display:block; margin-top:3px; color:#99a2aa; font-family:monospace; font-size:11px; }
        .scheduled-ops .ops-status { display:inline-block; padding:4px 9px; border-radius:12px; font-size:11px; font-weight:700; text-transform:uppercase; }
        .scheduled-ops .status-successful { background:#e4f6ea; color:#28784a; }
        .scheduled-ops .status-failed, .scheduled-ops .status-partial, .scheduled-ops .status-missed { background:#fde7e9; color:#b83e48; }
        .scheduled-ops .status-running, .scheduled-ops .status-queued { background:#e7f2fb; color:#3378aa; }
        .scheduled-ops .status-shadow, .scheduled-ops .status-skipped { background:#f0f1f2; color:#747d85; }
        .scheduled-ops .ops-btn { padding:7px 11px; border:1px solid transparent; border-radius:4px; font-weight:600; }
        .scheduled-ops .ops-btn-primary { background:#36c6d3; color:#fff; }
        .scheduled-ops .ops-btn-light { border-color:#d4dade; background:#fff; color:#596570; }
        .scheduled-ops .ops-category { margin:22px 0 9px; color:#46515f; font-size:17px; font-weight:600; text-transform:capitalize; }
        .scheduled-ops .ops-schedule-grid { display:grid; gap:10px; }
        .scheduled-ops .ops-schedule { display:grid; grid-template-columns:minmax(220px,1.1fr) minmax(190px,.8fr) minmax(260px,1.4fr) auto; gap:14px; align-items:center; padding:13px 15px; border:1px solid #e3e7ea; border-radius:7px; background:#fff; }
        .scheduled-ops .ops-recipient { color:#7a858f; font-size:13px; }
        .scheduled-ops .ops-recipient-mode { display:inline-block; margin-top:5px; padding:3px 7px; border-radius:10px; background:#eef2f4; color:#64717d; font-size:10px; font-weight:700; text-transform:uppercase; }
        .scheduled-ops .ops-off { opacity:.55; }
        .scheduled-ops .ops-flash { margin-bottom:15px; padding:11px 14px; border-radius:5px; background:#e5f6ec; color:#267747; }
        .scheduled-ops-modal .sws-modal-header { background:#46515f; border-bottom:0; }
        .scheduled-ops-modal .sws-modal-title, .scheduled-ops-modal .sws-modal-close { color:#fff; }
        .scheduled-ops-modal .sws-modal-close:hover { background:#5b6877; color:#fff; }
        .scheduled-ops .ops-detail-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:18px; }
        .scheduled-ops .ops-detail { padding:11px; background:#f3f5f6; border-radius:5px; }
        .scheduled-ops .ops-detail span { display:block; color:#929ba3; font-size:11px; text-transform:uppercase; }
        .scheduled-ops .ops-detail strong { display:block; margin-top:4px; color:#46515f; overflow-wrap:anywhere; }
        .scheduled-ops .ops-detail-status { border-left:4px solid #a7b0b8; }
        .scheduled-ops .ops-detail-status-successful { border-color:#36a866; background:#e5f6ec; }
        .scheduled-ops .ops-detail-status-successful strong { color:#267747; }
        .scheduled-ops .ops-detail-status-queued, .scheduled-ops .ops-detail-status-running { border-color:#e89b2c; background:#fff3df; }
        .scheduled-ops .ops-detail-status-queued strong, .scheduled-ops .ops-detail-status-running strong { color:#a65d00; }
        .scheduled-ops .ops-detail-status-failed, .scheduled-ops .ops-detail-status-partial, .scheduled-ops .ops-detail-status-missed { border-color:#e7505a; background:#fde7e9; }
        .scheduled-ops .ops-detail-status-failed strong, .scheduled-ops .ops-detail-status-partial strong, .scheduled-ops .ops-detail-status-missed strong { color:#b83e48; }
        .scheduled-ops .ops-detail-status-shadow { border-color:#4f94c8; background:#e9f2fb; }
        .scheduled-ops .ops-detail-status-shadow strong { color:#3977a8; }
        .scheduled-ops .ops-detail-status-skipped { border-color:#a7b0b8; background:#f0f1f2; }
        .scheduled-ops .ops-detail-status-skipped strong { color:#68737d; }
        .scheduled-ops .ops-output { max-height:240px; overflow:auto; padding:13px; background:#25303a; color:#e5ebef; border-radius:5px; white-space:pre-wrap; font:12px/1.5 monospace; }
        .scheduled-ops .ops-error { margin:12px 0; padding:12px; border-left:4px solid #e7505a; background:#fff2f3; color:#9b323a; overflow-wrap:anywhere; }
        .scheduled-ops .ops-mail { margin-top:10px; padding:11px 13px; border:1px solid #e2e6e9; border-radius:5px; }
        .scheduled-ops .ops-mail strong { color:#46515f; }
        .scheduled-ops .ops-mail small { display:block; margin-top:4px; color:#8a949c; }
        .scheduled-ops .ops-form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; }
        .scheduled-ops .ops-form-span-2 { grid-column:span 2; }
        .scheduled-ops .ops-checks { display:flex; flex-wrap:wrap; gap:9px 18px; padding:10px 0; }
        .scheduled-ops .ops-checks label { margin:0; font-weight:400; }
        .scheduled-ops .ops-help { color:#7a858f; font-size:12px; line-height:1.45; }
        .scheduled-ops .ops-recipient-panel { margin-top:18px; padding:15px; border:1px solid #dce3e7; border-radius:7px; background:#f7f9fa; }
        .scheduled-ops .ops-rule { display:grid; grid-template-columns:85px 150px minmax(180px,1fr) minmax(130px,.7fr) auto; gap:8px; align-items:start; margin-top:9px; }
        .scheduled-ops .ops-rule .form-control { width:100%; }
        .scheduled-ops .ops-rule-remove { min-height:40px; color:#b83e48; }
        .scheduled-ops .ops-activity { margin-top:16px; padding-top:13px; border-top:1px solid #e2e6e9; color:#7a858f; font-size:12px; }
        .scheduled-ops .ops-activity div + div { margin-top:5px; }
        .scheduled-ops .ops-handler { display:flex; justify-content:space-between; align-items:center; gap:14px; padding:13px; border:1px solid #e2e6e9; border-radius:6px; }
        .scheduled-ops .ops-handler + .ops-handler { margin-top:9px; }
        @media (max-width:850px) {
            .scheduled-ops .ops-stats { grid-template-columns:repeat(2,1fr); }
            .scheduled-ops .ops-schedule { grid-template-columns:1fr; }
            .scheduled-ops .ops-filters { grid-template-columns:1fr; }
            .scheduled-ops .ops-rule { grid-template-columns:1fr 1fr; }
            .scheduled-ops .ops-rule > :nth-child(3), .scheduled-ops .ops-rule > :nth-child(4) { grid-column:span 2; }
        }
        @media (max-width:550px) {
            .scheduled-ops .ops-title-row { align-items:flex-start; }
            .scheduled-ops .ops-detail-grid { grid-template-columns:1fr; }
            .scheduled-ops .ops-form-grid { grid-template-columns:1fr; }
            .scheduled-ops .ops-form-span-2 { grid-column:auto; }
            .scheduled-ops .ops-tab-tools { align-items:stretch; flex-direction:column; }
        }
    </style>

    <div class="portlet light">
        <div class="portlet-body">
            <div class="ops-title-row">
                <h2>Scheduled Operations</h2>
                <span class="ops-mode ops-mode-{{ $mode }}">{{ $mode }} mode</span>
            </div>

            <div class="ops-banner">
                @if($mode === 'legacy')
                    The original nightly and hourly controllers are still running. This dashboard is ready for testing but will not replace them until the environment is deliberately changed.
                @elseif($mode === 'shadow')
                    Shadow mode records which independent jobs would run while the original cron remains live. No new jobs are executed automatically.
                @else
                    Live mode is active. Scheduled work is dispatched as independent queue jobs and failures are monitored automatically.
                @endif

                @if(in_array($mode, ['shadow','live'], true))
                    @php
                        $heartbeatFresh = $heartbeat?->last_success_at && $heartbeat->last_success_at->gte(now()->subMinutes(3));
                    @endphp
                    <span class="ops-heartbeat {{ $heartbeatFresh ? 'ops-heartbeat-ok' : 'ops-heartbeat-warning' }}">
                        <i class="fa {{ $heartbeatFresh ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i>
                        {{ $heartbeat?->last_success_at ? 'Scheduler last checked '.$heartbeat->last_success_at->format('d/m/Y g:i:s a') : 'The new scheduler has not checked in yet.' }}
                    </span>
                @endif
            </div>

            @if(session()->has('scheduled-success'))
                <div class="ops-flash"><i class="fa fa-check-circle"></i> {{ session('scheduled-success') }}</div>
            @endif

            <div class="ops-stats">
                <div class="ops-stat"><strong>{{ $stats['total'] }}</strong><span>Runs today</span></div>
                <div class="ops-stat"><strong>{{ $stats['successful'] }}</strong><span>Successful</span></div>
                <div class="ops-stat"><strong>{{ $stats['running'] }}</strong><span>Queued / running</span></div>
                <div class="ops-stat"><strong>{{ $stats['failed'] }}</strong><span>Failed / missed</span></div>
            </div>

            <div class="ops-tabs">
                <button class="ops-tab {{ $activeTab === 'runs' ? 'active' : '' }}" wire:click="$set('activeTab','runs')">Run history</button>
                <button class="ops-tab {{ $activeTab === 'schedules' ? 'active' : '' }}" wire:click="$set('activeTab','schedules')">Schedules &amp; recipients</button>
            </div>

            @if($activeTab === 'runs')
                <div class="ops-filters">
                    <input type="search" class="form-control" placeholder="Search operation name or key" wire:model.live.debounce.300ms="search">
                    <select class="form-control" wire:model.live="statusFilter">
                        <option value="">All statuses</option>
                        @foreach(['queued','running','successful','failed','missed','shadow','skipped'] as $status)
                            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <select class="form-control" wire:model.live="categoryFilter">
                        <option value="">All categories</option>
                        @foreach($definitions->keys() as $category)
                            <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="ops-table-wrap">
                    <table class="ops-table">
                        <thead><tr><th>Operation</th><th>Scheduled</th><th>Trigger</th><th>Status</th><th>Duration</th><th>Emails</th><th></th></tr></thead>
                        <tbody>
                        @forelse($runs as $run)
                            <tr>
                                <td><span class="ops-name">{{ $run->task_name }}</span><span class="ops-key">{{ $run->task_key }}</span></td>
                                <td>{{ optional($run->scheduled_for)->format('d/m/Y g:i a') }}</td>
                                <td>{{ ucfirst($run->trigger) }}</td>
                                <td><span class="ops-status status-{{ $run->status }}">{{ $run->status }}</span></td>
                                <td>{{ $run->duration_ms !== null ? number_format($run->duration_ms / 1000, 2).'s' : '—' }}</td>
                                <td>{{ $run->messages->where('status','sent')->count() }}</td>
                                <td><button class="ops-btn ops-btn-light" wire:click="showRun({{ $run->id }})">Details</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="7">No scheduled run history matches these filters.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="ops-tab-tools">
                    <a class="ops-btn ops-btn-light" href="/settings/notifications"><i class="fa fa-envelope"></i> Manage notification recipients</a>
                    <button class="ops-btn ops-btn-primary" wire:click="openAddOperation"><i class="fa fa-plus"></i> Add operation</button>
                </div>
                @foreach($definitions as $category => $items)
                    <h3 class="ops-category">{{ str_replace('_',' ',$category) }}</h3>
                    <div class="ops-schedule-grid">
                        @foreach($items as $definition)
                            <div class="ops-schedule {{ !$definition['enabled'] ? 'ops-off' : '' }}">
                                <div><span class="ops-name">{{ $definition['name'] }}</span><span class="ops-key">{{ $definition['key'] }}</span></div>
                                <div><span class="ops-status {{ $definition['enabled'] ? 'status-successful' : 'status-skipped' }}">{{ $definition['enabled'] ? 'Enabled' : 'Disabled' }}</span><br><small>{{ $definition['schedule_label'] }}</small></div>
                                <div class="ops-recipient">
                                    <strong>Recipients:</strong> {{ $definition['recipients'] }}
                                    <span class="ops-recipient-mode">{{ $definition['recipient_mode'] ?? 'legacy' }}</span><br>
                                    <small>{{ $definition['description'] }}</small>
                                </div>
                                <div>
                                    <button class="ops-btn ops-btn-light" wire:click="editSettings('{{ $definition['key'] }}')"><i class="fa fa-cog"></i></button>
                                    <button class="ops-btn ops-btn-primary" wire:click="requestRun('{{ $definition['key'] }}')">Run now</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <x-ui.modal :show="(bool) $selectedRun" title="Scheduled operation details" close-action="closeModals" max-width="850px" class="scheduled-ops-modal">
        @if($selectedRun)
            <h3 style="margin-top:0;color:#46515f">{{ $selectedRun->task_name }}</h3>
            <div class="ops-detail-grid">
                <div class="ops-detail ops-detail-status ops-detail-status-{{ $selectedRun->status }}"><span>Status</span><strong>{{ ucfirst($selectedRun->status) }}</strong></div>
                <div class="ops-detail"><span>Scheduled</span><strong>{{ optional($selectedRun->scheduled_for)->format('d/m/Y g:i a') }}</strong></div>
                <div class="ops-detail"><span>Attempt / duration</span><strong>{{ $selectedRun->attempt }} / {{ $selectedRun->duration_ms !== null ? number_format($selectedRun->duration_ms / 1000, 2).'s' : '—' }}</strong></div>
            </div>

            @if($selectedRun->exception_message)
                <div class="ops-error"><strong>{{ $selectedRun->exception_class }}</strong><br>{{ $selectedRun->exception_message }}<br><small>{{ $selectedRun->exception_file }}:{{ $selectedRun->exception_line }}</small></div>
            @endif

            <h4>Output</h4>
            <pre class="ops-output">{{ $selectedRun->output ?: 'No console output was produced.' }}</pre>

            <h4 style="margin-top:20px">Emails sent ({{ $selectedRun->messages->where('status','sent')->count() }})</h4>
            @forelse($selectedRun->messages as $message)
                <div class="ops-mail">
                    <strong>{{ $message->subject ?: '(No subject)' }}</strong>
                    <span class="ops-status status-{{ $message->status === 'sent' ? 'successful' : 'failed' }}" style="float:right">{{ $message->status }}</span>
                    <small>To: {{ $message->recipients->where('type','to')->pluck('email')->join(', ') ?: 'No recipients captured' }}</small>
                    <small>CC/BCC: {{ $message->recipients->whereIn('type',['cc','bcc'])->pluck('email')->join(', ') ?: 'None' }}</small>
                    @if($message->html_body || $message->text_body)
                        <a href="{{ route('scheduled-operations.message-preview', $message) }}" target="_blank" rel="noopener">Preview email</a>
                    @endif
                </div>
            @empty
                <p>No email was sent by this run.</p>
            @endforelse

            <x-slot name="footer">
                <button class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Close</button>
                @if(in_array($selectedRun->status, ['failed', 'missed'], true))
                    <button class="sws-modal-btn sws-modal-btn-primary" wire:click="requestRetry({{ $selectedRun->id }})">Retry</button>
                @elseif(in_array($selectedRun->status, ['successful', 'shadow'], true))
                    <button class="sws-modal-btn sws-modal-btn-primary" wire:click="requestRunAgain({{ $selectedRun->id }})">Run again</button>
                @endif
            </x-slot>
        @endif
    </x-ui.modal>

    <x-ui.confirm-modal :show="$showRunConfirm" title="Run operation now?" close-action="closeModals" confirm-action="confirmRun" confirm-label="Add to queue" loading-target="confirmRun">
        This runs <span class="sws-confirm-item">{{ $pendingDefinition['name'] ?? '' }}</span> independently of its normal schedule. Any emails and data changes are real.
    </x-ui.confirm-modal>

    <x-ui.confirm-modal :show="$showRetryConfirm" title="Retry failed operation?" close-action="closeModals" confirm-action="confirmRetry" confirm-label="Retry operation" loading-target="confirmRetry">
        This creates a new auditable attempt for <span class="sws-confirm-item">{{ $pendingDefinition['name'] ?? '' }}</span>. The original failed run is preserved.
    </x-ui.confirm-modal>

    <x-ui.modal :show="$showSettings" title="Operation settings" close-action="closeModals" max-width="980px" class="scheduled-ops-modal">
        @if($settingsDefinition)
            <div class="ops-form-grid">
                <div class="form-group">
                    <label class="control-label">Display name</label>
                    <input class="form-control" type="text" wire:model="settingName">
                    @error('settingName')<span class="help-block">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="control-label">Category</label>
                    <input class="form-control" type="text" wire:model="settingCategory" placeholder="report, maintenance, reminder...">
                    @error('settingCategory')<span class="help-block">{{ $message }}</span>@enderror
                </div>
                <div class="form-group ops-form-span-2">
                    <label class="control-label">Description</label>
                    <textarea class="form-control" style="height:74px" wire:model="settingDescription"></textarea>
                </div>
            </div>

            <div class="ops-checks">
                <label><input type="checkbox" wire:model="settingEnabled"> Enabled for automatic runs</label>
            </div>

            <h4>Schedule <small>(Sydney time)</small></h4>
            <div class="ops-form-grid">
                <div class="form-group">
                    <label class="control-label">Frequency</label>
                    <select class="form-control" wire:model.live="settingScheduleType">
                        <option value="hourly">Every hour</option>
                        <option value="daily">Daily</option>
                        <option value="weekdays">Every weekday</option>
                        <option value="weekly">Selected weekdays</option>
                        <option value="fortnightly">Fortnightly</option>
                        <option value="monthly_nth_weekday">Monthly — numbered weekday</option>
                        <option value="monthly_last_weekday">Monthly — last weekday</option>
                        <option value="monthly_day">Monthly — day of month</option>
                        <option value="quarterly">Selected months</option>
                    </select>
                </div>
                @if($settingScheduleType === 'hourly')
                    <div class="form-group">
                        <label class="control-label">Minute past the hour</label>
                        <input class="form-control" type="number" min="0" max="59" wire:model="settingMinute">
                    </div>
                @else
                    <div class="form-group">
                        <label class="control-label">Run time</label>
                        <input class="form-control" type="time" wire:model="settingTime">
                    </div>
                @endif

                @if($settingScheduleType === 'weekly')
                    <div class="form-group ops-form-span-2">
                        <label class="control-label">Run on</label>
                        <div class="ops-checks">
                            @foreach([1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'] as $number => $day)
                                <label><input type="checkbox" value="{{ $number }}" wire:model="settingWeekdays"> {{ $day }}</label>
                            @endforeach
                        </div>
                        @error('settingWeekdays')<span class="help-block">{{ $message }}</span>@enderror
                    </div>
                @endif

                @if(in_array($settingScheduleType, ['fortnightly','monthly_nth_weekday','monthly_last_weekday'], true))
                    <div class="form-group">
                        <label class="control-label">Weekday</label>
                        <select class="form-control" wire:model="settingWeekday">
                            @foreach([1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'] as $number => $day)
                                <option value="{{ $number }}">{{ $day }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if($settingScheduleType === 'fortnightly')
                    <div class="form-group">
                        <label class="control-label">Anchor date</label>
                        <input class="form-control" type="date" wire:model="settingAnchor">
                        <span class="ops-help">Choose one date that belongs to the intended fortnight.</span>
                    </div>
                @elseif($settingScheduleType === 'monthly_nth_weekday')
                    <div class="form-group">
                        <label class="control-label">Occurrence</label>
                        <select class="form-control" wire:model="settingOccurrence">
                            <option value="1">First</option><option value="2">Second</option><option value="3">Third</option><option value="4">Fourth</option><option value="5">Fifth</option>
                        </select>
                    </div>
                @elseif(in_array($settingScheduleType, ['monthly_day','quarterly'], true))
                    <div class="form-group">
                        <label class="control-label">Day of month</label>
                        <input class="form-control" type="number" min="1" max="28" wire:model="settingDay">
                        <span class="ops-help">Limited to 1–28 so it exists every month.</span>
                    </div>
                @endif
                @if($settingScheduleType === 'quarterly')
                    <div class="form-group ops-form-span-2">
                        <label class="control-label">Run in these months</label>
                        <div class="ops-checks">
                            @foreach([1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'] as $number => $month)
                                <label><input type="checkbox" value="{{ $number }}" wire:model="settingMonths"> {{ $month }}</label>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="form-group">
                    <label class="control-label">Maximum attempts</label>
                    <input class="form-control" type="number" min="1" max="10" wire:model="settingTries">
                </div>
                <div class="form-group">
                    <label class="control-label">Timeout (seconds)</label>
                    <input class="form-control" type="number" min="30" max="300" wire:model="settingTimeout">
                    <span class="ops-help">Maximum 300 seconds to match the current Forge worker.</span>
                </div>
            </div>

            <div class="ops-recipient-panel">
                <h4 style="margin-top:0">Email recipients</h4>
                <div class="ops-form-grid">
                    <div class="form-group">
                        <label class="control-label">Recipient control</label>
                        <select class="form-control" wire:model.live="settingRecipientMode">
                            <option value="legacy">Legacy — use addresses in existing code</option>
                            <option value="append">Append — keep code addresses and add rules below</option>
                            <option value="managed">Managed — replace code addresses with rules below</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Summary shown in list</label>
                        <input class="form-control" type="text" wire:model="settingRecipientSummary" placeholder="e.g. Site supervisors and WHS group">
                    </div>
                </div>
                <p class="ops-help">
                    <strong>Legacy</strong> changes nothing. <strong>Append</strong> is safest while migrating. <strong>Managed</strong> makes this screen the complete To/CC/BCC source.
                    Managed mode also replaces any per-site, Supervisor or record-specific addresses selected inside the existing report code.
                </p>

                @foreach($recipientRules as $index => $rule)
                    <div class="ops-rule" wire:key="recipient-rule-{{ $index }}">
                        <select class="form-control" wire:model="recipientRules.{{ $index }}.delivery_type" aria-label="Delivery type">
                            <option value="to">To</option><option value="cc">CC</option><option value="bcc">BCC</option>
                        </select>
                        <select class="form-control" wire:model.live="recipientRules.{{ $index }}.source_type" aria-label="Recipient source">
                            <option value="user">SWS user</option><option value="notification_group">Notification group</option><option value="manual">Email address</option>
                        </select>
                        @if(($rule['source_type'] ?? '') === 'user')
                            <select class="form-control" wire:model="recipientRules.{{ $index }}.source_value">
                                <option value="">Select user</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->fullname }} — {{ $user->email }}</option>
                                @endforeach
                            </select>
                        @elseif(($rule['source_type'] ?? '') === 'notification_group')
                            <select class="form-control" wire:model="recipientRules.{{ $index }}.source_value">
                                <option value="">Select notification group</option>
                                @foreach($notificationGroups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <input class="form-control" type="email" wire:model="recipientRules.{{ $index }}.source_value" placeholder="person@example.com">
                        @endif
                        <input class="form-control" type="text" wire:model="recipientRules.{{ $index }}.label" placeholder="Optional label">
                        <button class="ops-btn ops-btn-light ops-rule-remove" wire:click="removeRecipientRule({{ $index }})" title="Remove recipient"><i class="fa fa-trash"></i></button>
                    </div>
                    @error('recipientRules.'.$index.'.source_value')<span class="help-block">{{ $message }}</span>@enderror
                @endforeach
                @error('recipientRules')<span class="help-block">{{ $message }}</span>@enderror
                <button class="ops-btn ops-btn-light" style="margin-top:10px" wire:click="addRecipientRule"><i class="fa fa-plus"></i> Add recipient rule</button>
            </div>

            @if($changeLogs->isNotEmpty())
                <div class="ops-activity">
                    <strong>Recent changes</strong>
                    @foreach($changeLogs as $change)
                        <div>{{ $change->created_at->format('d/m/Y g:i a') }} — {{ str_replace('_',' ',$change->action) }}{{ $change->user ? ' by '.$change->user->fullname : '' }}</div>
                    @endforeach
                </div>
            @endif

            <x-slot name="footer">
                @if($hasLegacyDefault)
                    <button class="sws-modal-btn sws-modal-btn-secondary" wire:click="resetSettings">Restore defaults</button>
                @endif
                <button class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Cancel</button>
                <button class="sws-modal-btn sws-modal-btn-primary" wire:click="saveSettings" wire:loading.attr="disabled" wire:target="saveSettings">Save operation</button>
            </x-slot>
        @endif
    </x-ui.modal>

    <x-ui.modal :show="$showAddOperation" title="Add scheduled operation" close-action="closeModals" max-width="760px" class="scheduled-ops-modal">
        <p>Code handlers found in <code>app/Scheduled/Operations</code> appear here. Installing one creates a disabled operation so its schedule and recipients can be reviewed safely.</p>
        @forelse($availableHandlers as $handler)
            <div class="ops-handler">
                <div>
                    <span class="ops-name">{{ $handler['name'] }}</span>
                    <span class="ops-key">{{ $handler['key'] }}</span>
                    <small>{{ $handler['description'] }}</small>
                </div>
                <button class="ops-btn ops-btn-primary" wire:click="installHandler('{{ $handler['handler_key'] }}')">Install</button>
            </div>
        @empty
            <div class="ops-banner">There are no unconfigured handlers. Add a class implementing <code>ScheduledOperationHandler</code>, deploy it, then run <code>php artisan scheduled:sync</code>.</div>
        @endforelse
        <x-slot name="footer">
            <button class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Close</button>
        </x-slot>
    </x-ui.modal>
</div>
