@inject('notificationTypes', 'App\Http\Utilities\SettingsNotificationTypes')
@inject('companyDocTypes', 'App\Http\Utilities\CompanyDocTypes')
@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/settings">Settings</a><i class="fa fa-circle"></i></li>
        <li><span>Notifications</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-cog "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Notifications</span>
                            <span class="caption-helper"> ID: {{ Auth::user()->company->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([\App\Http\Controllers\Misc\SettingsNotificationController::class, 'update'], Auth::user()->company->id) }}">
                            @csrf
                            @method('PATCH')

                            {{-- Company --}}
                            @if (Auth::user()->company->subscription > 1)
                                <h3 class="font-green form-section">Company Notifications</h3>
                                {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'company.signup.sent')->first()->notificationSelect() !!}
                                {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'company.signup.completed')->first()->notificationSelect() !!}
                                {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'company.updated.details')->first()->notificationSelect() !!}
                                {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'company.updated.business')->first()->notificationSelect() !!}
                                {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'company.updated.creditorcode')->first()->notificationSelect() !!}
                                {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'company.updated.trades')->first()->notificationSelect() !!}
                            @endif
                            {{-- Site --}}
                            <h3 class="font-green form-section">Site Notifications</h3>
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.status')->first()->notificationSelect() !!}
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.accident')->first()->notificationSelect() !!}
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.hazard')->first()->notificationSelect() !!}
                            @if (Auth::user()->isCC())
                                {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.asbestos')->first()->notificationSelect() !!}
                                {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.qa.handover')->first()->notificationSelect() !!}
                                {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.qa.super.photo')->first()->notificationSelect() !!}
                                {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'prac.completion.completed')->first()->notificationSelect() !!}
                                {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.maintenance.completed')->first()->notificationSelect() !!}
                                {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.inspection.completed')->first()->notificationSelect() !!}
                                {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.inspection.onhold')->first()->notificationSelect() !!}
                                {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.jobstart')->first()->notificationSelect() !!}
                                {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.planner.key.tasks')->first()->notificationSelect() !!}
                            @endif

                            {{-- Document --}}
                            <h3 class="font-green form-section">Document Notifications</h3>
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'doc.acc.approval')->first()->notificationSelect() !!}
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'doc.whs.approval')->first()->notificationSelect() !!}
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'swms.approval')->first()->notificationSelect() !!}
                            @if (Auth::user()->isCC())
                                {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'doc.cc.approval')->first()->notificationSelect() !!}
                                {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'doc.standard.renew')->first()->notificationSelect() !!}

                            @endif

                            {{-- Miscellaneous --}}
                            @if (Auth::user()->isCC())
                                <h3 class="font-green form-section">Miscellaneous Notifications</h3>
                                {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'user.archived.notifications')->first()->notificationSelect() !!}
                            @endif

                            @if (Auth::user()->isCC())
                                {{-- Email Lists --}}
                                <h3 class="font-green form-section clearfix">
                                    Report Email Lists
                                    @if (Auth::user()->hasRole2('web-admin'))
                                        <button type="button" class="btn btn-circle green btn-outline btn-sm pull-right" data-toggle="modal" data-target="#add-report-notification">
                                            <i class="fa fa-plus"></i> Add Report Email List
                                        </button>
                                    @endif
                                </h3>
                                @if (Auth::user()->hasRole2('web-admin'))
                                    <p class="help-block" style="margin-top:-5px">
                                        <i class="fa fa-lock font-grey-silver"></i> Locked lists are referenced by SafeWorkSite code and can be disabled, but not deleted.
                                    </p>
                                @endif

                                @forelse ($reportCategories as $category)
                                    {!! $category->notificationSelect(true, !$loop->first, !$loop->last) !!}
                                @empty
                                    <div class="note note-info">No report email lists have been configured.</div>
                                @endforelse
                            @endif

                            <div class="form-actions right">
                                <a href="/settings" class="btn default"> Back</a>
                                <button type="submit" class="btn green">Save</button>
                            </div>
                        </form>
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

@stop

@section('page-level-plugins-head')
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" tytype="text/css"/>
@stop

@section('page-level-styles-head')
    <link href="/assets/pages/css/profile-2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
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
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
        });

        $(document).ready(function () {
            /* Select2 */
            $(".select2").select2({
                placeholder: "Select one or more users",
                width: '100%',
            });

            @if ($errors->has('report_name') || $errors->has('report_slug') || $errors->has('report_title') || $errors->has('report_body') || $errors->has('report_brief'))
            $('#add-report-notification').modal('show');
            @endif

            $('.report-move').click(function (e) {
                e.preventDefault();

                $.ajax({
                    url: '/settings/notifications/report/' + $(this).data('id') + '/move/' + $(this).data('direction'),
                    type: 'PATCH',
                    dataType: 'json',
                    data: {submit: true},
                }).always(function () {
                    location.reload();
                });
            });

            $('.report-delete').click(function (e) {
                e.preventDefault();

                var id = $(this).data('id');
                var name = $(this).data('name');

                swal({
                    title: "Are you sure?",
                    text: "The report email list <b>" + name + "</b> will be deleted, including its selected recipients.<br><br><span class='font-red'><i class='fa fa-warning'></i> You will not be able to undo this action!</span>",
                    showCancelButton: true,
                    cancelButtonColor: "#555555",
                    confirmButtonColor: "#E7505A",
                    confirmButtonText: "Yes, delete it!",
                    allowOutsideClick: true,
                    html: true,
                }, function () {
                    $.ajax({
                        url: '/settings/notifications/report/' + id,
                        type: 'DELETE',
                        dataType: 'json',
                        data: {submit: true},
                        success: function () {
                            toastr.error('Deleted report email list');
                        },
                        error: function (xhr) {
                            var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to delete report email list';
                            toastr.error(message);
                        },
                    }).always(function () {
                        location.reload();
                    });
                });
            });


        });

    </script>
@stop