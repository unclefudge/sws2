<?php
$class = ['class' => 'form-control bs-select'];
$classDisable = ['class' => 'form-control bs-select', 'disabled'];
$sub1 = ($user->company->subscription > 0) ? 1 : 0;
$sub2 = ($user->company->subscription > 1) ? 1 : 0;
$plan = ($user->company->addon('planner')) ? 1 : 0;
$cc = ($user->isCC()) ? 1 : 0;
$be = ($user->company->id == '210') ? 1 : 0; // Blue Eco
$cid = $user->cid;
$dis = Auth::user()->hasPermission2('edit.user.security') ? false : true;
$rec = $user;
?>

@if (App\Models\Misc\Role2::where('company_id', $user->cid)->first())
    <div class="portlet light">
        <div class="portlet-title">
            <div class="caption">
                <span class="caption-subject font-dark bold uppercase">Assigned Roles</span>
            </div>
        </div>
        <div class="portlet-body form">
            <div class="row">
                <div class="col-md-12">
                    @if(Auth::user()->hasPermission2('edit.user.security') && Auth::user()->isCompany($cid))
                        <div class="form-group {!! fieldHasError('roles', $errors) !!}">
                            {!! Form::select('roles', $user->company->rolesSelect('int'), $user->roles2->pluck('id')->toArray(),
                            ['class' => 'form-control select2-multiple', 'name' => 'roles[]', 'title' => 'Select one or more roles', 'multiple', 'id' => 'roles' ]) !!}
                            {!! fieldErrorMessage('roles', $errors) !!}
                        </div>
                    @else
                        {!! Form::label('roles', 'Assigned Role(s)', ['class' => 'control-label']) !!}
                        {!! Form::text('roles_txt', $user->parentRolesSBC(), ['class' => 'form-control', 'disabled']) !!}
                        <br>
                    @endif
                </div>
            </div>
            @if (Auth::user()->isCompany($user->company->parent_company))
                <div class="form-actions right">
                    <button type="submit" class="btn green">Save</button>
                </div>
            @endif
        </div>
    </div>
@endif

<div class="row" style="margin:0px; background-color: #48525e; color: #FFFFFF;">
    <div class="col-md-12">
        <h3>SECURITY PERMISSIONS</h3>
    </div>
</div>
{{-- Extra Permissions --}}
@if (Auth::user()->isCompany($cid) && $user->extraUserPermissionsText($cid) && $user->company->subscription && $user->hasRoleCompany($cid))
    <div class="row">
        <div class="col-md-12">
            <div class="note note-warning">
                {!! $user->extraUserPermissionsText($cid) !!}
                <a href="/user/{{ $user->id }}/resetpermissions" class="btn dark">Remove additional permissions</a>
            </div>
        </div>
    </div>
@endif

{{-- Users / Companies Management --}}
<div class="portlet light">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase"> Users / Company Management</span>
        </div>
    </div>
    <div class="portlet-body form">
        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped">
                    <tr>
                        <td style="background: #FFF; border: 0px #e7ecf1; font-size: 18px; font-weight: 300; padding: 0;"></td>
                        <td width="15%" style="border: 1px solid; border-color:#e7ecf1">View</td>
                        <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Edit</td>
                        <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Create</td>
                        <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Delete
                            <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                               data-content="For record integrity most data can't be deleted but users may be given access to archive / resolve it instead. For data that is actually deleted you will be asked to 'confirm'"
                               data-original-title="Delete"> <i class="fa fa-question-circle font-grey-silver"></i> </a></td>
                        <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Sign Off
                            <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                               data-content="Certain actions or updates to the record are required to be 'Signed Off' by an authorised user."
                               data-original-title="Sign Off"> <i class="fa fa-question-circle font-grey-silver"></i> </a></td>
                    </tr>
                </table>

                {{-- Users --}}
                <h5 class="font-green-haze" style="font-size: 16px">User
                    <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                       data-content="Grants ability to view or modify users that belong to your company or any 'child' company."
                       data-original-title="User"> <i class="fa fa-question-circle font-grey-silver"></i> </a></h5>
                <table class="table table-bordered table-striped">
                    <tr>
                        <td>Login Details</td>
                        <td width="15%">{!! permSelect('view.user', 'all', $rec, $cid, $dis) !!}</td>
                        <td width="15%">{!! permSelect('edit.user', 'all', $rec, $cid, $dis) !!}</td>
                        <td width="15%">{!! permSelect('add.user', 'add', $rec, $cid, $dis) !!}</td>
                        <td width="15%">{!! permSelect('del.user', 'arc', $rec, $cid, $dis) !!}</td>
                        <td width="15%"></td>
                    </tr>
                    <tr>
                        <td>Contact Details</td>
                        <td width="15%">{!! permSelect('view.user.contact', 'all', $rec, $cid, $dis) !!}</td>
                        <td width="15%">{!! permSelect('edit.user.contact', 'all', $rec, $cid, $dis) !!}</td>
                        @if (false && Auth::user()->company_id == $user->company_id)
                            <td width="30%" colspan="2"></td>
                            <td width="15%">{!! permSelect('sig.user.contact', 'sig', $rec, $cid, $dis) !!}</td>
                        @else
                            <td width="45%" colspan="3"></td>
                        @endif
                    </tr>
                    <tr>
                        <td>Security Details</td>
                        <td width="15%">{!! permSelect('view.user.security', 'all', $rec, $cid, $dis) !!}</td>
                        <td width="15%">{!! permSelect('edit.user.security', 'all', $rec, $cid, $dis) !!}</td>
                        <td width="45%" colspan="3"></td>
                    </tr>
                    <tr>
                        <td>Construction</td>
                        <td width="15%">{!! permSelect('view.user.construction', 'all', $rec, $cid, $dis) !!}</td>
                        <td width="15%">{!! permSelect('edit.user.construction', 'all', $rec, $cid, $dis) !!}</td>
                        <td width="45%" colspan="3"></td>
                    </tr>
                </table>

                {{-- Companies --}}
                <h5 class="font-green-haze" style="font-size: 16px">Companies
                    <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                       data-content="Grants ability to view or modify your company information or any 'child' company."
                       data-original-title="Company"> <i class="fa fa-question-circle font-grey-silver"></i> </a></h5>
                <table class="table table-bordered table-striped">
                    <tr>
                        <td>Company Record</td>
                        <td width="30%" colspan="2"></td>
                        <td width="15%">{!! permSelect('add.company', 'add', $rec, $cid, $dis) !!}</td>
                        <td width="15%">{!! permSelect('del.company', 'arc', $rec, $cid, $dis) !!}</td>
                        <td width="15%">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>Company Details</td>
                        <td width="15%">{!! permSelect('view.company', ($sub1) ? 'own' : 'all', $rec, $cid, $dis) !!}</td>
                        <td width="15%">{!! permSelect('edit.company', ($sub1) ? 'own' : 'all', $rec, $cid, $dis) !!}</td>
                        @if (Auth::user()->company_id == $user->company_id)
                            <td width="30%" colspan="2"></td>
                            <td width="15%">{!! permSelect('sig.company', 'sig', $rec, $cid, $dis) !!}</td>
                        @else
                            <td width="45%" colspan="3"></td>
                        @endif

                    </tr>
                    <tr>
                        <td>Business Details</td>
                        <td width="15%">{!! permSelect('view.company.acc', ($sub1) ? 'own' : 'all', $rec, $cid, $dis) !!}</td>
                        <td width="15%">{!! permSelect('edit.company.acc', ($sub1) ? 'own' : 'all', $rec, $cid, $dis) !!}</td>
                        @if (Auth::user()->company_id == $user->company_id)
                            <td width="30%" colspan="2"></td>
                            <td width="15%">{!! permSelect('sig.company.acc', 'sig', $rec, $cid, $dis) !!}</td>
                        @else
                            <td width="45%" colspan="3"></td>
                        @endif
                    </tr>

                    @if (Auth::user()->company_id == $user->company_id)
                        <tr>
                            <td>Construction</td>
                            <td width="15%">{!! permSelect('view.company.con', ($sub1) ? 'own' : 'all', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('edit.company.con', ($sub1) ? 'own' : 'all', $rec, $cid, $dis) !!}</td>
                            <td width="30%" colspan="2"></td>
                            <td width="15%">{!! permSelect('sig.company.con', 'sig', $rec, $cid, $dis) !!}</td>
                        </tr>
                        <tr>
                            <td>Company Leave</td>
                            <td width="15%">{!! permSelect('view.company.leave', ($sub1) ? 'own' : 'all', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('edit.company.leave', ($sub1) ? 'own' : 'all', $rec, $cid, $dis) !!}</td>
                            <td width="45%" colspan="3"></td>
                        </tr>
                        @if ($cc || $be)
                            <tr>
                                <td>Compliance Management<a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                                            data-content="Grants ability to override the default compliance requirements in regards to documents for individual companies. To be used sparingly"
                                                            data-original-title="Compliance Management"> <i class="fa fa-question-circle font-grey-silver"></i> </a></td>
                                <td width="15%">{!! permSelect('view.compliance.manage', ($sub1) ? 'own' : 'all', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.compliance.manage', ($sub1) ? 'own' : 'all', $rec, $cid, $dis) !!}</td>
                                <td width="45%" colspan="3"></td>
                            </tr>
                        @endif
                    @endif
                </table>
            </div>
        </div>
        @if (Auth::user()->isCompany($user->company))
            <div class="form-actions right">
                <button type="submit" class="btn green">Save</button>
            </div>
        @endif
    </div>
</div>

@if ($sub1 && Auth::user()->company_id == $user->company_id)
    {{-- Construction Management --}}
    <div class="portlet light">
        <div class="portlet-title">
            <div class="caption">
                <span class="caption-subject font-dark bold uppercase"> Construction Management</span>
            </div>
        </div>
        <div class="portlet-body form">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-striped">
                        <tr>
                            <td style="background: #FFF; border: 0px #e7ecf1; font-size: 18px; font-weight: 300; padding: 0;"></td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">View</td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Edit</td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Create</td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Delete
                                <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                   data-content="For record integrity most data can't be deleted but users may be given access to archive / resolve it instead. For data that is actually deleted you will be asked to 'confirm'"
                                   data-original-title="Delete"> <i class="fa fa-question-circle font-grey-silver"></i> </a></td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Sign Off
                                <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                   data-content="Certain actions or updates to the record are required to be 'Signed Off' by an authorised user."
                                   data-original-title="Sign Off"> <i class="fa fa-question-circle font-grey-silver"></i> </a></td>
                        </tr>
                    </table>

                    <h5 class="font-green-haze" style="font-size: 16px">Work Site
                        <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                           data-content="Grants ability to view or modify work sites."
                           data-original-title="Work Sites"> <i class="fa fa-question-circle font-grey-silver"></i> </a></h5>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <td>Site / Client Details</td>
                            <td width="15%">{!! permSelect('view.site', ($plan) ? 'super.plan' : 'super', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('edit.site', ($plan) ? 'super.plan' : 'super', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('add.site', 'add', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('del.site', 'arc', $rec, $cid, $dis) !!}</td>
                            <td width="15%"></td>
                        </tr>
                        @if ($cc)
                            <tr>
                                <td>Site Admin Info<br><span class="font-grey-silver">Cape Cod Only</span></td>
                                <td width="15%">{!! permSelect('view.site.admin', 'all', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.site.admin', 'all', $rec, $cid, $dis) !!}</td>
                                <td width="45%" colspan="3"></td>
                            </tr>
                            <tr>
                                <td>Site Zoho Fields<br><span class="font-grey-silver">Cape Cod Only</span></td>
                                <td width="15%">&nbsp;</td>
                                <td width="15%">{!! permSelect('edit.site.zoho.fields', 'all', $rec, $cid, $dis) !!}</td>
                                <td width="45%" colspan="3"></td>
                            </tr>
                        @endif
                        <tr>
                            <td>Attendance</td>
                            <td width="15%">{!! permSelect('view.site.attendance', ($sub1) ? 'company.individual' : 'all', $rec, $cid, $dis) !!}</td>
                            <td width="60%" colspan="4"></td>
                        </tr>
                    </table>

                    <h5 class="font-green-haze" style="font-size: 16px">Trades / Supervisors
                        <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                           data-content="Grants ability to view or modify trades/tasks/superviors required on the sites."
                           data-original-title="Trades / Supervisors"> <i class="fa fa-question-circle font-grey-silver"></i> </a></h5>
                    <table class="table table-bordered table-striped">
                        @if ($plan)
                            <tr>
                                <td>Trades / Tasks</td>
                                <td width="15%">{!! permSelect('view.trade', 'all', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.trade', 'all', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('add.trade', 'add', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('del.trade', 'arc', $rec, $cid, $dis) !!}</td>
                                <td width="15%"></td>
                            </tr>
                        @endif
                        <tr>
                            <td>Supervisors</td>
                            <td width="15%">{!! permSelect('view.area.super', 'all', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('edit.area.super', 'all', $rec, $cid, $dis) !!}</td>
                            <td width="45%" colspan="3"></td>
                        </tr>
                    </table>
                </div>
            </div>
            @if (Auth::user()->isCompany($user->company))
                <div class="form-actions right">
                    <button type="submit" class="btn green">Save</button>
                </div>
            @endif
        </div>
    </div>

    {{-- Miscellaneous Management --}}
    <div class="portlet light">
        <div class="portlet-title">
            <div class="caption">
                <span class="caption-subject font-dark bold uppercase"> Miscellaneous Management</span>
            </div>
        </div>
        <div class="portlet-body form">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-striped">
                        <tr>
                            <td style="background: #FFF; border: 0px #e7ecf1; font-size: 18px; font-weight: 300; padding: 0;"></td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">View</td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Edit</td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Create</td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Delete
                                <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                   data-content="For record integrity most data can't be deleted but users may be given access to archive / resolve it instead. For data that is actually deleted you will be asked to 'confirm'"
                                   data-original-title="Delete"> <i class="fa fa-question-circle font-grey-silver"></i> </a></td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Sign Off
                                <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                   data-content="Certain actions or updates to the record are required to be 'Signed Off' by an authorised user."
                                   data-original-title="Sign Off"> <i class="fa fa-question-circle font-grey-silver"></i> </a></td>
                        </tr>
                    </table>

                    <h5 class="font-green-haze" style="font-size: 16px">Daily / Alerts
                        <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                           data-content="Grants ability to communicate via messages / alert to other users which belong to your company or child company."
                           data-original-title="Messages / Alerts"> <i class="fa fa-question-circle font-grey-silver"></i>
                        </a>
                    </h5>
                    <table class="table table-bordered table-striped">
                        @if($cc)
                            <tr>
                                <td>Compliance
                                    <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                       data-content="Grants ability to view or edit roster for users which belong to your company work sites."
                                       data-original-title="Attendance / Compliance"> <i class="fa fa-question-circle font-grey-silver"></i>
                                    </a>
                                    <br><span class="font-grey-silver">Cape Cod Only</span></td>
                                <td width="15%">{!! permSelect('view.compliance', ($sub1) ? 'super.company' : 'super', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.compliance', ($sub1) ? 'super.company' : 'super', $rec, $cid, $dis) !!}</td>
                                <td width="45%" colspan="3"></td>
                            </tr>
                            <tr>
                                <td>Safety Tips<br><span class="font-grey-silver">Cape Cod Only</span></td>
                                <td width="15%">{!! permSelect('view.safetytip', 'all', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.safetytip', 'all', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('add.safetytip', 'add', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('del.safetytip', 'del', $rec, $cid, $dis) !!}</td>
                                <td width="15%"></td>
                            </tr>
                        @endif
                        <tr>
                            <td>Alert Nofications</td>
                            <td width="15%">{!! permSelect('view.notify', 'all', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('edit.notify', 'all', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('add.notify', 'add', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('del.notify', 'del', $rec, $cid, $dis) !!}</td>
                            <td width="15%"></td>
                        </tr>
                    </table>

                    <h5 class="font-green-haze" style="font-size: 16px">Reports / Exports
                        <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                           data-content="Grants ability to view management reports and exports which belong to your company."
                           data-original-title="Management Reports / Exports"> <i class="fa fa-question-circle font-grey-silver"></i>
                        </a>
                    </h5>
                    <table class="table table-bordered table-striped">
                        @if ($cc)
                            <tr>
                                <td>Export Site Data</td>
                                <td width="15%">{!! permSelect('view.site.export', 'super', $rec, $cid, $dis) !!}</td>
                                <td width="60%" colspan="4"></td>
                            </tr>
                        @endif
                        <tr>
                            <td>Management Reports</td>
                            <td width="15%">{!! permSelect('view.manage.report', 'all', $rec, $cid, $dis) !!}</td>
                            <td width="60%" colspan="4"></td>
                        </tr>

                    </table>

                    <h5 class="font-green-haze" style="font-size: 16px">Configuration / Settings
                        <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                           data-content="Grants ability to view / modify configuration settings for this website"
                           data-original-title="Configuration / Settings"> <i class="fa fa-question-circle font-grey-silver"></i>
                        </a>
                    </h5>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <td>Settings</td>
                            <td width="15%">{!! permSelect('view.settings', 'all', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('edit.settings', 'all', $rec, $cid, $dis) !!}</td>
                            <td width="45%" colspan="3"></td>
                        </tr>
                        @if ($cc)
                            <tr>
                                <td>Support Ticket Upgrades</td>
                                <td width="15%">{!! permSelect('view.support.ticket.upgrade', 'all', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.support.ticket.upgrade', 'all', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('add.support.ticket.upgrade', 'add', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('del.support.ticket.upgrade', 'del', $rec, $cid, $dis) !!}</td>
                                <td width="15%"></td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
            @if (Auth::user()->isCompany($user->company))
                <div class="form-actions right">
                    <button type="submit" class="btn green">Save</button>
                </div>
            @endif
        </div>
    </div>

    {{-- Job Site Info --}}
    <div class="portlet light">
        <div class="portlet-title">
            <div class="caption">
                <span class="caption-subject font-dark bold uppercase"> Job Site Info</span>
            </div>
        </div>
        <div class="portlet-body form">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-striped">
                        <tr>
                            <td style="background: #FFF; border: 0px #e7ecf1; font-size: 18px; font-weight: 300; padding: 0;"></td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">View</td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Edit</td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Create</td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Delete
                                <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                   data-content="For record integrity most data can't be deleted but users may be given access to archive / resolve it instead. For data that is actually deleted you will be asked to 'confirm'"
                                   data-original-title="Delete"> <i class="fa fa-question-circle font-grey-silver"></i> </a></td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Sign Off
                                <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                   data-content="Certain actions or updates to the record are required to be 'Signed Off' by an authorised user."
                                   data-original-title="Sign Off"> <i class="fa fa-question-circle font-grey-silver"></i> </a></td>
                        </tr>
                    </table>

                    <h5 class="font-green-haze" style="font-size: 16px">Safety</h5>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <td>Hazard Reports</td>
                            @if ($plan)
                                <td width="15%">{!! permSelect('view.site.hazard', ($sub1) ? 'every' : 'super.individual', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.site.hazard', ($sub1) ? 'every' : 'super.individual', $rec, $cid, $dis) !!}</td>
                            @else
                                <td width="15%">{!! permSelect('view.site.hazard', ($sub1) ? 'every-plan' : 'super.individual', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.site.hazard', ($sub1) ? 'every-plan' : 'super.individual', $rec, $cid, $dis) !!}</td>
                            @endif
                            <td width="15%">{!! permSelect('add.site.hazard', 'add', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('del.site.hazard', 'res', $rec, $cid, $dis) !!}</td>
                            <td width="15%"></td>
                        </tr>
                        <tr>
                            <td>Accident Reports</td>
                            @if ($plan)
                                <td width="15%">{!! permSelect('view.site.accident', ($sub1) ? 'every' : 'super.individual', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.site.accident', ($sub1) ? 'every' : 'super.individual', $rec, $cid, $dis) !!}</td>
                            @else
                                <td width="15%">{!! permSelect('view.site.accident', ($sub1) ? 'every-plan' : 'super.individual', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.site.accident', ($sub1) ? 'every-plan' : 'super.individual', $rec, $cid, $dis) !!}</td>
                            @endif
                            <td width="15%">{!! permSelect('add.site.accident', 'add', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('del.site.accident', 'res', $rec, $cid, $dis) !!}</td>
                            <td width="15%"></td>
                        </tr>
                        <tr>
                            <td>Incident Reports</td>
                            @if ($plan)
                                <td width="15%">{!! permSelect('view.site.incident', ($sub1) ? 'every' : 'super.individual', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.site.incident', ($sub1) ? 'every' : 'super.individual', $rec, $cid, $dis) !!}</td>
                            @else
                                <td width="15%">{!! permSelect('view.site.incident', ($sub1) ? 'every-plan' : 'super.individual', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.site.incident', ($sub1) ? 'every-plan' : 'super.individual', $rec, $cid, $dis) !!}</td>
                            @endif
                            <td width="15%">{!! permSelect('add.site.incident', 'add', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('del.site.incident', 'res', $rec, $cid, $dis) !!}</td>
                            <td width="15%"></td>
                        </tr>
                        <tr>
                            <td>Risk Assessments / Hazardous Materials</td>
                            <td width="15%">{!! permSelect('view.safety.doc', ($plan) ? 'super.plan' : 'super', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('edit.safety.doc', ($plan) ? 'super.plan' : 'super', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('add.safety.doc', 'add', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('del.safety.doc', 'del', $rec, $cid, $dis) !!}</td>
                            <td width="15%"></td>
                        </tr>{{--}}
                        <tr>
                            <td>Asbestos Register</td>
                            <td width="15%">{!! permSelect('view.site.asbestos.register', 'super', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('edit.site.asbestos.register', 'super', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('add.site.asbestos.register', 'add', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('del.site.asbestos.register', 'res', $rec, $cid, $dis) !!}</td>
                            <td width="15%"></td>
                        </tr>--}}
                        <tr>
                            <td>Asbestos Notifications</td>
                            <td width="15%">{!! permSelect('view.site.asbestos', 'super', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('edit.site.asbestos', 'super', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('add.site.asbestos', 'add', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('del.site.asbestos', 'res', $rec, $cid, $dis) !!}</td>
                            <td width="15%"></td>
                        </tr>
                    </table>

                    <h5 class="font-green-haze" style="font-size: 16px">Documents</h5>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <td>General / Plans</td>
                            <td width="15%">{!! permSelect('view.site.doc', ($plan) ? 'super.plan' : 'super', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('edit.site.doc', ($plan) ? 'super.plan' : 'super', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('add.site.doc', 'add', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('del.site.doc', 'del', $rec, $cid, $dis) !!}</td>
                            <td width="15%"></td>
                        </tr>
                        @if($cc)
                            <tr>
                                <td>Upcoming Plans<br><span class="font-grey-silver">Cape Cod Only</span></td>
                                <td width="15%">{!! permSelect('view.site.doc.upcoming', 'all', $rec, $cid, $dis) !!}</td>
                                <td width="60%" colspan="4"></td>
                            </tr>
                        @endif
                        <tr>
                            <td>Site List</td>
                            <td width="15%">{!! permSelect('view.site.list', 'all', $rec, $cid, $dis) !!}</td>
                            <td width="60%" colspan="4"></td>
                        </tr>
                    </table>

                    @if($cc)
                        <h5 class="font-green-haze" style="font-size: 16px">Reports</h5>
                        <table class="table table-bordered table-striped">
                            <tr>
                                <td>Maintenance Requests<br><span class="font-grey-silver">Cape Cod Only</span></td>
                                <td width="15%">{!! permSelect('view.site.maintenance', 'super.plan', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.site.maintenance', 'super', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('add.site.maintenance', 'add', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('del.site.maintenance', 'res', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('sig.site.maintenance', 'sig', $rec, $cid, $dis) !!}</td>
                            </tr>
                            <tr>
                                <td>Quality Assurance Reports<br><span class="font-grey-silver">Cape Cod Only</span></td>
                                <td width="15%">{!! permSelect('view.site.qa', 'super', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.site.qa', 'super', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('add.site.qa', 'add', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('del.site.qa', 'res', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('sig.site.qa', 'sig', $rec, $cid, $dis) !!}</td>
                            </tr>
                            <tr>
                                <td>Quality Assurance Templates<br><span class="font-grey-silver">Cape Cod Only</span></td>
                                <td width="15%">{!! permSelect('view.site.qa.templates', 'all', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.site.qa.templates', 'all', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('add.site.qa.templates', 'add', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('del.site.qa.templates', 'arc', $rec, $cid, $dis) !!}</td>
                                <td width="15%"></td>
                            </tr>
                            <tr>
                                <td>Inspection Reports<br><span class="font-grey-silver">Cape Cod Only</span></td>
                                <td width="15%">{!! permSelect('view.site.inspection', 'super.plan', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.site.inspection', 'super', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('add.site.inspection', 'add', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('del.site.inspection', 'res', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('sig.site.inspection', 'sig', $rec, $cid, $dis) !!}</td>
                            </tr>
                            <tr>
                                <td>Scaffold Handover Certificate<br><span class="font-grey-silver">Cape Cod Only</span></td>
                                <td width="15%">{!! permSelect('view.site.scaffold.handover', 'super.plan', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.site.scaffold.handover', 'super', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('add.site.scaffold.handover', 'add', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('del.site.scaffold.handover', 'res', $rec, $cid, $dis) !!}</td>
                                <td width="15%"></td>
                            </tr>
                            <tr>
                                <td>Project Supply Information</td>
                                <td width="15%">{!! permSelect('view.site.project.supply', 'super', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.site.project.supply', 'super', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('add.site.project.supply', 'add', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('del.site.project.supply', 'res', $rec, $cid, $dis) !!}</td>
                                <td width="15%"></td>
                            </tr>
                        </table>
                    @endif
                </div>
            </div>
            @if (Auth::user()->isCompany($user->company))
                <div class="form-actions right">
                    <button type="submit" class="btn green">Save</button>
                </div>
            @endif
        </div>
    </div>

    {{-- General Info --}}
    <div class="portlet light">
        <div class="portlet-title">
            <div class="caption">
                <span class="caption-subject font-dark bold uppercase"> General Info</span>
            </div>
        </div>
        <div class="portlet-body form">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-striped">
                        <tr>
                            <td style="background: #FFF; border: 0px #e7ecf1; font-size: 18px; font-weight: 300; padding: 0;"></td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">View</td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Edit</td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Create</td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Delete
                                <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                   data-content="For record integrity most data can't be deleted but users may be given access to archive / resolve it instead. For data that is actually deleted you will be asked to 'confirm'"
                                   data-original-title="Delete"> <i class="fa fa-question-circle font-grey-silver"></i> </a></td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Sign Off
                                <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                   data-content="Certain actions or updates to the record are required to be 'Signed Off' by an authorised user."
                                   data-original-title="Sign Off"> <i class="fa fa-question-circle font-grey-silver"></i> </a></td>
                        </tr>
                    </table>

                    <h5 class="font-green-haze" style="font-size: 16px">Safety
                        <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                           data-content="Grants ability to view or modify documents which belong to your company."
                           data-original-title="General Documents"> <i class="fa fa-question-circle font-grey-silver"></i>
                        </a>
                    </h5>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <td>Safe Work Method Statements</td>
                            <td width="15%">{!! permSelect('view.wms', ($sub1) ? 'own' : 'all', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('edit.wms', ($sub1) ? 'own' : 'all', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('add.wms', 'add', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('del.wms', 'arc', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('sig.wms', 'sig', $rec, $cid, $dis) !!}</td>
                        </tr>
                        <tr>
                            <td>Toolbox Talks</td>
                            <td width="15%">{!! permSelect('view.toolbox', ($sub1) ? 'own' : 'all', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('edit.toolbox', ($sub1) ? 'own' : 'all', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('add.toolbox', 'add', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('del.toolbox', 'res', $rec, $cid, $dis) !!}</td>
                            <td width="15%">{!! permSelect('sig.toolbox', 'sig', $rec, $cid, $dis) !!}</td>
                        </tr>
                        <tr>
                            <td>Safety Data Sheets (SDS)</td>
                            <td width="15%">{!! permSelect('view.sds', 'all', $rec, $cid, $dis) !!}</td>
                            @if ($cc)
                                <td width="15%">{!! permSelect('edit.sds', 'all', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('add.sds', 'add', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('del.sds', 'del', $rec, $cid, $dis) !!}</td>
                                <td width="15%"></td>
                            @else
                                <td width="60%" colspan="4"></td>
                            @endif
                        </tr>
                    </table>

                    @if ($cc)
                        <h5 class="font-green-haze" style="font-size: 16px">Equipment
                            <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                               data-content="Grants ability to view or transfer equipment between sites which belong to your company."
                               data-original-title="Equipment"> <i class="fa fa-question-circle font-grey-silver"></i>
                            </a>
                        </h5>

                        <table class="table table-bordered table-striped">
                            <tr>
                                <td>Equipment Allocation</td>
                                <td width="15%">{!! permSelect('view.equipment', 'all', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.equipment', 'all', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('add.equipment', 'add', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('del.equipment', 'del', $rec, $cid, $dis) !!}</td>
                                <td width="15%"></td>
                            </tr>
                            <tr>
                                <td>Equipment Stocktake</td>
                                <td width="15%">{!! permSelect('view.equipment.stocktake', 'all', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.equipment.stocktake', 'all', $rec, $cid, $dis) !!}</td>
                                <td width="45%" colspan="3"></td>
                            </tr>
                        </table>
                    @endif
                </div>
            </div>
            @if (Auth::user()->isCompany($user->company))
                <div class="form-actions right">
                    <button type="submit" class="btn green">Save</button>
                </div>
            @endif
        </div>
    </div>

    {{-- Planners --}}
    @if ($plan)
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject font-dark bold uppercase"> Planners</span>
                </div>
            </div>
            <div class="portlet-body form">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-striped">
                            <tr>
                                <td style="background: #FFF; border: 0px #e7ecf1; font-size: 18px; font-weight: 300; padding: 0;"></td>
                                <td width="15%" style="border: 1px solid; border-color:#e7ecf1">View</td>
                                <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Edit</td>
                                <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Create</td>
                                <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Delete
                                    <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                       data-content="For record integrity most data can't be deleted but users may be given access to archive / resolve it instead. For data that is actually deleted you will be asked to 'confirm'"
                                       data-original-title="Delete"> <i class="fa fa-question-circle font-grey-silver"></i> </a></td>
                                <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Sign Off
                                    <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                       data-content="Certain actions or updates to the record are required to be 'Signed Off' by an authorised user."
                                       data-original-title="Sign Off"> <i class="fa fa-question-circle font-grey-silver"></i> </a></td>
                            </tr>
                        </table>

                        <h5 class="font-green-haze" style="font-size: 16px">Planners
                            <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                               data-content="Grants ability to view or modify planners for work sites that belong to your company."
                               data-original-title="Planners"> <i class="fa fa-question-circle font-grey-silver"></i>
                            </a>
                        </h5>
                        <table class="table table-bordered table-striped">
                            <tr>
                                <td>Weekly</td>
                                <td width="15%">{!! permSelect('view.weekly.planner', 'super.plan', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.weekly.planner', 'super.plan', $rec, $cid, $dis) !!}</td>
                                <td width="45%" colspan="3"></td>
                            </tr>
                            <tr>
                                <td>Trade / Labourer</td>
                                <td width="15%">{!! permSelect('view.trade.planner', 'super.plan', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.trade.planner', 'super.plan', $rec, $cid, $dis) !!}</td>
                                <td width="45%" colspan="3"></td>
                            </tr>
                            <tr>
                                <td>Site</td>
                                <td width="15%">{!! permSelect('view.site.planner', 'super.plan', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.site.planner', 'super.plan', $rec, $cid, $dis) !!}</td>
                                <td width="45%" colspan="3"></td>
                            </tr>
                            <tr>
                                <td>Pre-contruction</td>
                                <td width="15%">{!! permSelect('view.preconstruction.planner', 'super.plan', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.preconstruction.planner', 'super.plan', $rec, $cid, $dis) !!}</td>
                                <td width="45%" colspan="3"></td>
                            </tr>
                            <tr>
                                <td>Roster</td>
                                <td width="15%">{!! permSelect('view.roster', ($sub1) ? 'super.company' : 'super.individual', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.roster', ($sub1) ? 'super.company' : 'super.individual', $rec, $cid, $dis) !!}</td>
                                <td width="45%" colspan="3"></td>
                            </tr>
                            <tr>
                                <td>Client Planner Email</td>
                                <td width="15%">{!! permSelect('view.client.planner.email','super', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('edit.client.planner.email', 'super', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect('add.client.planner.email', 'add', $rec, $cid, $dis) !!}</td>
                                <td width="30%" colspan="2"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                @if (Auth::user()->isCompany($user->company))
                    <div class="form-actions right">
                        <button type="submit" class="btn green">Save</button>
                    </div>
                @endif
            </div>
        </div>
    @endif
@endif

{{-- Documents --}}
@if ($sub1 && Auth::user()->company_id == $user->company_id)
    <div class="portlet light">
        <div class="portlet-title">
            <div class="caption">
                <span class="caption-subject font-dark bold uppercase">Documents</span>
            </div>
        </div>
        <div class="portlet-body form">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-striped">
                        <tr>
                            <td style="background: #FFF; border: 0px #e7ecf1; font-size: 18px; font-weight: 300; padding: 0;"></td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">View</td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Edit</td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Create</td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Delete
                                <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                   data-content="For record integrity most data can't be deleted but users may be given access to archive / resolve it instead. For data that is actually deleted you will be asked to 'confirm'"
                                   data-original-title="Delete"> <i class="fa fa-question-circle font-grey-silver"></i> </a></td>
                            <td width="15%" style="border: 1px solid; border-color:#e7ecf1">Sign Off
                                <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                   data-content="Certain actions or updates to the record are required to be 'Signed Off' by an authorised user."
                                   data-original-title="Sign Off"> <i class="fa fa-question-circle font-grey-silver"></i> </a></td>
                        </tr>
                    </table>
                    <h5 class="font-green-haze" style="font-size: 16px">Public Documents</h5>
                    <table class="table table-bordered table-striped">
                        @foreach ($companyDocTypes::all() as $doc_type => $doc_name)
                            <tr>
                                <td>{{ $doc_name }}
                                    <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                       data-content="{!! $companyDocTypes::docNames($doc_type, 0) !!}" data-original-title="Documents"> <i
                                                class="fa fa-question-circle font-grey-silver"></i> </a></td>
                                <td width="15%">{!! permSelect("view.docs.$doc_type.pub", ($sub1) ? 'own' : 'all', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect("edit.docs.$doc_type.pub", ($sub1) ? 'own' : 'all', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect("add.docs.$doc_type.pub", 'up', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect("del.docs.$doc_type.pub", 'arc', $rec, $cid, $dis) !!}</td>
                                <td width="15%">{!! permSelect("sig.docs.$doc_type.pub", 'sig', $rec, $cid, $dis) !!}</td>
                            </tr>
                        @endforeach
                    </table>
                    {{--}}
                    @if ($cc)
                        <h5 class="font-green-haze" style="font-size: 16px">Private Documents</h5>
                        <table class="table table-bordered table-striped">
                            @foreach ($companyDocTypes::all() as $doc_type => $doc_name)
                                <tr>
                                    <td>{{ $doc_name }}
                                        <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                           data-content="{!! $companyDocTypes::docNames('acc', 1) !!}" data-original-title="Documents"> <i
                                                    class="fa fa-question-circle font-grey-silver"></i> </a></td>
                                    <td width="15%">{!! permSelect("view.docs.$doc_type.pri", ($sub1) ? 'own' : 'all', $rec, $cid, $dis) !!}</td>
                                    <td width="15%">{!! permSelect("edit.docs.$doc_type.pri", ($sub1) ? 'own' : 'all', $rec, $cid, $dis) !!}</td>
                                    <td width="15%">{!! permSelect("add.docs.$doc_type.pri", 'up', $rec, $cid, $dis) !!}</td>
                                    <td width="15%">{!! permSelect("del.docs.$doc_type.pri", 'arc', $rec, $cid, $dis) !!}</td>
                                    <td width="15%">{!! permSelect("sig.docs.$doc_type.pri", 'sig', $rec, $cid, $dis) !!}</td>
                                </tr>
                            @endforeach
                        </table>
                    @endif --}}
                </div>
            </div>
            @if (Auth::user()->isCompany($user->company))
                <div class="form-actions right">
                    <button type="submit" class="btn green">Save</button>
                </div>
            @endif
        </div>
    </div>
@endif