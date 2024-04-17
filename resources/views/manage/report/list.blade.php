@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Management Reports</span></li>
    </ul>
@stop

@section('content')

    <!-- BEGIN PAGE CONTENT INNER -->
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Report List</span>
                        </div>
                        <div class="actions">
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th> Miscellaneous</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><a href="/manage/report/recent">Recent Reports</a></td>
                            </tr>
                            {{-- User --}}
                            <tr style="background-color: #f0f6fa">
                                <th> User</th>
                            </tr>
                            <tr>
                                <td><a href="/manage/report/newusers">New Users</a></td>
                            </tr>
                            <tr>
                                <td><a href="/manage/report/users_noemail">Users without emails</a></td>
                            </tr>
                            <tr>
                                <td><a href="/manage/report/users_nowhitecard">Users without white card</a></td>
                            <tr>
                                <td><a href="/manage/report/users_lastlogin">Users Last Login</a></td>
                            </tr>
                            <tr>
                                <td><a href="/manage/report/users_contactinfo">Users Contact Info</a></td>
                            </tr>
                            {{-- Company --}}
                            <tr style="background-color: #f0f6fa">
                                <th> Company</th>
                            </tr>
                            <tr>
                                <td><a href="/manage/report/newcompanies">New Companies</a></td>
                            </tr>
                            <tr>
                                <td><a href="/manage/report/company_contactinfo">Company Contact Info</a></td>
                            </tr>
                            <tr>
                                <td><a href="/manage/report/company_swms">Company SWMS</a></td>
                            </tr>
                            <tr>
                                <td><a href="/manage/report/company_privacy">Company Privacy Policy</a></td>
                            </tr>
                            <tr>
                                <td><a href="/manage/report/company_users">Company Staff</a></td>
                            </tr>
                            <tr>
                                <td><a href="/manage/report/missing_company_info">Companies with missing information or expired documents</a></td>
                            </tr>
                            <tr>
                                <td><a href="/manage/report/missing_company_info_planner">Planned Companies with missing information or expired documents 7 days+</a></td>
                            </tr>
                            <tr>
                                <td><a href="/manage/report/expired_company_docs">Expired Company Documents</a></td>
                            </tr>
                            <tr>
                                <td><a href="/manage/report/company_planned_tasks">Company Planned Tasks</a></td>
                            </tr>
                            {{-- Site --}}
                            <tr style="background-color: #f0f6fa">
                                <th> Site</th>
                            </tr>
                            <tr>
                                <td><a href="/manage/report/attendance">Attendance</a></td>
                            </tr>
                            @if (Auth::user()->hasAnyPermissionType('site.upcoming.compliance'))
                                <tr>
                                    <td><a href="/site/upcoming/compliance">Upcoming Jobs Compliance Data</a></td>
                                </tr>
                            @endif
                            @if (Auth::user()->hasAnyPermissionType('site.extension'))
                                <tr>
                                    <td><a href="/site/extension">Contract Time Extensions</a></td>
                                </tr>
                            @endif
                            @if (Auth::user()->isCC())
                                {{-- Quality Assurance --}}
                                <tr style="background-color: #f0f6fa">
                                    <th> Quality Assurance</th>
                                </tr>
                                <tr>
                                    <td><a href="/manage/report/qa_onhold">On Hold QA</a></td>
                                </tr>
                                <tr>
                                    <td><a href="/manage/report/qa_outstanding">Outstanding QA</a></td>
                                </tr>
                                {{-- Maintenance --}}
                                <tr style="background-color: #f0f6fa">
                                    <th> Maintenance Requests</th>
                                </tr>
                                <tr>
                                    <td><a href="/manage/report/maintenance_no_action">No Action 14 days</a></td>
                                </tr>
                                <tr>
                                    <td><a href="/manage/report/maintenance_on_hold">On Hold</a></td>
                                </tr>
                                <tr>
                                    <td><a href="/manage/report/maintenance_appointment">Without Client Appointment</a></td>
                                </tr>
                                <tr>
                                    <td><a href="/manage/report/maintenance_aftercare">Without After Care</a></td>
                                </tr>
                                <tr>
                                    <td><a href="/manage/report/maintenance_supervisor_no_action">Supervisor No Appointment / Action 14 days</a></td>
                                </tr>
                                <tr>
                                    <td><a href="/manage/report/maintenance_assigned_company">Assigned Companies</a></td>
                                </tr>
                                <tr>
                                    <td><a href="/manage/report/maintenance_executive">Executive Summary</a></td>
                                </tr>

                                {{-- Inspection Reports --}}
                                <tr style="background-color: #f0f6fa">
                                    <th> Inspection Reports</th>
                                </tr>
                                <tr>
                                    <td><a href="/manage/report/inspection_electrical_plumbing">Open Electrical / Plumbing Inspections</a></td>
                                </tr>
                                {{-- Equipment --}}
                                <tr style="background-color: #f0f6fa">
                                    <th> Equipment</th>
                                </tr>
                                <tr>
                                    <td><a href="/manage/report/equipment">Equipment List</a></td>
                                </tr>
                                <tr>
                                    <td><a href="/manage/report/equipment_site">Equipment List by Site </a></td>
                                </tr>
                                <tr>
                                    <td><a href="/manage/report/equipment_transactions">Equipment Purchases</a></td>
                                </tr>
                                <tr>
                                    <td><a href="/manage/report/equipment_transfers">Equipment Transfers</a></td>
                                </tr>
                                <tr>
                                    <td><a href="/manage/report/equipment_stocktake">Equipment Stocktake</a></td>
                                </tr>
                                <tr>
                                    <td><a href="/manage/report/equipment_restock">Equipment Restock Items</a></td>
                                </tr>
                                {{-- Accounting --}}
                                <tr style="background-color: #f0f6fa">
                                    <th> Accounting</th>
                                </tr>
                                <tr>
                                    <td><a href="/manage/report/payroll">Payroll</a></td>
                                </tr>
                                @if (Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
                                    {{-- Tasks --}}
                                    <tr style="background-color: #f0f6fa">
                                        <th> Tasks</th>
                                    </tr>
                                    <tr>
                                        <td><a href="/manage/report/todo">Active Todo Tasks</a></td>
                                    </tr>
                                @endif
                            @endif
                            {{-- Security --}}
                            @if (Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
                                <tr style="background-color: #f0f6fa">
                                    <th> Security</th>
                                </tr>
                                <tr>
                                    <td><a href="/manage/report/roleusers">Roles assigned to Users</a></td>
                                </tr>
                                <tr>
                                    <td><a href="/manage/report/users_extra_permissions">Users with extra permissions (on top of what is provided by their role)</a></td>
                                </tr>
                                <tr>
                                    <td><a href="/manage/report/users_with_permission/user">Users with Specific Permission</a></td>
                                </tr>
                            @endif
                            @if (Auth::user()->hasRole2('web-admin'))
                                {{-- Web Admin --}}
                                <tr style="background-color: #f0f6fa">
                                    <th> Website Admin</th>
                                </tr>
                                <tr>
                                    <td><a href="/manage/report/nightly">Nightly Log</a></td>
                                </tr>
                                <tr>
                                    <td><a href="/manage/report/zoho">Zoho Import Log</a></td>
                                </tr>
                                <tr>
                                    <td><a href="/manage/report/cronjobs">Manual Cron Jobs</a></td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END PAGE CONTENT INNER -->
@stop


@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
@stop