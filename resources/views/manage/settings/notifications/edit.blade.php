@inject('notificationTypes', 'App\Http\Utilities\SettingsNotificationTypes')
@inject('companyDocTypes', 'App\Http\Utilities\CompanyDocTypes')
@extends('layout')

@php
    $reportSettingsMode = config('scheduled_operations.report_settings_mode', 'legacy');
    $reportSettingsMode = in_array($reportSettingsMode, ['legacy', 'preview', 'live'], true) ? $reportSettingsMode : 'legacy';
    $canPreviewReports = $reportSettingsMode === 'preview' && Auth::user()->hasRole2('web-admin');
    $canUseLiveReports = $reportSettingsMode === 'live'
        && (Auth::user()->hasRole2('web-admin') || Auth::user()->hasAnyPermissionType('settings'));
    $showScheduledReports = Auth::user()->isCC() && ($canPreviewReports || $canUseLiveReports);
@endphp

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/settings">Settings</a><i class="fa fa-circle"></i></li>
        <li><span>Notifications</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner settings-notifications-page">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-cog"></i>
                            <span class="caption-subject font-green-haze bold uppercase">Notifications</span>
                            <span class="caption-helper"> ID: {{ Auth::user()->company->id }}</span>
                        </div>
                        @if($reportSettingsMode === 'preview' && $showScheduledReports)
                            <div class="actions"><span class="label label-warning">REPORT SETTINGS PREVIEW</span></div>
                        @endif
                    </div>

                    <div class="portlet-body form">
                        @if($showScheduledReports)
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#system-notifications" data-toggle="tab"><i class="fa fa-bell"></i> System Notifications</a></li>
                                <li><a href="#scheduled-reports" data-toggle="tab"><i class="fa fa-file-pdf-o"></i> Scheduled Reports</a></li>
                            </ul>

                            <div class="tab-content" style="padding-top:15px">
                                <div class="tab-pane active" id="system-notifications">
                                    <form method="POST" action="{{ action([\App\Http\Controllers\Misc\SettingsNotificationController::class, 'update'], Auth::user()->company->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        @include('manage.settings.notifications.partials.system-notifications')
                                        <div class="form-actions right">
                                            <a href="/settings" class="btn default">Back</a>
                                            <button type="submit" class="btn green">Save system notifications</button>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane" id="scheduled-reports">
                                    @livewire('settings.scheduled-reports')

                                    @if(Auth::user()->hasRole2('web-admin'))
                                        <details style="margin-top:24px; padding-top:14px; border-top:1px solid #e4e8eb">
                                            <summary style="cursor:pointer; color:#7a858f">Legacy report email lists (migration fallback)</summary>
                                            <form method="POST" action="{{ action([\App\Http\Controllers\Misc\SettingsNotificationController::class, 'update'], Auth::user()->company->id) }}" style="margin-top:10px">
                                                @csrf
                                                @method('PATCH')
                                                @include('manage.settings.notifications.partials.legacy-report-lists')
                                                <div class="form-actions right">
                                                    <button type="submit" class="btn green">Save legacy lists</button>
                                                </div>
                                            </form>
                                        </details>
                                    @endif
                                </div>
                            </div>
                        @else
                            {{-- Exact legacy workflow remains the default and rollback path. --}}
                            <form method="POST" action="{{ action([\App\Http\Controllers\Misc\SettingsNotificationController::class, 'update'], Auth::user()->company->id) }}">
                                @csrf
                                @method('PATCH')
                                @include('manage.settings.notifications.partials.system-notifications')
                                @include('manage.settings.notifications.partials.legacy-report-lists')
                                <div class="form-actions right">
                                    <a href="/settings" class="btn default">Back</a>
                                    <button type="submit" class="btn green">Save</button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (Auth::user()->isCC() && Auth::user()->hasRole2('web-admin'))
        <form method="POST" action="{{ url('/settings/notifications/report') }}">
            @csrf
            <x-ui.bootstrap-modal id="add-report-notification" title="Add Report Email List" max-width="600px">
                <x-form.input name="report_name" label="Name" placeholder="FOC Defective Inspections"/>
                <x-form.input name="report_slug" label="Slug" help="Used by code to retrieve the selected recipients." placeholder="site.foc.defective"/>
                <x-form.input name="report_title" label="Helper title (optional)" help="Heading shown in the ? popover beside the notification name." placeholder="FOC Defective Inspections"/>
                <x-form.textarea name="report_body" label="Helper body (optional)" rows="3" placeholder="Explain what this report sends and who the selected users are."/>
                <x-form.input name="report_brief" label="Text below recipient field (optional)" help="Useful for schedule notes such as Report sent weekly (Monday)." placeholder="Report sent weekly (Monday)"/>
                <x-slot name="footer">
                    <button type="button" class="sws-modal-btn sws-modal-btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="sws-modal-btn sws-modal-btn-primary">Add Email List</button>
                </x-slot>
            </x-ui.bootstrap-modal>
        </form>
    @endif

    <form method="POST" action="{{ url('/settings/notifications/group') }}">
        @csrf
        <x-ui.bootstrap-modal id="add-notification-group" title="Add Notification Group" max-width="600px">
            <x-form.input name="notification_group_name" label="Name" placeholder="Scaffold handover notifications"/>
            <x-form.input name="notification_group_slug" label="Slug" help="Stable code name used by an operation, for example site.scaffold.handover.created." placeholder="site.scaffold.handover.created"/>
            <x-form.input name="notification_group_title" label="Helper title (optional)" help="Heading shown in the ? popover beside the group name." placeholder="Scaffold handover notifications"/>
            <x-form.textarea name="notification_group_body" label="Helper body (optional)" rows="3" placeholder="Explain which emails use this group."/>
            <x-form.input name="notification_group_brief" label="Text below recipient field (optional)" placeholder="Users selected here receive scaffold handover emails."/>
            <div class="note note-info" style="margin-bottom:0">
                After adding the group, select its SafeWorkSite users and click <strong>Save system notifications</strong>.
            </div>
            <x-slot name="footer">
                <button type="button" class="sws-modal-btn sws-modal-btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="sws-modal-btn sws-modal-btn-primary">Add Notification Group</button>
            </x-slot>
        </x-ui.bootstrap-modal>
    </form>
@stop

@section('page-level-plugins-head')
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-styles-head')
    <link href="/assets/pages/css/profile-2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <style>
        .settings-notifications-page .nav-tabs > li > a:focus { outline:0 !important; box-shadow:none !important; }
        .settings-notifications-page .nav-tabs > li.active > a,
        .settings-notifications-page .nav-tabs > li.active > a:hover,
        .settings-notifications-page .nav-tabs > li.active > a:focus { border-color:#ddd #ddd transparent !important; outline:0 !important; box-shadow:none !important; }
    </style>
@stop

@section('page-level-plugins')
    <script src="/js/libs/fileinput.min.js"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

        $(document).ready(function () {
            $('.select2').select2({placeholder: 'Select one or more users', width: '100%'});

            @if ($errors->has('report_name') || $errors->has('report_slug') || $errors->has('report_title') || $errors->has('report_body') || $errors->has('report_brief'))
            $('#add-report-notification').modal('show');
            @endif

            @if ($errors->has('notification_group_name') || $errors->has('notification_group_slug') || $errors->has('notification_group_title') || $errors->has('notification_group_body') || $errors->has('notification_group_brief'))
            $('#add-notification-group').modal('show');
            @endif

            $('.report-move').click(function (event) {
                event.preventDefault();
                $.ajax({url: '/settings/notifications/report/' + $(this).data('id') + '/move/' + $(this).data('direction'), type: 'PATCH', dataType: 'json', data: {submit: true}})
                    .always(function () {
                        location.reload();
                    });
            });

            $('.report-delete').click(function (event) {
                event.preventDefault();
                var id = $(this).data('id');
                var name = $(this).data('name');
                swal({
                    title: 'Are you sure?',
                    text: 'The report email list <b>' + name + '</b> will be deleted, including its selected recipients.<br><br><span class="font-red"><i class="fa fa-warning"></i> You will not be able to undo this action!</span>',
                    showCancelButton: true, cancelButtonColor: '#555555', confirmButtonColor: '#E7505A', confirmButtonText: 'Yes, delete it!', allowOutsideClick: true, html: true
                }, function () {
                    $.ajax({
                        url: '/settings/notifications/report/' + id, type: 'DELETE', dataType: 'json', data: {submit: true},
                        success: function () {
                            toastr.error('Deleted report email list');
                        },
                        error: function (xhr) {
                            toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to delete report email list');
                        }
                    }).always(function () {
                        location.reload();
                    });
                });
            });

            $('.notification-group-move').click(function (event) {
                event.preventDefault();
                $.ajax({url: '/settings/notifications/group/' + $(this).data('id') + '/move/' + $(this).data('direction'), type: 'PATCH', dataType: 'json', data: {submit: true}})
                    .always(function () {
                        location.reload();
                    });
            });

            $('.notification-group-delete').click(function (event) {
                event.preventDefault();
                var id = $(this).data('id');
                var name = $(this).data('name');
                swal({
                    title: 'Are you sure?',
                    text: 'The notification group <b>' + name + '</b> will be deleted, including its selected users.<br><br><span class="font-red"><i class="fa fa-warning"></i> Remove it from any operation recipient rules first.</span>',
                    showCancelButton: true, cancelButtonColor: '#555555', confirmButtonColor: '#E7505A', confirmButtonText: 'Yes, delete it!', allowOutsideClick: true, html: true
                }, function () {
                    $.ajax({
                        url: '/settings/notifications/group/' + id, type: 'DELETE', dataType: 'json', data: {submit: true},
                        success: function () {
                            toastr.error('Deleted notification group');
                        },
                        error: function (xhr) {
                            toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to delete notification group');
                        }
                    }).always(function () {
                        location.reload();
                    });
                });
            });
        });
    </script>
@stop
