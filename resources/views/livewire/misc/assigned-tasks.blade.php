<div>
    <div class="row">
        <div class="col-md-12">
            <h4 class="clearfix" style="margin-bottom: 5px">
                Assigned Tasks
                @if ($canAdd)
                    <button type="button" wire:click="add" class="btn btn-circle green btn-outline btn-sm pull-right">Add</button>
                @endif
            </h4>
            <hr style="padding: 0; margin: 0 0 10px 0">

            @if ($tasks->isNotEmpty())
                <table class="table table-striped table-bordered table-nohover order-column">
                    <thead>
                    <tr class="mytable-header">
                        <th style="width:5%">#</th>
                        <th>Action</th>
                        <th style="width:15%">Created by</th>
                        <th style="width:15%">Completed by</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($tasks as $todo)
                        <tr wire:key="assigned-task-{{ $todo->id }}">
                            <td>
                                <div class="text-center"><a href="/todo/{{ $todo->id }}"><i class="fa fa-search"></i></a></div>
                            </td>
                            <td>
                                {{ $todo->info }}<br><br>
                                <i>Assigned to: {{ $todo->users->pluck('user')->filter()->map(fn($user) => $user->fullname)->join(', ') }}</i>
                                @if ($todo->comments)
                                    <br><b>Comments:</b> {{ $todo->comments }}
                                @endif

                                @php
                                    $images = $todo->attachments->where('type', 'image');
                                    $files = $todo->attachments->where('type', 'file');
                                @endphp

                                @if ($todo->attachments->isNotEmpty())
                                    <hr style="margin: 10px 0; padding: 0">
                                    @if ($images->isNotEmpty())
                                        <div class="row" style="margin: 0">
                                            @foreach ($images as $attachment)
                                                <div style="width:60px; float:left; padding-right:5px">
                                                    <a href="{{ $attachment->url }}" target="_blank" data-lity><img src="{{ $attachment->url }}" class="thumbnail img-responsive img-thumbnail"></a>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    @foreach ($files as $attachment)
                                        <i class="fa fa-file-text-o"></i> &nbsp;<a href="{{ $attachment->url }}" target="_blank">{{ $attachment->name }}</a><br>
                                    @endforeach
                                @endif

                                @if ($todo->attachment)
                                    <br><a href="{{ $todo->attachmentUrl }}" data-lity class="btn btn-xs blue"><i class="fa fa-picture-o"></i></a>
                                @endif
                            </td>
                            <td>{{ $todo->createdBy?->full_name ?? $todo->createdBy?->fullname ?? 'Unknown' }}<br>{{ $todo->created_at->format('d/m/Y') }}</td>
                            <td>
                                @if ($todo->status && !$todo->done_by)
                                    <span class="font-red">Outstanding</span>
                                @else
                                    {{ $todo->doneBy?->full_name ?? $todo->doneBy?->fullname ?? 'Unknown' }}<br>{{ $todo->done_at?->format('d/m/Y') }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <x-ui.modal :show="$showAddModal" title="Add Assigned Task" close-action="close" max-width="760px">
        <x-form.textarea name="info" label="Description of what to do" rows="4" wire:model="info"/>

        <div class="row">
            <div class="col-md-4">
                <x-form.datepicker name="dueAt" label="Due Date" :value="$dueAt" format="dd/mm/yyyy" start-date="+0d" clear-button readonly wire:ignore
                                   x-init="
                                       const picker = $($el).closest('.date-picker');
                                       if (!picker.data('datepicker')) {
                                           picker.datepicker({
                                               rtl: typeof App !== 'undefined' ? App.isRTL() : false,
                                               orientation: 'left',
                                               autoclose: true
                                           });
                                       }
                                       picker.on('changeDate clearDate', function () {
                                           $wire.set('dueAt', $(this).find('input').val() || '');
                                       });
                                   "/>
            </div>

            <div class="col-md-4">
                <div wire:ignore>
                    <x-form.select name="assignTo" label="Send To" :options="Auth::user()->company->subscription ? ['' => 'Select type', 'user' => 'User', 'company' => 'Company', 'role' => 'Role'] : ['' => 'Select type', 'user' => 'User']"
                                   :value="$assignTo" plugin="bs-select" data-width="100%"
                                   x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()"
                                   x-on:change="$wire.set('assignTo', $el.value)"/>
                </div>
            </div>
        </div>

        @if ($assignTo === 'user')
            <div wire:key="assigned-task-users-select" wire:ignore>
                <x-form.select name="userList[]" id="assigned_task_users" label="User(s)" :options="$userOptions" :value="$userList" plugin="select2" multiple style="width:100%"
                               x-init="const parent = $($el).closest('.sws-modal-card'); $($el).select2({width: '100%', placeholder: 'Select one or more users', dropdownParent: parent.length ? parent : $(document.body)}).on('change', function () { $wire.set('userList', $(this).val() || []); })"/>
            </div>
        @elseif ($assignTo === 'company')
            <div wire:key="assigned-task-companies-select" wire:ignore>
                <x-form.select name="companyList[]" id="assigned_task_companies" label="Company(s)" :options="$companyOptions" :value="$companyList" plugin="select2" multiple style="width:100%"
                               x-init="const parent = $($el).closest('.sws-modal-card'); $($el).select2({width: '100%', placeholder: 'Select one or more companies', dropdownParent: parent.length ? parent : $(document.body)}).on('change', function () { $wire.set('companyList', $(this).val() || []); })"/>
            </div>
        @elseif ($assignTo === 'role')
            <div wire:key="assigned-task-roles-select" wire:ignore>
                <x-form.select name="roleList[]" id="assigned_task_roles" label="Role(s)" :options="$roleOptions" :value="$roleList" plugin="select2" multiple style="width:100%"
                               x-init="const parent = $($el).closest('.sws-modal-card'); $($el).select2({width: '100%', placeholder: 'Select one or more roles', dropdownParent: parent.length ? parent : $(document.body)}).on('change', function () { $wire.set('roleList', $(this).val() || []); })"/>
            </div>
        @endif

        <div class="form-group">
            <label class="control-label">Attachments</label>
            <x-livewire-filepond wire:model="uploads" multiple/>
            @error('uploads.*')<span class="help-block font-red">{{ $message }}</span>@enderror
            <div wire:loading wire:target="uploads" class="font-grey-silver" style="margin-top:6px">Uploading...</div>
        </div>

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="close">Cancel</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="save" wire:loading.attr="disabled" wire:target="save,uploads">Create Task</button>
        </x-slot>
    </x-ui.modal>
</div>
