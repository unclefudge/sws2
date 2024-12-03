<div class="page-header-menu">
    <div class="container">
        <!-- BEGIN HEADER SEARCH BOX -->
        <!--
        <form class="search-form" action="page_general_search.html" method="GET">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Search" name="query">
                            <span class="input-group-btn">
                                <a href="javascript:;" class="btn submit">
                                    <i class="icon-magnifier"></i>
                                </a>
                            </span>
            </div>
        </form>
        -->
        <!-- END HEADER SEARCH BOX -->
        <!-- BEGIN MEGA MENU -->
        <!-- DOC: Apply "hor-menu-light" class after the "hor-menu" class below to have a horizontal menu with white background -->
        <!-- DOC: Remove data-hover="dropdown" and data-close-others="true" attributes below to disable the dropdown opening on mouse hover -->
        <div class="hor-menu  ">
            <ul class="nav navbar-nav">
                <li class="menu-dropdown classic-menu-dropdown {{ (Request::is('dashboard') ? 'active' : '') }}">
                    <a href="/"><i class="fa fa-home"></i> Home </a>
                </li>
                <li class="menu-dropdown classic-menu-dropdown visible-sm visible-xs">
                    <a href="javascript:;"><i class="fa fa-user"></i> Profile &nbsp; ({!! (Auth::user()->firstname) ? Auth::user()->firstname : Auth::user()->username !!})
                        <span class="arrow"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-default">
                        <li><a href="/user/{{ Auth::user()->id }}"><i class="fa fa-user"></i> My Profile </a></li>
                        @if(Auth::user()->hasAnyPermission2('view.company|edit.company'))
                            <li><a href="/company/{{ Auth::user()->company_id }}"><i class="fa fa-users"></i> Company Profile </a></li>
                        @endif
                        <li class="divider"></li>
                        @if (Auth::user()->id == 3)
                            <li><a href="/userlog"><i class="fa fa-users"></i> Login as User </a></li>
                            <li class="divider"></li>
                        @endif
                        <li>
                            <a href="/logout">
                                <i class="fa fa-key"></i> Log Out </a>
                        </li>
                    </ul>
                </li>

                @if (Auth::user()->company->status == 1)
                    {{-----------------------------------------------------------------------------------
                       Job Site Info
                     -----------------------------------------------------------------------------------}}
                    @if (Auth::user()->hasAnyPermissionType('site.hazard|site.accident|safety.doc|site.doc|site'))
                        <li class="menu-dropdown mega-menu-dropdown mega-menu-full">
                            <a href="javascript:;"><i class="fa fa-wrench"></i> Job Site Info
                                <span class="arrow"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <div class="mega-menu-content">
                                        <div class="row">
                                            <div class="col-md-1 hidden-sm hidden-xs"><img src="/img/menu_siteinfo.png"></div>
                                            {{-- Site Info Safety --}}
                                            @if (Auth::user()->hasAnyPermissionType('site.hazard|site.incident|site.accident|safety.doc'))
                                                <div class="col-md-2">
                                                    <ul class="mega-menu-submenu">
                                                        <li><h3 class="h3-submenu">Safety</h3></li>
                                                        @if (Auth::user()->hasAnyPermissionType('site.hazard'))
                                                            <li><a href="/site/hazard" class="nav-link @if (Auth::user()->siteHazards('1')->count()) font-yellow-lemon @endif">Hazards</a></li>
                                                        @endif

                                                        @if (Auth::user()->hasAnyPermissionType('site.accident') && !Auth::user()->hasAnyPermissionType('site.incident'))
                                                            <li><a href="/site/accident" class="nav-link @if (Auth::user()->siteAccidents('1')->count()) font-yellow-lemon @endif">Accidents</a>
                                                            </li>
                                                        @endif
                                                        @if (Auth::user()->hasAnyPermissionType('site.incident'))
                                                            <li><a href="/site/incident" class="nav-link @if (Auth::user()->siteincidents('1')->count()) font-yellow-lemon @endif">Incidents</a>
                                                            </li>
                                                        @endif

                                                        @if (Auth::user()->hasAnyPermissionType('safety.doc'))
                                                            <li><a href="/site/doc/type/risk" class="nav-link "> Risk Assessments</a></li>
                                                            <li><a href="/site/doc/type/hazard" class="nav-link "> Hazardous Materials</a></li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            @endif
                                            @if (Auth::user()->hasAnyPermissionType('site.asbestos|site.scaffold.handover'))
                                                <div class="col-md-2">
                                                    <ul class="mega-menu-submenu">
                                                        <li><h3>&nbsp;</h3></li>
                                                        @if (Auth::user()->hasAnyPermissionType('site.asbestos'))
                                                            <li><a href="/site/asbestos/register" class="nav-link "> Asbestos Register</a></li>
                                                            <li><a href="/site/asbestos/notification" class="nav-link "> Asbestos Notifications</a></li>
                                                        @endif
                                                        @if (Auth::user()->hasAnyPermissionType('site.scaffold.handover'))
                                                            <li><a href="/site/scaffold/handover" class="nav-link "> Scaffold Handover</a></li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            @endif
                                            {{-- Site Info Document --}}
                                            @if (Auth::user()->hasAnyPermissionType('site.doc|site.list'))
                                                <div class="col-md-2">
                                                    <ul class="mega-menu-submenu">
                                                        <li><h3 class="h3-submenu">Documents</h3></li>
                                                        @if (Auth::user()->hasAnyPermissionType('site.doc'))
                                                            <li><a href="/site/doc/type/plan" class="nav-link"> Site Plans </a></li>
                                                        @endif

                                                        @if (Auth::user()->hasAnyPermissionType('site.list'))
                                                            <li><a href="/sitelist" class="nav-link"> Site List </a></li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            @endif
                                            {{-- Site Info Document --}}
                                            @if (Auth::user()->hasAnyPermissionType('site.attendance|site.qa|site.qa|site.qa.templates|site.maintenance|site.project.supply|site.inspection|site.upcoming.compliance|super.checklist'))
                                                <div class="col-md-2">
                                                    <ul class="mega-menu-submenu">
                                                        <li><h3 class="h3-submenu">Reports / Info</h3></li>
                                                        @if (Auth::user()->hasAnyPermissionType('site.attendance'))
                                                            <li><a href="/site/attendance" class="nav-link"> Site Attendance </a></li>
                                                        @endif
                                                        @if (Auth::user()->hasAnyPermissionType('prac.completion'))
                                                            <li><a href="/site/prac-completion" class="nav-link"> Prac Completion </a></li>
                                                        @endif
                                                        @if (Auth::user()->hasAnyPermissionType('site.maintenance'))
                                                            <li><a href="/site/maintenance" class="nav-link"> Maintenance Requests </a></li>
                                                        @endif
                                                        @if (Auth::user()->hasAnyPermissionType('site.qa'))
                                                            <li><a href="/site/qa" class="nav-link"> Quality Assurance </a></li>
                                                        @endif
                                                        @if (Auth::user()->hasAnyPermissionType('super.checklist') || Auth::user()->hasAnyRole2('con-supervisor|web-admin|mgt-general-manager'))
                                                            <li><a href="/supervisor/checklist/checklist" class="nav-link"> Supervisor Checklist </a></li>
                                                        @endif
                                                        @if (Auth::user()->hasAnyPermissionType('site.note'))
                                                            <li><a href="/site/note" class="nav-link"> Site Notes </a></li>
                                                        @endif
                                                    </ul>
                                                </div>
                                                <div class="col-md-2">
                                                    <ul class="mega-menu-submenu">
                                                        <li><h3 class="h3-submenu">&nbsp;</h3></li>
                                                        @if (Auth::user()->hasAnyPermissionType('site.inspection.whs'))
                                                            <li><a href="/site/inspection" class="nav-link"> Site Inspection</a></li>
                                                        @endif
                                                        @if (Auth::user()->hasAnyPermissionType('site.inspection'))
                                                            <li><a href="/site/inspection/electrical" class="nav-link"> Electrical Inspection </a></li>
                                                            <li><a href="/site/inspection/plumbing" class="nav-link"> Plumbing Inspection </a></li>
                                                        @endif
                                                        @if (Auth::user()->hasAnyPermissionType('site.project.supply'))
                                                            <li><a href="/site/supply" class="nav-link"> Project Suppy Info </a></li>
                                                            @if (in_array(Auth::user()->id, [3,108]))
                                                                <li><a href="/site/shutdown" class="nav-link"> Site Shutdown </a></li>
                                                            @endif
                                                        @endif
                                                        @if (Auth::user()->hasAnyPermissionType('site.upcoming.compliance'))
                                                            <li><a href="/site/upcoming/compliance" class="nav-link"> Upcoming Jobs </a></li>
                                                        @endif
                                                        @if (Auth::user()->hasAnyPermissionType('site.extension'))
                                                            <li><a href="/site/extension" class="nav-link"> Contract Extensions</a></li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            @endif
                                            <!--<div class="col-md-3 hidden-sm hidden-xs pull-right"><img src="/img/think-safety.png"></div>-->
                                        </div>
                                        <div class="row hidden-sm hidden-xs" style="background:#444d58; border-top: 1px solid grey; padding:10px; margin-bottom: -50px">
                                            <div class="col-md-4">&nbsp;</div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    @endif

                    {{-----------------------------------------------------------------------------------
                       General Info
                     -----------------------------------------------------------------------------------}}
                    @if (Auth::user()->hasAnyPermissionType('wms|toolbox|sds'))
                        <li class="menu-dropdown mega-menu-dropdown mega-menu-full">
                            <a href="javascript:;"><i class="fa fa-file-text-o"></i> General Info
                                <span class="arrow"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <div class="mega-menu-content">
                                        <div class="row">
                                            <div class="col-md-1 hidden-sm hidden-xs"><img src="/img/menu_generalinfo.png"></div>
                                            {{-- General Info Safety --}}
                                            @if (Auth::user()->hasAnyPermissionType('wms|toolbox|sds'))
                                                <div class="col-md-2">
                                                    <ul class="mega-menu-submenu">
                                                        <li><h3 class="h3-submenu">Safety</h3></li>
                                                        @if (Auth::user()->hasAnyPermissionType('wms'))
                                                            <li><a href="/safety/doc/wms" class="nav-link "> SWMS</a></li>
                                                        @endif
                                                        @if (Auth::user()->hasAnyPermissionType('toolbox'))
                                                            <li><a href="/safety/doc/toolbox2" class="nav-link "> Toolbox Talks</a></li>
                                                        @endif
                                                        @if (Auth::user()->hasAnyPermissionType('toolbox') && in_array(Auth::user()->id, [3, 108]))
                                                            <li><a href="/safety/doc/toolbox3" class="nav-link "> Toolbox Talks3</a></li>
                                                        @endif
                                                        @if (Auth::user()->hasAnyPermissionType('sds'))
                                                            <li><a href="/safety/doc/sds" class="nav-link "> Safety Data Sheets</a></li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            @endif
                                            @if (Auth::user()->isCC() || Auth::user()->company->parent_company == 3)
                                                <div class="col-md-2">
                                                    <ul class="mega-menu-submenu">
                                                        <li><h3 class="h3-submenu">Documents</h3></li>
                                                        <li><a href="/company/doc/standard" class="nav-link "> Standard Details</a></li>
                                                        <li><a href="/construction/doc/standards" class="nav-link "> Construction Standards</a></li>
                                                        {{--}}@if (Auth::user()->hasAnyPermissionType('company.doc.review'))
                                                            <li><a href="/equipment" class="nav-link "> Standard Details Review</a></li>
                                                        @endif --}}
                                                    </ul>
                                                </div>
                                            @endif
                                            @if (Auth::user()->hasAnyPermissionType('equipment|equipment.stocktake'))
                                                <div class="col-md-2">
                                                    <ul class="mega-menu-submenu">
                                                        <li><h3 class="h3-submenu">Equipment</h3></li>
                                                        @if (Auth::user()->hasAnyPermissionType('equipment'))
                                                            <li><a href="/equipment" class="nav-link "> Equipment Allocation</a></li>
                                                        @endif
                                                        @if (Auth::user()->hasAnyPermissionType('equipment.stocktake'))
                                                            <li><a href="/equipment/stocktake/0" class="nav-link "> Equipment Stocktake</a></li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            @endif
                                            <div class="col-md-3 hidden-sm hidden-xs pull-right"><img src="/img/think-safety.png"></div>
                                        </div>
                                        <div class="row hidden-sm hidden-xs" style="background:#444d58; border-top: 1px solid grey; padding:10px; margin-bottom: -50px">
                                            <div class="col-md-4">&nbsp;</div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    @endif

                    {{-----------------------------------------------------------------------------------
                       Planners
                     -----------------------------------------------------------------------------------}}
                    @if (Auth::user()->hasAnyPermissionType('weekly.planner|site.planner|trade.planner|roster|preconstruction.planner'))
                        <li class="menu-dropdown mega-menu-dropdown mega-menu-full">
                            <a href="javascript:;"><i class="fa fa-calendar"></i> Planners
                                <span class="arrow"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <div class="mega-menu-content">
                                        <div class="row">
                                            <div class="col-md-1 hidden-sm hidden-xs"><img src="/img/menu_planners.png"></div>
                                            <div class="col-md-2">
                                                <ul class="mega-menu-submenu">
                                                    <li><h3 class="h3-submenu">Planners</h3></li>
                                                    @if (Auth::user()->hasAnyPermissionType('weekly.planner'))
                                                        <li><a href="/planner/weekly" class="nav-link"> Weekly Planner </a></li>
                                                    @endif
                                                    @if (Auth::user()->hasAnyPermissionType('trade.planner'))
                                                        <li><a href="/planner/trade" class="nav-link"> Trade Planner </a></li>
                                                    @endif
                                                    @if (Auth::user()->hasAnyPermissionType('site.planner'))
                                                        <li><a href="/planner/site" class="nav-link"> Site Planner </a></li>
                                                    @endif
                                                    @if (Auth::user()->hasAnyPermissionType('roster'))
                                                        <li><a href="/planner/roster" class="nav-link"> Site Roster </a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                            <div class="col-md-2">
                                                <ul class="mega-menu-submenu">
                                                    <li><h3 class="h3-submenu">&nbsp;</h3></li>
                                                    @if (Auth::user()->hasAnyPermissionType('trade.planner'))
                                                        <li><a href="/planner/transient" class="nav-link"> Labourer Planner </a></li>
                                                    @endif
                                                    @if (Auth::user()->hasAnyPermissionType('preconstruction.planner'))
                                                        <li><a href="/planner/upcoming" class="nav-link"> Upcoming Projects </a></li>
                                                        {{--<li><a href="/planner/preconstruction" class="nav-link"> Pre-construction Planner </a></li>--}}
                                                    @endif
                                                </ul>
                                            </div>
                                            <div class="col-md-2">
                                                <ul class="mega-menu-submenu">
                                                    @if (Auth::user()->hasAnyPermissionType('client.planner.email'))
                                                        <li><h3 class="h3-submenu">Reports</h3></li>
                                                        <li><a href="/client/planner/email" class="nav-link"> Client Email </a></li>
                                                        <li><a href="/site/export/plan" class="nav-link"> Planner Export </a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                            <div class="col-md-2 hidden-sm hidden-xs"></div>
                                            <div class="col-md-3 hidden-sm hidden-xs"><img src="/img/think-safety.png"></div>
                                        </div>
                                        <div class="row hidden-sm hidden-xs"
                                             style="background:#444d58; border-top: 1px solid grey; padding:10px; margin-bottom: -50px">
                                            <div class="col-md-4">&nbsp;</div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    @endif

                    {{-----------------------------------------------------------------------------------
                       Management
                     -----------------------------------------------------------------------------------}}
                    @if (Auth::user()->hasAnyPermissionType('user|company|client|trade|compliance|site.export|safetytip|settings|') || Auth::user()->allowed2('add.company.doc'))
                        <li class="menu-dropdown mega-menu-dropdown mega-menu-full">
                            <a href="javascript:;"><i class="fa fa-crosshairs"></i> Management
                                <span class="arrow"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <div class="mega-menu-content">
                                        <div class="row">
                                            <div class="col-md-1 hidden-sm hidden-xs"><img src="/img/menu_management.png"></div>
                                            @if(Auth::user()->hasAnyPermissionType('user|company|client'))
                                                <div class="col-md-2">
                                                    <ul class="mega-menu-submenu">
                                                        <li><h3 class="h3-submenu">User / Company</h3></li>
                                                        @if (Auth::user()->hasAnyPermissionType('user'))
                                                            <li><a href="/company/{{ Auth::user()->company_id }}/user" class="nav-link"> Users </a></li>
                                                        @endif
                                                        @if(Auth::user()->hasAnyPermissionType('company'))
                                                            <li><a href="/company" class="nav-link"> Companies </a></li>
                                                        @endif
                                                        @if (Auth::user()->company->subscription > 1 && Auth::user()->hasAnyPermissionType('company.leave'))
                                                            <li><a href="/company/leave" class="nav-link"> Company Leave </a></li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            @endif

                                            @if (Auth::user()->company->subscription && Auth::user()->hasAnyPermissionType('site|trade|area.super'))
                                                <div class="col-md-2">
                                                    <ul class="mega-menu-submenu">
                                                        @if (Auth::user()->hasAnyPermissionType('site|trade|area.super'))
                                                            <li><h3 class="h3-submenu">Construction</h3></li>
                                                            @if (Auth::user()->hasAnyPermissionType('site'))
                                                                <li><a href="/site" class="nav-link"> Sites </a></li>
                                                            @endif
                                                            @if (Auth::user()->hasAnyPermissionType('trade'))
                                                                <li><a href="/trade" class="nav-link"> Trades </a></li>
                                                            @endif
                                                            @if(Auth::user()->hasAnyPermissionType('area.super'))
                                                                <li><a href="/site/supervisor" class="nav-link"> Supervisors</a></li>
                                                            @endif
                                                        @endif
                                                    </ul>
                                                </div>
                                            @endif

                                            @if (Auth::user()->hasAnyPermissionType('compliance|safetytip|notify'))
                                                <div class="col-md-2">
                                                    <ul class="mega-menu-submenu">
                                                        @if (Auth::user()->hasAnyPermissionType('compliance|safetytip|notify'))
                                                            <li><h3 class="h3-submenu">Daily / Alerts</h3></li>
                                                            @if (Auth::user()->hasAnyPermissionType('compliance'))
                                                                <li><a href="/site/compliance" class="nav-link"> Compliance </a></li>
                                                            @endif
                                                            @if (Auth::user()->hasAnyPermissionType('safetytip'))
                                                                <li><a href="/safety/tip" class="nav-link"> Safety Tips </a></li>
                                                            @endif
                                                            @if (Auth::user()->hasAnyPermissionType('notify'))
                                                                <li><a href="/comms/notify" class="nav-link"> Alert Notifications </a></li>
                                                            @endif
                                                        @endif
                                                    </ul>
                                                </div>
                                            @endif


                                            @if(Auth::user()->hasAnyPermissionType('manage.report|site.export') || Auth::user()->hasAnyPermission2('add.site.doc|edit.site.doc|del.site.doc|add.safety.doc|edit.safety.doc|del.safety.doc'))
                                                <div class="col-md-2">
                                                    <ul class="mega-menu-submenu">
                                                        @if(Auth::user()->hasAnyPermissionType('manage.report|site.export') || Auth::user()->hasAnyPermission2('add.site.doc|edit.site.doc|del.site.doc|add.safety.doc|edit.safety.doc|del.safety.doc'))
                                                            <li><h3 class="h3-submenu">Reports / Exports</h3></li>
                                                            @if (Auth::user()->hasAnyPermission2('add.site.doc|edit.site.doc|del.site.doc|add.safety.doc|edit.safety.doc|del.safety.doc'))
                                                                <li><a href="/manage/file" class="nav-link"> File Manager </a></li>
                                                            @endif
                                                            @if (Auth::user()->hasAnyPermissionType('site.export'))
                                                                <li><a href="/site/export" class="nav-link"> Export Site Data </a></li>
                                                            @endif
                                                            @if (Auth::user()->hasAnyPermissionType('manage.report'))
                                                                <li><a href="/manage/report" class="nav-link"> Management Reports</a></li>
                                                            @endif
                                                        @endif
                                                    </ul>
                                                </div>
                                            @endif


                                            @if(Auth::user()->hasAnyPermissionType('settings'))
                                                <div class="col-md-2">
                                                    <ul class="mega-menu-submenu">
                                                        @if(Auth::user()->hasAnyPermissionType('settings'))
                                                            <li><h3 class="h3-submenu">Configuration</h3></li>
                                                            @if(Auth::user()->hasAnyPermissionType('settings'))
                                                                <li><a href="/settings" class="nav-link"> Settings</a></li>
                                                            @endif
                                                        @endif
                                                    </ul>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="row hidden-sm hidden-xs" style="background:#444d58; border-top: 1px solid grey; padding:10px; margin-bottom: -50px">
                                            <div class="col-md-4">&nbsp;</div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    @endif
                @endif {{-- End - Company status == 1 --}}

                @if(Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
                    <li class="menu-dropdown classic-menu-dropdown {{ (Request::is('dashboard') ? 'active' : '') }}">
                        <a href="/support/ticket"><i class="fa fa-tag"></i> Support </a>
                    </li>
                @endif


            </ul>
        </div>
        <!-- END MEGA MENU -->
    </div>
</div>