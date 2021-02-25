@inject('notificationTypes', 'App\Http\Utilities\SettingsNotificationTypes')
@inject('companyDocTypes', 'App\Http\Utilities\CompanyDocTypes')
@extends('layout')

@section('pagetitle')
    <div class="page-title">
        <h1><i class="fa fa-cog"></i> Settings</h1>
    </div>
@stop

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
                        <div class="actions">
                            <a href="javascript:;" class="btn btn-circle btn-icon-only btn-default fullscreen"> </a>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        {!! Form::model('settings_notification', ['method' => 'PATCH', 'action' => ['Misc\SettingsNotificationController@update', Auth::user()->company->id]]) !!}

                        {{-- Company --}}
                        @if (Auth::user()->company->subscription > 1)
                            <h3 class="font-green form-section">Company Notifications</h3>
                            {!! notificationSelect($notificationTypes::type('n.company.signup.sent'), 'Signup Sent', 'Company Signup', 'Company signup request sent') !!}
                            {!! notificationSelect($notificationTypes::type('n.company.signup.completed'), 'Signup Completed / Archived', 'Company Signup/Archived', 'Company has completed the signup process or made inactive/active') !!}
                            {!! notificationSelect($notificationTypes::type('n.company.updated.details'), 'Company Details updated', 'Company updated', 'Company details have been updated') !!}
                            {!! notificationSelect($notificationTypes::type('n.company.updated.business'), 'Business Details updated', 'Company updated', 'Business details have been updated') !!}
                            {!! notificationSelect($notificationTypes::type('n.company.updated.trades'), 'Company Trades updated (WHS)', 'Company updated', 'Company trades have been updated but also licence required was previously overridden') !!}
                        @endif
                        {{-- Site --}}
                        <h3 class="font-green form-section">Site Notifications</h3>
                        {!! notificationSelect($notificationTypes::type('n.site.status'), 'Site Created/Status change', 'Site Status', 'created, status change', 'Automatically includes relevant Site/Area Supervisors') !!}
                        {!! notificationSelect($notificationTypes::type('n.site.accident'), 'Accident Reports', 'Site Accident', 'lodgement, updated', 'Automatically includes relevant Site/Area Supervisors') !!}
                        {!! notificationSelect($notificationTypes::type('n.site.hazard'), 'Hazard Reports', 'Site Hazard', 'lodgement, updated', 'Automatically includes: Site/Area Supervisors') !!}
                        @if (Auth::user()->isCC())
                            {!! notificationSelect($notificationTypes::type('n.site.asbestos'), 'Asbestos Notification', 'Site Asbestos', 'lodgement, updated', 'Automatically includes relevant Site/Area Supervisors') !!}
                            {!! notificationSelect($notificationTypes::type('n.site.qa.handover'), 'QA Handover Completion', 'Site Quality Assurance', 'Handover Completion') !!}
                            {!! notificationSelect($notificationTypes::type('n.site.qa.super.photo'), 'QA Supervisor Photo Checklist', 'Site Quality Assurance', 'QA Supervisor Photo Checklist Completion') !!}
                            {!! notificationSelect($notificationTypes::type('n.site.maintenance.completed'), 'Maintenance Request Completed', 'Maintenance Request', 'Maintenance completed') !!}
                            {!! notificationSelect($notificationTypes::type('n.site.inspection.completed'), 'Inspection Report Completed', 'Inspection Report', 'Inspection completed') !!}
                            {!! notificationSelect($notificationTypes::type('n.site.jobstart'), 'Job Start', 'Job Start', 'Job Start created / updated') !!}
                        @endif

                        {{-- Document --}}
                        <h3 class="font-green form-section">Document Notifications</h3>
                        {!! notificationSelect($notificationTypes::type('n.doc.acc.approval'), 'Accounts Approval', 'Accounts document requires approval', $companyDocTypes::docNames('acc', 0), 'Companies are automatically notified 2 weeks prior document expiry + every week after expiry for 1 month<br>Users above are notified 2 weeks prior document expiry + 2 weeks after.') !!}
                        {!! notificationSelect($notificationTypes::type('n.doc.whs.approval'), 'WHS Approval', 'WHS document requires approval', $companyDocTypes::docNames('whs', 0), 'Companies are automatically notified 2 weeks prior document expiry + every week after expiry for 1 month<br>Users above are notified 2 weeks prior document expiry + 2 weeks after.') !!}
                        {!! notificationSelect($notificationTypes::type('n.swms.approval'), 'SWMS Approval', 'SWMS requires approval', 'SWMS documents', 'Companies are automatically notified 2 weeks prior document expiry + every week after expiry for 1 month<br>Users above are notified 2 weeks prior document expiry + 2 weeks after.') !!}

                        {{-- Miscellaneous --}}
                        @if (Auth::user()->isCC())
                            <h3 class="font-green form-section">Miscellaneous Notifications</h3>
                            {!! notificationSelect($notificationTypes::type('n.user.archived.notifications'), 'User Achived Notifys', 'User archived', 'User archived with active Notifications') !!}
                        @endif

                        @if (Auth::user()->isCC())
                            {{-- Email Lists --}}
                            <h3 class="font-green form-section">Report Email Lists</h3>
                            {!! notificationSelect($notificationTypes::type('n.site.jobstartexport'), 'Job Start Export', 'Job Start Export', 'Default list to email Job Start Export ') !!}
                            {!! notificationSelect($notificationTypes::type('n.site.maintenance.noaction'), 'Maintenance No Actions', 'Maintenance Request No Actions', 'No Actions for 14 days on Maintenance Request', 'Report sent fortnightly (Monday)') !!}
                            {!! notificationSelect($notificationTypes::type('n.site.maintenance.onhold'), 'Maintenance On Hold', 'Maintenance Request On Hold', 'Maintenance Requests currently On Hold', 'Report sent fortnightly (Monday)') !!}
                            {!! notificationSelect($notificationTypes::type('n.site.maintenance.executive'), 'Maintenance Executive', 'Maintenance Executive Report', 'Maintenance overview of all requests and how they were dealt with', 'Report sent quarterly (end Feb/May/Aug/Nov)') !!}
                            {!! notificationSelect($notificationTypes::type('n.site.qa.outstanding'), 'QA Outstanding', 'Quality Assurance Outstanding', 'Report to show all outstanding QA Checklists at the time of the report', 'Report sent weekly (Tuesday)') !!}
                            {!! notificationSelect($notificationTypes::type('n.site.qa.onhold'), 'QA On Hold', 'Quality Assurance On Hold', 'Report to show all QA Checklists on Hold at the time of the report', 'Report sent fortnightly (Thursday)') !!}
                            {!! notificationSelect($notificationTypes::type('n.equipment.transfers'), 'Equipment Transfers', 'Equipment Transfers', 'Equipment transfers for the last 7 days', 'Report sent weekly (Thursday)') !!}
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

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
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