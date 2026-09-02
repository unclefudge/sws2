<div class="client-scheduled-reports" x-on:sws-toastr.stop="toastr[$event.detail.type]($event.detail.message)">

    <p class="help-block margin-bottom-20">Choose when each report runs and who receives it. SafeWorksite controls the run time and any report-specific recipients such as a site supervisor.</p>

    <div class="csr-tools">
        <input type="search" class="form-control csr-search" placeholder="Search report, schedule or recipient" wire:model.live.debounce.300ms="reportSearch">
        <button type="button" class="btn btn-default csr-sort-toggle" wire:click="toggleReportSort"
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
                        @unless($report['enabled'])
                            <span class="label label-default">Disabled</span>
                        @endunless
                    </div>
                    @if($report['description'])
                        <div class="csr-description">{{ $report['description'] }}</div>
                    @endif
                </div>
                <div><strong>{{ $report['schedule'] }}</strong></div>
                <div>
                    <div class="csr-recipients">{{ $report['recipients'] }}</div>
                </div>
                <div class="csr-actions">
                    <button type="button" class="btn btn-link btn-sm {{ $report['enabled'] ? 'font-green-haze' : 'font-red' }}" wire:click="toggleReportEnabled({{ $report['id'] }})" wire:loading.attr="disabled" wire:target="toggleReportEnabled({{ $report['id'] }})"
                            title="{{ $report['enabled'] ? 'Disable' : 'Enable' }} {{ $report['name'] }}" aria-label="{{ $report['enabled'] ? 'Disable' : 'Enable' }} {{ $report['name'] }}">
                        <i class="fa {{ $report['enabled'] ? 'fa-bell' : 'fa-bell-slash' }}"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-default" wire:click="openReportLog({{ $report['id'] }})">Log</button>
                    <button type="button" class="btn btn-sm blue" wire:click="editReport({{ $report['id'] }})"> Edit</button>
                    <button type="button" class="btn btn-sm green" wire:click="requestReportRun({{ $report['id'] }})"><i class="fa fa-play"></i> Run</button>
                </div>
            </div>
        @empty
            <div class="well well-sm text-muted">
                {{ trim($reportSearch) !== '' ? 'No scheduled reports match your search.' : 'No converted reports are available yet. Reports appear here after their scheduled handler is installed and synchronised.' }}
            </div>
        @endforelse
    </div>

    <x-ui.modal :show="$showRecipientWarning" title="Report cannot be enabled" close-action="closeRecipientWarning" max-width="520px" class="client-scheduled-reports-warning-modal">
        @if($showRecipientWarning)
            <div class="note note-danger">
                <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                <div><strong>Recipients are required</strong><span>{{ $recipientWarning }}</span></div>
            </div>
        @endif
        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="closeRecipientWarning">Close</button>
        </x-slot>
    </x-ui.modal>

    <x-ui.confirm-modal :show="$showRunConfirm" title="Run report now?" close-action="closeRunConfirm" confirm-action="confirmReportRun" confirm-label="Run report" loading-target="confirmReportRun">
        This creates a one-off execution of<br><span class="sws-confirm-item">{{ $pendingRunName }}</span><br><br>
        <span class="display-block margin-top-15"><strong>Recipients</strong><br>{{ $pendingRunRecipients }}</span>
        <span class="display-block help-block margin-top-10">Automatic recipients are resolved from the report records when it runs.</span>
    </x-ui.confirm-modal>

    <x-ui.modal :show="(bool) $logDefinitionId" title="Report log" close-action="closeReportLog" max-width="900px" class="client-scheduled-reports-log-modal">
        @if($logMessage)
            <h4>{{ $logMessage->subject ?: '(No subject)' }}</h4>
            <iframe class="csr-email-preview" sandbox="allow-same-origin" referrerpolicy="no-referrer" src="{{ route('scheduled-reports.message-preview', $logMessage) }}" title="Email preview"></iframe>
            <x-slot name="footer">
                <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="backToReportLogRun"><i class="fa fa-arrow-left"></i> Back to run</button>
                <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeReportLog">Close</button>
            </x-slot>
        @elseif($logRun)
            <button type="button" class="btn btn-default btn-sm" wire:click="backToReportLogList"><i class="fa fa-arrow-left"></i> All recent runs</button>
            <h4>{{ $logDefinition?->name }}</h4>
            <div class="csr-run-details"
                 @if(in_array($logRun->status, ['queued', 'running'], true)) wire:poll.2s @endif>
                <div class="csr-run-detail csr-run-detail-status csr-run-detail-status-{{ $logRun->status }}"><span>Status</span><strong>{{ ucfirst($logRun->status) }}</strong></div>
                <div class="csr-run-detail"><span>Executed</span><strong>{{ optional($logRun->started_at ?: $logRun->scheduled_for)->format('d/m/Y g:i a') }}</strong></div>
                <div class="csr-run-detail"><span>Trigger / duration</span><strong>{{ ucfirst($logRun->trigger) }} / {{ $logRun->duration_ms !== null ? number_format($logRun->duration_ms / 1000, 2).'s' : '—' }}</strong></div>
            </div>
            @if($logRun->status === 'failed')
                <div class="note note-danger"><i class="fa fa-exclamation-triangle"></i>
                    <div><strong>This execution failed</strong><span>The SafeWorksite administrator has access to the technical failure information.</span></div>
                </div>
            @endif
            <h4>Emails ({{ $logRun->messages->count() }})</h4>
            @forelse($logRun->messages as $message)
                <div class="csr-mail">
                    <strong>{{ $message->subject ?: '(No subject)' }}</strong>
                    <span class="csr-status csr-status-{{ $message->status === 'sent' ? 'successful' : 'failed' }} pull-right">{{ $message->status }}</span>
                    <small>To: {{ $message->recipients->where('type','to')->pluck('email')->join(', ') ?: 'No recipients captured' }}</small>
                    <small>CC: {{ $message->recipients->where('type','cc')->pluck('email')->join(', ') ?: 'None' }}</small>
                    <div class="csr-mail-actions">
                        @if($message->html_body || $message->text_body)
                            <button type="button" class="btn btn-default btn-sm" wire:click="showReportLogMessage({{ $message->id }})"><i class="fa fa-envelope-open"></i> View email</button>
                        @endif
                        @foreach($message->archivedAttachments as $attachment)
                            <a class="btn btn-default btn-sm" href="{{ route('scheduled-report-attachments.download', $attachment) }}"><i class="fa fa-paperclip"></i> {{ $attachment->original_name }}</a>
                        @endforeach
                    </div>
                </div>
            @empty
                <p>No email was produced by this run.</p>
            @endforelse
            <x-slot name="footer">
                <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="backToReportLogList">Back</button>
                <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeReportLog">Close</button>
            </x-slot>
        @else
            <h4>{{ $logDefinition?->name }}</h4>
            <p class="help-block">The latest 20 executions are shown. Select a run to see its recipients, email and retained attachments.</p>
            <div class="csr-log-list">
                @forelse($logRuns as $run)
                    <button type="button" class="csr-log-row" wire:click="showReportLogRun({{ $run->id }})">
                        <strong>{{ optional($run->started_at ?: $run->scheduled_for)->format('d/m/Y g:i a') }}</strong>
                        <span>{{ ucfirst($run->trigger) }}</span>
                        <span class="csr-status csr-status-{{ $run->status }}">{{ $run->status }}</span>
                        <span>{{ $run->duration_ms !== null ? number_format($run->duration_ms / 1000, 2).'s' : '—' }}</span>
                        <span>{{ $run->sent_messages_count }} email{{ $run->sent_messages_count === 1 ? '' : 's' }} <i class="fa fa-chevron-right"></i></span>
                    </button>
                @empty
                    <div class="well well-sm">This report has not run yet.</div>
                @endforelse
            </div>
            <x-slot name="footer">
                <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeReportLog">Close</button>
            </x-slot>
        @endif
    </x-ui.modal>

    <x-ui.modal :show="$showEditor" title="Scheduled report settings" close-action="closeEditor" max-width="900px" class="client-scheduled-reports-modal">
        @if($showEditor)
            <div class="csr-grid">
                <div class="csr-span-2"><h4>{{ $reportName }}</h4></div>

                <div class="csr-select-host" wire:key="client-report-frequency-{{ $definitionId }}-{{ $scheduleType }}">
                    <x-form.select name="scheduleType" label="Frequency" :value="$scheduleType" data-width="100%" wire:ignore x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('scheduleType', $el.value)">
                        <option value="daily" @selected($scheduleType === 'daily')>Daily</option>
                        <option value="weekdays" @selected($scheduleType === 'weekdays')>Every weekday</option>
                        <option value="weekly" @selected($scheduleType === 'weekly')>Weekly</option>
                        <option value="fortnightly" @selected($scheduleType === 'fortnightly')>Fortnightly</option>
                        <option value="monthly_nth_weekday" @selected($scheduleType === 'monthly_nth_weekday')>Monthly — selected weekday</option>
                        <option value="monthly_last_weekday" @selected($scheduleType === 'monthly_last_weekday')>Monthly — last weekday</option>
                        <option value="monthly_day" @selected($scheduleType === 'monthly_day')>Monthly — selected date</option>
                        <option value="quarterly" @selected($scheduleType === 'quarterly')>Quarterly</option>
                    </x-form.select>
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
                    <div class="csr-select-host" wire:key="client-report-weekday-{{ $definitionId }}-{{ $weekday }}">
                        <x-form.select name="weekday" label="Day" :value="$weekday" data-width="100%" wire:ignore x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('weekday', Number($el.value))">
                            @foreach([1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'] as $value => $label)
                                <option value="{{ $value }}" @selected((int)$weekday === $value)>{{ $label }}</option>
                            @endforeach
                        </x-form.select>
                    </div>
                @elseif(in_array($scheduleType, ['monthly_day','quarterly'], true))
                    <div class="csr-select-host" wire:key="client-report-month-day-{{ $definitionId }}-{{ $day }}">
                        <x-form.select name="day" label="Day of month" :value="$day" data-width="100%" wire:ignore x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('day', Number($el.value))">
                            @foreach(range(1,28) as $value)
                                <option value="{{ $value }}" @selected((int)$day === $value)>{{ $value }}</option>
                            @endforeach
                        </x-form.select>
                    </div>
                @endif

                @if($scheduleType === 'monthly_nth_weekday')
                    <div class="csr-select-host" wire:key="client-report-occurrence-{{ $definitionId }}-{{ $occurrence }}">
                        <x-form.select name="occurrence" label="Occurrence" :value="$occurrence" data-width="100%" wire:ignore x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('occurrence', Number($el.value))">
                            <option value="1" @selected((int)$occurrence === 1)>First</option>
                            <option value="2" @selected((int)$occurrence === 2)>Second</option>
                            <option value="3" @selected((int)$occurrence === 3)>Third</option>
                            <option value="4" @selected((int)$occurrence === 4)>Fourth</option>
                            <option value="5" @selected((int)$occurrence === 5)>Fifth</option>
                        </x-form.select>
                    </div>
                @endif
            </div>

            @if($dynamicRecipients)
                <div class="note note-info">
                    <strong>Recipients selected automatically by this report</strong>
                    @foreach($dynamicRecipients as $recipient)
                        <div class="csr-dynamic-row">
                            <span class="label label-info">{{ strtoupper($recipient['delivery']) }}</span> {{ $recipient['label'] }}
                            @if($recipient['description'])
                                <small>{{ $recipient['description'] }}</small>
                            @endif
                        </div>
                    @endforeach
                    <div class="help-block">These rules are protected because they depend on the records in each report. Additional recipients below are optional and receive every email sent by this report.</div>
                </div>
            @endif

            <div class="csr-rules">
                <strong>Additional recipients</strong>
                <div class="help-block">Use User for Cape Cod staff or Email for an external address. Each email address has its own validated rule.</div>
                @error('recipientRules')
                <div class="help-block font-red">{{ $message }}</div>@enderror

                @foreach($recipientRules as $index => $rule)
                    <div class="csr-rule" wire:key="client-recipient-rule-{{ $index }}-{{ $rule['source_type'] }}">
                        <div class="csr-select-host" wire:key="client-recipient-delivery-{{ $definitionId }}-{{ $index }}-{{ $rule['delivery_type'] }}" wire:ignore>
                            <select class="form-control bs-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('recipientRules.{{ $index }}.delivery_type', $el.value)">
                                <option value="to" @selected($rule['delivery_type'] === 'to')>To</option>
                                <option value="cc" @selected($rule['delivery_type'] === 'cc')>CC</option>
                            </select>
                        </div>
                        <div class="csr-select-host" wire:key="client-recipient-source-{{ $definitionId }}-{{ $index }}-{{ $rule['source_type'] }}" wire:ignore>
                            <select class="form-control bs-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('recipientRules.{{ $index }}.source_type', $el.value)">
                                <option value="user" @selected($rule['source_type'] === 'user')>User</option>
                                <option value="manual" @selected($rule['source_type'] === 'manual')>Email</option>
                            </select>
                        </div>
                        @if($rule['source_type'] === 'user')
                            <div class="csr-user-select" wire:ignore>
                                <select multiple class="form-control"
                                        x-init="const parent = $($el).closest('.sws-modal-card'); $($el).select2({width:'100%', placeholder:'Select one or more users', dropdownParent:parent.length ? parent : $(document.body)}).on('change', function(){ $wire.set('recipientRules.{{ $index }}.source_value', $(this).val() || []); })">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" @selected(in_array((string)$user->id, array_map('strval', $rule['source_value'] ?? []), true))>{{ $user->fullname }} ({{ $user->company?->name_alias ?? 'Unknown company' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="email" class="form-control" wire:model="recipientRules.{{ $index }}.source_value" placeholder="person@example.com">
                        @endif
                        <button type="button" class="btn btn-link font-red" title="Remove" wire:click="removeRecipientRule({{ $index }})"><i class="fa fa-trash"></i></button>
                        @error("recipientRules.$index.source_value")<span class="help-block csr-rule-error">{{ $message }}</span>@enderror
                    </div>
                @endforeach

                <div class="csr-rule-actions">
                    <button type="button" class="btn btn-default" wire:click="addRecipientRule"><i class="fa fa-plus"></i> Add recipient</button>
                    <label class="csr-status-toggle">
                        <input type="checkbox" wire:model="enabled" aria-label="Enable scheduled report">
                        <span class="csr-status-track"><span class="csr-status-disabled">Disabled</span><span class="csr-status-enabled">Enabled</span></span>
                    </label>
                </div>
            </div>

            @error('report')
            <div class="help-block font-red margin-top-15">{{ $message }}</div>@enderror
        @endif

        <x-slot name="footer">
            <div>
                <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeEditor">Cancel</button>
                <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="saveReport">Save report</button>
            </div>
        </x-slot>
    </x-ui.modal>
</div>
