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
    <!-- BEGIN PAGE CONTENT INNER -->
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
                        {!! Form::model('settings_notification', ['method' => 'PATCH', 'action' => ['Misc\SettingsNotificationController@update', Auth::user()->company->id]]) !!}

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
                            <h3 class="font-green form-section">Report Email Lists</h3>
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.jobstartexport')->first()->notificationSelect() !!}
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.upcoming.compliance')->first()->notificationSelect() !!}
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.extension')->first()->notificationSelect() !!}
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.maintenance.noaction')->first()->notificationSelect() !!}
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.maintenance.onhold')->first()->notificationSelect() !!}
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.maintenance.appointment')->first()->notificationSelect() !!}
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.maintenance.aftercare')->first()->notificationSelect() !!}
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.maintenance.underreview')->first()->notificationSelect() !!}
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.maintenance.super.noaction')->first()->notificationSelect() !!}
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.maintenance.executive')->first()->notificationSelect() !!}
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.asbestos.active')->first()->notificationSelect() !!}
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.qa.outstanding')->first()->notificationSelect() !!}
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.inspection.open')->first()->notificationSelect() !!}
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'site.attenance.super')->first()->notificationSelect() !!}
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'equipment.transfers')->first()->notificationSelect() !!}
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'equipment.restock')->first()->notificationSelect() !!}
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'company.missing.info')->first()->notificationSelect() !!}
                            {!! App\Models\Misc\SettingsNotificationCategory::where('slug', 'user.oldusers')->first()->notificationSelect() !!}
                        @endif

                        <div class="form-actions right">
                            <a href="/settings" class="btn default"> Back</a>
                            <button type="submit" class="btn green">Save</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END PAGE CONTENT INNER -->
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
        $(document).ready(function () {
            /* Select2 */
            $(".select2").select2({
                placeholder: "Select one or more users",
                width: '100%',
            });
        });

    </script>
@stop