<div class="scheduled-ops" wire:poll.15s x-on:sws-toastr.stop="toastr[$event.detail.type]($event.detail.message)">
    @php
        // wire:ignore lets Bootstrap Select own its generated markup. Changing
        // this fingerprint deliberately rebuilds category selects after the
        // category manager adds, renames, reorders or disables an option.
        $categorySelectVersion = md5($categories->map(
            fn($category) => implode('|', [$category->id, $category->slug, $category->name, (int) $category->enabled, $category->sort_order])
        )->join(';'));
    @endphp

    <div class="portlet light bordered">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-clock-o"></i>
                <span class="caption-subject font-green-haze bold uppercase">Scheduled Operations</span>
            </div>
        </div>
        <div class="portlet-body">
            @if(in_array($mode, ['shadow','live'], true))
                @php
                    $heartbeatFresh = $heartbeat?->last_success_at && $heartbeat->last_success_at->gte(now()->subMinutes(3));
                @endphp
                <div class="note note-info">
                    <span class="{{ $heartbeatFresh ? 'font-green' : 'font-red' }}">
                        <i class="fa {{ $heartbeatFresh ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i>
                        {{ $heartbeat?->last_success_at ? 'Scheduler last checked '.$heartbeat->last_success_at->format('d/m/Y g:i:s a') : 'The new scheduler has not checked in yet.' }}
                    </span>
                </div>
            @endif

            <div class="ops-stats">
                <div class="ops-stat"><strong>{{ $stats['total'] }}</strong><span>Runs {{ $stats['date_label'] }}</span></div>
                <div class="ops-stat"><strong>{{ $stats['successful'] }}</strong><span>Successful</span></div>
                <div class="ops-stat"><strong>{{ $stats['running'] }}</strong><span>Queued / running</span></div>
                <div class="ops-stat {{ $stats['failed'] > 0 ? 'ops-stat-danger' : '' }}"><strong>{{ $stats['failed'] }}</strong><span>Failed / missed</span></div>
            </div>

            <ul class="nav nav-tabs">
                <li class="{{ $activeTab === 'runs' ? 'active' : '' }}"><a href="#" wire:click.prevent="$set('activeTab','runs')">Run history</a></li>
                <li class="{{ $activeTab === 'schedules' ? 'active' : '' }}"><a href="#" wire:click.prevent="$set('activeTab','schedules')">Schedules &amp; recipients</a></li>
            </ul>

            @if($activeTab === 'runs')
                {{-- Run History --}}
                <div class="ops-filters">
                    <input type="search" class="form-control" placeholder="Search operation name" wire:model.live.debounce.300ms="search">
                    <div class="ops-date-filter">
                        <button type="button" class="btn grey" wire:click="shiftRunDate(-1)" title="Previous day" aria-label="Previous day">
                            <i class="fa fa-angle-left" aria-hidden="true"></i>
                        </button>
                        <div class="ops-date-picker">
                            <span class="form-control ops-date-display" aria-hidden="true">
                                <b>{{ \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $dateFilter)->format('D d M, Y') }}</b>
                            </span>
                            <input type="date" class="ops-native-date-picker" wire:model.live="dateFilter" inputmode="none"
                                   x-on:click="if ($el.showPicker) $el.showPicker()"
                                   x-on:keydown.prevent x-on:beforeinput.prevent x-on:paste.prevent
                                   aria-label="Run date">
                        </div>
                        <button type="button" class="btn grey" wire:click="shiftRunDate(1)" title="Next day" aria-label="Next day">
                            <i class="fa fa-angle-right" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="ops-select-host" wire:key="run-status-filter-{{ $statusFilter }}" wire:ignore>
                        <select class="form-control bs-select ops-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('statusFilter', $el.value)">
                            <option value="">All statuses</option>
                            @foreach(['queued','running','successful','failed','missed','shadow','skipped'] as $status)
                                <option value="{{ $status }}" @selected($statusFilter === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ops-select-host" wire:key="run-category-filter-{{ $categorySelectVersion }}-{{ $categoryFilter }}" wire:ignore>
                        <select class="form-control bs-select ops-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('categoryFilter', $el.value)">
                            <option value="except_hourly" @selected($categoryFilter === 'except_hourly')>All categories except Hourly</option>
                            <option value="">All categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->slug }}" @selected($categoryFilter === $category->slug)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover table-checkable order-column">
                        <thead>
                        <tr class="mytable-header">
                            <th>Operation</th>
                            <th>Scheduled</th>
                            <th>Trigger</th>
                            <th>Status</th>
                            <th>Duration</th>
                            <th style="width:10%">Emails</th>
                            <th style="width:10%"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($runs as $run)
                            <tr>
                                <td><span class="ops-name">{{ $run->task_name }}</span></td>
                                <td>{{ optional($run->scheduled_for)->format('d/m/Y g:i a') }}</td>
                                <td>{{ ucfirst($run->trigger) }}</td>
                                <td><span class="ops-status status-{{ $run->status }}">{{ $run->status }}</span></td>
                                <td>{{ $run->duration_ms !== null ? number_format($run->duration_ms / 1000, 2).'s' : '—' }}</td>
                                <td>{{ $run->messages->where('status','sent')->count() }}</td>
                                <td>
                                    <button class="btn btn-default btn-sm" wire:click="showRun({{ $run->id }})">Details</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">No scheduled run history matches these filters.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if($runs->hasPages())
                    <div class="row">
                        <div class="col-sm-5 dataTables_info">Showing {{ $runs->firstItem() }} to {{ $runs->lastItem() }} of {{ $runs->total() }} results</div>
                        <div class="col-sm-7 text-right">
                            <ul class="pagination pagination-sm">
                                <li class="{{ $runs->onFirstPage() ? 'disabled' : '' }}"><a href="#" wire:click.prevent="previousPage('runsPage')" aria-label="Previous page"><i class="fa fa-chevron-left"></i></a></li>
                                @foreach(range(1, $runs->lastPage()) as $page)
                                    <li class="{{ $runs->currentPage() === $page ? 'active' : '' }}"><a href="#" wire:click.prevent="gotoPage({{ $page }}, 'runsPage')" aria-label="Page {{ $page }}" @if($runs->currentPage() === $page) aria-current="page" @endif>{{ $page }}</a></li>
                                @endforeach
                                <li class="{{ $runs->hasMorePages() ? '' : 'disabled' }}"><a href="#" wire:click.prevent="nextPage('runsPage')" aria-label="Next page"><i class="fa fa-chevron-right"></i></a></li>
                            </ul>
                        </div>
                    </div>
                @endif
            @else
                {{-- Schedule & Recipients --}}
                <div class="ops-tab-tools">
                    <input type="search" class="form-control ops-schedule-search" placeholder="Search schedule, recipient or handler" wire:model.live.debounce.300ms="scheduleSearch">
                    <button type="button" class="btn btn-default ops-archive-toggle {{ $includeArchived ? 'is-active' : '' }}" wire:click="$toggle('includeArchived')" aria-pressed="{{ $includeArchived ? 'true' : 'false' }}"
                            title="{{ $includeArchived ? 'Hide archived operations' : 'Show archived operations' }}">
                        <i class="fa {{ $includeArchived ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                    </button>
                    <button type="button" class="btn btn-default ops-sort-toggle" wire:click="toggleScheduleSort"
                            title="Switch to {{ $scheduleSort === 'name' ? 'day/schedule' : 'name' }} order" aria-label="Currently sorted by {{ $scheduleSort === 'name' ? 'name' : 'day and schedule' }}; switch order">
                        <i class="fa {{ $scheduleSort === 'name' ? 'fa-sort-alpha-asc' : 'fa-calendar' }}"></i> {{ $scheduleSort === 'name' ? 'Name order' : 'Day order' }}
                    </button>
                    <a class="btn btn-default" href="/settings/notifications"><i class="fa fa-envelope"></i> Notifications</a>
                    <button class="btn btn-default" wire:click="openCategoryManager"><i class="fa fa-folder-open"></i> Categories</button>
                    <button class="btn green" wire:click="openAddOperation"><i class="fa fa-plus"></i> Add operation</button>
                </div>
                @forelse($definitions as $category => $items)
                    @php
                        // Search results open automatically so a matching operation is visible even when its category was previously collapsed.
                        $categoryCollapsed = trim($scheduleSearch) === '' && in_array($category, $collapsedScheduleCategories, true);
                    @endphp
                    <section class="ops-category-section" wire:key="schedule-category-{{ $category }}">
                        <button class="ops-category-toggle" type="button" wire:click="toggleScheduleCategory('{{ $category }}')" aria-expanded="{{ $categoryCollapsed ? 'false' : 'true' }}">
                            <span>
                                <strong>{{ $categoryLabels[$category] ?? str_replace('_',' ',$category) }}</strong>
                                <small>{{ count($items) }} operation{{ count($items) === 1 ? '' : 's' }}</small>
                            </span>
                            <i class="fa {{ $categoryCollapsed ? 'fa-chevron-down' : 'fa-chevron-up' }}"></i>
                        </button>
                        @unless($categoryCollapsed)
                            <div class="ops-schedule-grid">
                                @foreach($items as $definition)
                                    <div class="ops-schedule {{ !$definition['enabled'] ? 'ops-off' : '' }}">
                                        <div>
                                            <span class="ops-name">{{ $definition['name'] }}</span>
                                            @if($definition['archived'] ?? false)
                                                <span class="ops-status status-skipped ops-disabled-label">Archived</span>
                                            @elseif(!$definition['enabled'])
                                                <span class="ops-status status-skipped ops-disabled-label">Disabled</span>
                                            @endif
                                            @if($definition['description'])
                                                <span class="ops-schedule-description">{{ $definition['description'] }}</span>
                                            @endif
                                            {{--}}<span class="ops-handler-info">
                                                <span class="ops-handler-badge ops-handler-{{ $definition['handler_type'] }}">{{ $definition['handler_type_label'] }}</span>
                                                <span class="ops-handler-code">{{ $definition['handler_label'] }}</span>
                                            </span>--}}
                                        </div>
                                        <div><strong>{{ $definition['schedule_label'] }}</strong></div>
                                        <div class="ops-recipient">
                                            {{ $definition['recipient_label'] }}
                                        </div>
                                        <div>
                                            @if($definition['archived'] ?? false)
                                                <button class="btn btn-sm  btn-default" wire:click="restoreOperation({{ $definition['definition_id'] }})"><i class="fa fa-undo"></i> Restore</button>
                                            @else
                                                <button class="btn btn-sm btn-default" wire:click="editSettings('{{ $definition['key'] }}')" title="Operation settings"><i class="fa fa-cog"></i></button>
                                                <button class="btn btn-sm btn-default" wire:click="openOperationLog('{{ $definition['key'] }}')"><i class="fa fa-history"></i></button>
                                                <button class="btn btn-sm green" wire:click="requestRun('{{ $definition['key'] }}')"><i class="fa fa-play"></i> Run</button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endunless
                    </section>
                @empty
                    <div class="note note-info">No schedules match your search and archive filters.</div>
                @endforelse
            @endif
        </div>
    </div>

    <x-ui.modal :show="(bool) $selectedRun" title="Scheduled operation details" close-action="closeModals" max-width="850px" class="scheduled-ops-modal">
        @if($selectedMessage)
            <iframe class="ops-email-preview" sandbox="allow-same-origin" referrerpolicy="no-referrer" src="{{ route('scheduled-operations.message-preview', $selectedMessage) }}" title="Email preview"></iframe>
            <x-slot name="footer">
                <button class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeSelectedMessagePreview"><i class="fa fa-arrow-left"></i> Back</button>
                <button class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Close</button>
            </x-slot>
        @elseif($selectedRun)
            <h3 class="font-grey-cascade">{{ $selectedRun->task_name }}</h3>
            <div class="ops-detail-grid">
                <div class="ops-detail ops-detail-status ops-detail-status-{{ $selectedRun->status }}"><span>Status</span><strong>{{ ucfirst($selectedRun->status) }}</strong></div>
                <div class="ops-detail"><span>Scheduled</span><strong>{{ optional($selectedRun->scheduled_for)->format('d/m/Y g:i a') }}</strong></div>
                <div class="ops-detail"><span>Attempt / duration</span><strong>{{ $selectedRun->attempt }} / {{ $selectedRun->duration_ms !== null ? number_format($selectedRun->duration_ms / 1000, 2).'s' : '—' }}</strong></div>
            </div>

            @if($selectedRun->exception_message)
                <div class="note note-danger"><strong>{{ $selectedRun->exception_class }}</strong><br>{{ $selectedRun->exception_message }}<br><small>{{ $selectedRun->exception_file }}:{{ $selectedRun->exception_line }}</small></div>
            @endif

            <h4>Output</h4>
            <pre class="ops-output">{{ $selectedRun->output ?: 'No console output was produced.' }}</pre>

            <h4 class="margin-top-20">Emails sent ({{ $selectedRun->messages->where('status','sent')->count() }})</h4>
            @forelse($selectedRun->messages as $message)
                <div class="ops-mail">
                    <strong>{{ $message->subject ?: '(No subject)' }}</strong>
                    <span class="ops-status status-{{ $message->status === 'sent' ? 'successful' : 'failed' }} pull-right">{{ $message->status }}</span>
                    <small>To: {{ $message->recipients->where('type','to')->pluck('email')->join(', ') ?: 'No recipients captured' }}</small>
                    <small>CC/BCC: {{ $message->recipients->whereIn('type',['cc','bcc'])->pluck('email')->join(', ') ?: 'None' }}</small>
                    <div class="ops-mail-actions">
                        @if($message->html_body || $message->text_body)
                            <button type="button" class="btn btn-default btn-sm" wire:click="previewSelectedMessage({{ $message->id }})"><i class="fa fa-envelope-open"></i> View email</button>
                        @endif
                        @foreach($message->archivedAttachments as $attachment)
                            <a class="btn btn-default btn-sm" href="{{ route('scheduled-report-attachments.download', $attachment) }}"><i class="fa fa-paperclip"></i> {{ $attachment->original_name }}</a>
                        @endforeach
                    </div>
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

    <x-ui.modal :show="(bool) $logTaskKey" title="Operation log" close-action="closeOperationLog" max-width="920px" class="scheduled-ops-modal">
        @if($logMessage)
            <h3 class="font-grey-cascade">{{ $logMessage->subject ?: '(No subject)' }}</h3>
            <iframe class="ops-email-preview" sandbox="allow-same-origin" referrerpolicy="no-referrer" src="{{ route('scheduled-operations.message-preview', $logMessage) }}" title="Email preview"></iframe>
            <x-slot name="footer">
                <button class="sws-modal-btn sws-modal-btn-secondary" wire:click="backToLogRun"><i class="fa fa-arrow-left"></i> Back to run</button>
                <button class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeOperationLog">Close</button>
            </x-slot>
        @elseif($logRun)
            <button type="button" class="btn btn-default btn-sm" wire:click="backToLogList"><i class="fa fa-arrow-left"></i> All recent runs</button>
            <h3 class="font-grey-cascade">{{ $logRun->task_name }}</h3>
            <div class="ops-detail-grid">
                <div class="ops-detail ops-detail-status ops-detail-status-{{ $logRun->status }}"><span>Status</span><strong>{{ ucfirst($logRun->status) }}</strong></div>
                <div class="ops-detail"><span>Executed</span><strong>{{ optional($logRun->started_at ?: $logRun->scheduled_for)->format('d/m/Y g:i a') }}</strong></div>
                <div class="ops-detail"><span>Trigger / duration</span><strong>{{ ucfirst($logRun->trigger) }} / {{ $logRun->duration_ms !== null ? number_format($logRun->duration_ms / 1000, 2).'s' : '—' }}</strong></div>
            </div>
            @if($logRun->exception_message)
                <div class="note note-danger"><strong>{{ $logRun->exception_class }}</strong><br>{{ $logRun->exception_message }}<br><small>{{ $logRun->exception_file }}:{{ $logRun->exception_line }}</small></div>
            @endif
            <h4>Emails ({{ $logRun->messages->count() }})</h4>
            @forelse($logRun->messages as $message)
                <div class="ops-mail">
                    <strong>{{ $message->subject ?: '(No subject)' }}</strong>
                    <span class="ops-status status-{{ $message->status === 'sent' ? 'successful' : 'failed' }} pull-right">{{ $message->status }}</span>
                    <small>To: {{ $message->recipients->where('type','to')->pluck('email')->join(', ') ?: 'No recipients captured' }}</small>
                    <small>CC/BCC: {{ $message->recipients->whereIn('type',['cc','bcc'])->pluck('email')->join(', ') ?: 'None' }}</small>
                    <div class="ops-mail-actions">
                        @if($message->html_body || $message->text_body)
                            <button type="button" class="btn btn-default btn-sm" wire:click="showLogMessage({{ $message->id }})"><i class="fa fa-envelope-open"></i> View email</button>
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
                <button class="sws-modal-btn sws-modal-btn-secondary" wire:click="backToLogList">Back</button>
                <button class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeOperationLog">Close</button>
            </x-slot>
        @else
            <h3 class="font-grey-cascade">{{ $logDefinition?->name }}</h3>
            <p class="help-block">The latest 20 executions are shown. Email content follows the scheduler history period; attachment files follow the separate retention policy.</p>
            <div class="ops-log-list">
                @forelse($logRuns as $run)
                    <button type="button" class="ops-log-row" wire:click="showLogRun({{ $run->id }})">
                        <strong>{{ optional($run->started_at ?: $run->scheduled_for)->format('d/m/Y g:i a') }}</strong>
                        <span>{{ ucfirst($run->trigger) }}</span>
                        <span class="ops-status status-{{ $run->status }}">{{ $run->status }}</span>
                        <span>{{ $run->duration_ms !== null ? number_format($run->duration_ms / 1000, 2).'s' : '—' }}</span>
                        <span>{{ $run->sent_messages_count }} email{{ $run->sent_messages_count === 1 ? '' : 's' }} <i class="fa fa-chevron-right"></i></span>
                    </button>
                @empty
                    <div class="well well-sm">This operation has not run yet.</div>
                @endforelse
            </div>
            <x-slot name="footer">
                <button class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeOperationLog">Close</button>
            </x-slot>
        @endif
    </x-ui.modal>

    <x-ui.confirm-modal :show="$showRunConfirm" title="Run operation now?" close-action="closeModals" confirm-action="confirmRun" confirm-label="Add to queue" loading-target="confirmRun">
        This runs<br><span class="sws-confirm-item">{{ $pendingDefinition['name'] ?? '' }}</span><br><br>independently of its normal schedule. Any emails and data changes are real.
    </x-ui.confirm-modal>

    <x-ui.confirm-modal :show="$showRetryConfirm" title="Retry failed operation?" close-action="closeModals" confirm-action="confirmRetry" confirm-label="Retry operation" loading-target="confirmRetry">
        This creates a new auditable attempt for<br><span class="sws-confirm-item">{{ $pendingDefinition['name'] ?? '' }}</span><br><br>The original failed run is preserved.
    </x-ui.confirm-modal>

    <x-ui.confirm-modal :show="$showArchiveConfirm" title="Archive operation?" close-action="closeModals" confirm-action="confirmArchive" confirm-label="Archive operation" loading-target="confirmArchive">
        Archive<br><span class="sws-confirm-item">{{ $pendingArchiveName }}</span><br><br>It will be disabled, removed from normal scheduling, and unavailable for manual runs. Its settings, recipient rules and history will be preserved and it can be restored later.
    </x-ui.confirm-modal>

    <x-ui.modal :show="$showSettings" title="Operation settings" close-action="closeModals" max-width="980px" class="scheduled-ops-modal">
        @if($settingsDefinition)
            <div class="ops-form-grid">
                <x-form.input name="settingName" label="Display name" wire:model="settingName"/>
                <div class="form-group">
                    <label class="control-label">Category</label>
                    <div class="ops-category-field">
                        <div class="ops-select-host" wire:key="setting-category-{{ $settingDefinitionId }}-{{ $categorySelectVersion }}-{{ $settingCategory }}" wire:ignore>
                            <select class="form-control bs-select ops-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('settingCategory', $el.value)">
                                @foreach($categories as $category)
                                    @if($category->enabled || $category->slug === $settingCategory)
                                        <option value="{{ $category->slug }}" @selected($settingCategory === $category->slug)>{{ $category->name }}{{ !$category->enabled ? ' (disabled)' : '' }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-default" type="button" wire:click="openCategoryManager" title="Manage categories"><i class="fa fa-cog"></i></button>
                    </div>
                    @error('settingCategory')<span class="help-block">{{ $message }}</span>@enderror
                </div>
                <div class="ops-form-span-2">
                    <x-form.textarea name="settingDescription" label="Description" wire:model="settingDescription" rows="3"/>
                </div>
            </div>

            <label class="ops-status-toggle">
                <input type="checkbox" wire:model="settingEnabled" aria-label="Enable automatic runs">
                <span class="ops-status-track"><span class="ops-status-disabled">Disabled</span><span class="ops-status-enabled">Enabled</span></span>
            </label>

            <h4>Schedule <small>(Sydney time)</small></h4>
            <div class="ops-form-grid">
                <div class="ops-select-host" wire:key="setting-frequency-{{ $settingDefinitionId }}-{{ $settingScheduleType }}">
                    <x-form.select name="settingScheduleType" label="Frequency" :value="$settingScheduleType" plugin="bs-select ops-select" data-width="100%" wire:ignore x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()"
                                   x-on:change="$wire.set('settingScheduleType', $el.value)">
                        <option value="hourly" @selected($settingScheduleType === 'hourly')>Every hour</option>
                        <option value="daily" @selected($settingScheduleType === 'daily')>Daily</option>
                        <option value="weekdays" @selected($settingScheduleType === 'weekdays')>Every weekday</option>
                        <option value="weekly" @selected($settingScheduleType === 'weekly')>Selected weekdays</option>
                        <option value="fortnightly" @selected($settingScheduleType === 'fortnightly')>Fortnightly</option>
                        <option value="monthly_nth_weekday" @selected($settingScheduleType === 'monthly_nth_weekday')>Monthly — numbered weekday</option>
                        <option value="monthly_last_weekday" @selected($settingScheduleType === 'monthly_last_weekday')>Monthly — last weekday</option>
                        <option value="monthly_day" @selected($settingScheduleType === 'monthly_day')>Monthly — day of month</option>
                        <option value="quarterly" @selected($settingScheduleType === 'quarterly')>Selected months</option>
                    </x-form.select>
                </div>
                @if($settingScheduleType === 'weekly')
                    <div class="form-group">
                        <label class="control-label">Run on</label>
                        <div class="ops-day-toggle" role="group" aria-label="Run on weekdays">
                            @foreach([1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri'] as $number => $day)
                                <label><input type="checkbox" value="{{ $number }}" wire:model="settingWeekdays"><span>{{ $day }}</span></label>
                            @endforeach
                        </div>
                        @error('settingWeekdays')<span class="help-block">{{ $message }}</span>@enderror
                    </div>
                @endif

                @if(in_array($settingScheduleType, ['fortnightly','monthly_nth_weekday','monthly_last_weekday'], true))
                    <div class="ops-select-host" wire:key="setting-weekday-{{ $settingDefinitionId }}-{{ $settingWeekday }}">
                        <x-form.select name="settingWeekday" label="Weekday" :value="$settingWeekday" plugin="bs-select ops-select" data-width="100%" wire:ignore x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()"
                                       x-on:change="$wire.set('settingWeekday', Number($el.value))">
                            @foreach([1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'] as $number => $day)
                                <option value="{{ $number }}" @selected((int) $settingWeekday === $number)>{{ $day }}</option>
                            @endforeach
                        </x-form.select>
                    </div>
                @endif
                @if($settingScheduleType === 'fortnightly')
                    <div>
                        <x-form.input name="settingAnchor" label="Anchor date" type="date" wire:model="settingAnchor"/>
                        <span class="help-block">Choose one date that belongs to the intended fortnight.</span></div>
                @elseif($settingScheduleType === 'monthly_nth_weekday')
                    <div class="ops-select-host" wire:key="setting-occurrence-{{ $settingDefinitionId }}-{{ $settingOccurrence }}">
                        <x-form.select name="settingOccurrence" label="Occurrence" :value="$settingOccurrence" plugin="bs-select ops-select" data-width="100%" wire:ignore x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()"
                                       x-on:change="$wire.set('settingOccurrence', Number($el.value))">
                            <option value="1" @selected((int) $settingOccurrence === 1)>First</option>
                            <option value="2" @selected((int) $settingOccurrence === 2)>Second</option>
                            <option value="3" @selected((int) $settingOccurrence === 3)>Third</option>
                            <option value="4" @selected((int) $settingOccurrence === 4)>Fourth</option>
                            <option value="5" @selected((int) $settingOccurrence === 5)>Fifth</option>
                        </x-form.select>
                    </div>
                @elseif(in_array($settingScheduleType, ['monthly_day','quarterly'], true))
                    <div>
                        <x-form.input name="settingDay" label="Day of month" type="number" min="1" max="28" wire:model="settingDay"/>
                        <span class="help-block">Limited to 1–28 so it exists every month.</span></div>
                @endif
                @if($settingScheduleType === 'quarterly')
                    <div class="form-group ops-form-span-2">
                        <label class="control-label">Run in these months</label>
                        <div class="ops-month-toggle" role="group" aria-label="Run in selected months">
                            @foreach([1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'] as $number => $month)
                                <label><input type="checkbox" value="{{ $number }}" wire:model="settingMonths"><span>{{ $month }}</span></label>
                            @endforeach
                        </div>
                        @error('settingMonths')<span class="help-block">{{ $message }}</span>@enderror
                    </div>
                @endif
            </div>

            <button class="ops-advanced-toggle" type="button" wire:click="$toggle('showAdvancedSettings')">
                <i class="fa {{ $showAdvancedSettings ? 'fa-chevron-up' : 'fa-chevron-down' }}"></i>
                {{ $showAdvancedSettings ? 'Hide advanced settings' : 'Advanced settings' }}
            </button>
            @if($showAdvancedSettings)
                <div class="well well-sm">
                    <div class="ops-form-grid-3">
                        @if($settingScheduleType === 'hourly')
                            <x-form.input name="settingMinute" label="Minute past the hour" type="number" min="0" max="59" wire:model="settingMinute"/>
                        @else
                            <x-form.input name="settingTime" label="Run time" type="time" wire:model="settingTime"/>
                        @endif
                        <x-form.input name="settingTries" label="Maximum attempts" type="number" min="1" max="10" wire:model="settingTries"/>
                        <div>
                            <x-form.input name="settingTimeout" label="Timeout (seconds)" type="number" min="30" max="300" wire:model="settingTimeout"/>
                            <span class="help-block">Maximum 300 seconds to match the current Forge worker.</span></div>
                    </div>
                    <div class="ops-archive-panel">
                        @if($settingCanBeClientConfigurable && $settingCategory === 'report')
                            <div class="ops-client-setting">
                                <label class="ops-status-toggle" title="Show this report under the client's Settings > Notifications page">
                                    <input type="checkbox" wire:model="settingClientConfigurable" aria-label="Show in client scheduled report settings">
                                    <span class="ops-status-track"><span class="ops-status-disabled">No</span><span class="ops-status-enabled">Yes</span></span>
                                </label>
                                <strong>Client report settings</strong>
                            </div>
                        @else
                            <div><strong>Archive operation</strong><span>Stops future scheduled and manual runs while preserving settings and history.</span></div>
                        @endif
                        <button class="btn red" type="button" wire:click="requestArchive"><i class="fa fa-archive"></i> Archive operation</button>
                    </div>
                    @error('settingClientConfigurable')<span class="help-block">{{ $message }}</span>@enderror
                </div>
            @endif

            @if($settingSendsEmail)
                <div class="well">
                    <h4>Email recipients</h4>
                    @php
                        $automaticRecipients = $settingsDefinition['dynamicRecipients'] ?? [];
                    @endphp
                    @if($automaticRecipients)
                        <div class="note note-info">
                            <strong>Recipients selected automatically by this operation</strong>
                            @foreach($automaticRecipients as $recipient)
                                <div class="ops-dynamic-recipient">
                                    <span class="label label-info">{{ strtoupper($recipient['delivery']) }}</span> {{ $recipient['label'] }}
                                    @if($recipient['description'] ?? null)
                                        <small>{{ $recipient['description'] }}</small>
                                    @endif
                                </div>
                            @endforeach
                            <div class="help-block margin-top-10">These recipients are protected because they are selected from the records processed by each email. Add fixed recipients below only when someone should receive every email from this operation.</div>
                        </div>
                    @endif
                    <x-form.input name="settingRecipientSummary" label="Summary shown in list" wire:model="settingRecipientSummary" placeholder="e.g. Site supervisors and WHS group"/>

                    @foreach($recipientRules as $index => $rule)
                        <div class="ops-rule" wire:key="recipient-rule-{{ $index }}">
                            <div class="ops-select-host" wire:key="recipient-delivery-{{ $settingDefinitionId }}-{{ $index }}-{{ $rule['delivery_type'] ?? '' }}" wire:ignore>
                                <select class="form-control bs-select ops-select" data-width="100%" aria-label="Delivery type" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('recipientRules.{{ $index }}.delivery_type', $el.value)">
                                    <option value="to" @selected(($rule['delivery_type'] ?? '') === 'to')>To</option>
                                    <option value="cc" @selected(($rule['delivery_type'] ?? '') === 'cc')>CC</option>
                                    <option value="bcc" @selected(($rule['delivery_type'] ?? '') === 'bcc')>BCC</option>
                                </select>
                            </div>
                            <div class="ops-select-host" wire:key="recipient-source-{{ $settingDefinitionId }}-{{ $index }}-{{ $rule['source_type'] ?? '' }}" wire:ignore>
                                <select class="form-control bs-select ops-select" data-width="100%" aria-label="Recipient source" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('recipientRules.{{ $index }}.source_type', $el.value)">
                                    <option value="user" @selected(($rule['source_type'] ?? '') === 'user')>User</option>
                                    <option value="notification_group" @selected(($rule['source_type'] ?? '') === 'notification_group')>Notification group</option>
                                    <option value="manual" @selected(($rule['source_type'] ?? '') === 'manual')>Email address</option>
                                </select>
                            </div>
                            @if(($rule['source_type'] ?? '') === 'user')
                                @php
                                    $selectedUserIds = collect(is_array($rule['source_value'] ?? null) ? $rule['source_value'] : [])
                                        ->map(fn($id) => (string) $id);
                                @endphp
                                <div class="ops-select-host" wire:key="recipient-user-value-{{ $settingDefinitionId }}-{{ $index }}" wire:ignore>
                                    <select class="form-control" multiple
                                            x-init="const parent = $($el).closest('.sws-modal-card'); $($el).select2({width: '100%', placeholder: 'Select one or more users', dropdownParent: parent.length ? parent : $(document.body)}).on('change', function () { $wire.set('recipientRules.{{ $index }}.source_value', $(this).val() || []); })">
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" @selected($selectedUserIds->contains((string) $user->id))>{{ $user->fullname }} ({{ $user->company?->name_alias ?? 'Unknown company' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            @elseif(($rule['source_type'] ?? '') === 'notification_group')
                                <div class="ops-select-host" wire:key="recipient-group-value-{{ $settingDefinitionId }}-{{ $index }}-{{ $rule['source_value'] ?? '' }}" wire:ignore>
                                    <select class="form-control bs-select ops-select" data-width="100%" data-live-search="true" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('recipientRules.{{ $index }}.source_value', $el.value)">
                                        <option value="">Select notification group</option>
                                        @foreach($notificationGroups as $group)
                                            <option value="{{ $group->id }}" @selected((string) ($rule['source_value'] ?? '') === (string) $group->id)>{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <input class="form-control" type="email" wire:model="recipientRules.{{ $index }}.source_value" placeholder="person@example.com">
                            @endif
                            <button class="btn btn-default ops-rule-remove" wire:click="removeRecipientRule({{ $index }})" title="Remove recipient"><i class="fa fa-trash"></i></button>
                        </div>
                        @error('recipientRules.'.$index.'.delivery_type')<span class="help-block">{{ $message }}</span>@enderror
                        @error('recipientRules.'.$index.'.source_type')<span class="help-block">{{ $message }}</span>@enderror
                        @error('recipientRules.'.$index.'.source_value')<span class="help-block">{{ $message }}</span>@enderror
                    @endforeach
                    @error('recipientRules')<span class="help-block">{{ $message }}</span>@enderror
                    <button class="btn btn-default margin-top-10" wire:click="addRecipientRule"><i class="fa fa-plus"></i> Add recipient rule</button>
                    <span class="help-block">Additional recipients receive every email sent by this operation. Select several users in one User rule; use a separate Email address rule for each manual address.</span>
                </div>
            @else
                <div class="well">
                    <h4>Email recipients</h4>
                    <p class="help-block">No email is sent by this operation, so recipient settings are not required.</p>
                </div>
            @endif

            @if($changeLogs->isNotEmpty())
                <div class="ops-activity" x-data="{ open: false }">
                    <button type="button" class="ops-advanced-toggle" x-on:click="open = !open"><i class="fa" x-bind:class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i> <span x-text="open ? 'Hide recent changes' : 'Show recent changes'"></span></button>
                    <div x-cloak x-show="open" class="margin-top-10">
                        @foreach($changeLogs as $change)
                            <div>{{ $change->created_at->format('d/m/Y g:i a') }} — {{ str_replace('_',' ',$change->action) }}{{ $change->user ? ' by '.$change->user->fullname : '' }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <x-slot name="footer">
                @if($hasLegacyDefault)
                    {{--}}<button class="sws-modal-btn sws-modal-btn-secondary" wire:click="resetSettings">Restore defaults</button>--}}
                @endif
                <button class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Cancel</button>
                <button class="sws-modal-btn sws-modal-btn-primary" wire:click="saveSettings" wire:loading.attr="disabled" wire:target="saveSettings">Save operation</button>
            </x-slot>
        @endif
    </x-ui.modal>

    <x-ui.modal :show="$showCategoryManager" title="Operation categories" close-action="closeCategoryManager" max-width="760px" class="scheduled-ops-modal">
        <p class="help-block">Drag the handles to set the dashboard order. The eye controls whether a category is available; internal slugs stay fixed so existing handlers and run history remain compatible.</p>

        <div class="ops-form-grid ops-category-add">
            <div class="ops-category-add-field">
                <x-form.input name="newCategoryName" label="New category" wire:model="newCategoryName" placeholder="e.g. Safety reports"/>
            </div>
            <div>
                <button class="btn green" type="button" wire:click="addCategory"><i class="fa fa-plus"></i> Add category</button>
            </div>
        </div>

        <div class="ops-category-sort" x-data="{ draggedRow: null }"
             x-on:dragstart="draggedRow = $event.target.closest('.ops-category-row'); if (!draggedRow) return; draggedRow.classList.add('is-dragging'); $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('text/plain', draggedRow.dataset.categoryId)"
             x-on:dragend="draggedRow?.classList.remove('is-dragging'); $el.querySelectorAll('.is-drag-over').forEach((row) => row.classList.remove('is-drag-over')); draggedRow = null"
             x-on:dragover.prevent="if (!draggedRow) return; const target = $event.target.closest('.ops-category-row'); if (!target || target === draggedRow) return; $el.querySelectorAll('.is-drag-over').forEach((row) => row.classList.remove('is-drag-over')); target.classList.add('is-drag-over'); const after = $event.clientY > target.getBoundingClientRect().top + (target.offsetHeight / 2); $el.insertBefore(draggedRow, after ? target.nextSibling : target)"
             x-on:drop.prevent="if (!draggedRow) return; $wire.reorderCategories(Array.from($el.querySelectorAll('.ops-category-row')).map((row) => row.dataset.categoryId))">
            @foreach($categoryRows as $rowKey => $category)
                <div class="ops-category-row" data-category-id="{{ $category['id'] }}" wire:key="operation-category-{{ $category['id'] }}">
                    <button class="ops-drag-handle" type="button" draggable="true" title="Drag to reorder" aria-label="Drag {{ $category['name'] }} to reorder"><i class="fa fa-bars"></i></button>
                    <button class="ops-visibility {{ $category['enabled'] ? 'is-enabled' : 'is-disabled' }}" type="button" wire:click="toggleCategoryEnabled('{{ $rowKey }}')" title="{{ $category['enabled'] ? 'Disable' : 'Enable' }} {{ $category['name'] }}"
                            aria-pressed="{{ $category['enabled'] ? 'true' : 'false' }}">
                        <i class="fa {{ $category['enabled'] ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                        <span class="sr-only">{{ $category['enabled'] ? 'Enabled' : 'Disabled' }}</span>
                    </button>
                    <div>
                        <input class="form-control" type="text" wire:model="categoryRows.{{ $rowKey }}.name">
                        @error('categoryRows.'.$rowKey.'.name')<span class="help-block">{{ $message }}</span>@enderror
                    </div>
                    <span class="ops-slug">{{ $category['slug'] }}</span>
                    <small>{{ $categoryOperationCounts[$category['slug']] ?? 0 }} operation(s)</small>
                </div>
            @endforeach
        </div>

        <x-slot name="footer">
            <button class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeCategoryManager">Cancel</button>
            <button class="sws-modal-btn sws-modal-btn-primary" wire:click="saveCategories" wire:loading.attr="disabled" wire:target="saveCategories">Save categories</button>
        </x-slot>
    </x-ui.modal>

    <x-ui.modal :show="$showAddOperation" title="Add scheduled operation" close-action="closeModals" max-width="760px" class="scheduled-ops-modal">
        <p>Code handlers found in <code>app/Scheduled/Operations</code> appear here. Installing one creates a disabled operation so its schedule and recipients can be reviewed safely.</p>
        @forelse($availableHandlers as $handler)
            <div class="list-group-item ops-handler">
                <div>
                    <span class="ops-name">{{ $handler['name'] }}</span>
                    <span class="ops-key">{{ $handler['key'] }}</span>
                    <small>{{ $handler['description'] }}</small>
                </div>
                <button class="btn green" wire:click="installHandler('{{ $handler['handler_key'] }}')">Install</button>
            </div>
        @empty
            <div class="note note-info">There are no unconfigured handlers. Add a class implementing <code>ScheduledOperationHandler</code>, deploy it, then run <code>php artisan scheduled:sync</code>.</div>
        @endforelse
        <x-slot name="footer">
            <button class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Close</button>
        </x-slot>
    </x-ui.modal>

</div>
