<div class="top-menu">
    <ul class="nav navbar-nav pull-right">
        <!-- BEGIN NOTIFICATION DROPDOWN -->

        <li class="dropdown dropdown-extended dropdown-notification dropdown-dark" id="header_notification_bar">
            <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                <i class="icon-bell"></i>
                @if (Auth::user()->todo('1')->count())
                    <span class="badge badge-default">{{ Auth::user()->todo('1')->count() }}</span>
                @endif
            </a>
            <ul class="dropdown-menu">
                <li class="external">
                    <h3>You have <strong>{{ Auth::user()->todo('1')->count() }} outstanding</strong> tasks</h3>
                    <a href="/todo">view all</a>
                </li>
                <li>
                    <ul class="dropdown-menu-list scroller" style="height: 250px;" data-handle-color="#637283">
                        {{-- Outstanding ToDoo Type for Users --}}
                        @foreach (TODO_TYPES as $type => $name)
                            @foreach(Auth::user()->todoType($type, 1) as $todo)
                                <li>
                                    <a href="{{ $todo->url() }}">
                                        <span class="time">{!! ($todo->due_at) ? $todo->due_at->format('d/m/Y') : '' !!}</span>
                                    <span class="details">
                                        <span class="badge badge-success badge-roundless"><i class="fa fa-plus"></i></span>
                                    <span style="line-height: 25px">&nbsp; {{ $todo->name }} {{ $todo->id }}:</span>
                                </span>
                                    </a>
                                </li>
                            @endforeach
                        @endforeach

                        <?php $todo_types = ['company doc', 'company ptc', 'company privacy']; ?>
                        @foreach ($todo_types as $type)
                            @foreach(Auth::user()->todoType($type, 1) as $todo)
                                <li>
                                    <a href="{{ $todo->url() }}">
                                        <span class="time">{!! ($todo->due_at) ? $todo->due_at->format('d/m/Y') : '' !!}</span>
                                    <span class="details">
                                        <span class="badge badge-success badge-roundless"><i class="fa fa-plus"></i></span>
                                    <span style="line-height: 25px">&nbsp; {{ $todo->name }} {{ $todo->id }}:</span>
                                </span>
                                    </a>
                                </li>
                            @endforeach
                        @endforeach
                    </ul>
                </li>
            </ul>
        </li>
        <!-- END NOTIFICATION DROPDOWN -->
        <!-- BEGIN TODO DROPDOWN -->
        {{--
        <li class="dropdown dropdown-extended dropdown-tasks dropdown-dark" id="header_task_bar">
            <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                <i class="icon-calendar"></i>
                <span class="badge badge-default">3</span>
            </a>
            <ul class="dropdown-menu extended tasks">
                <li class="external">
                    <h3>You have
                        <strong>12 pending</strong> tasks</h3>
                    <a href="app_todo_2.html">view all</a>
                </li>
                <li>
                    <ul class="dropdown-menu-list scroller" style="height: 275px;" data-handle-color="#637283">
                        <li>
                            <a href="javascript:;">
                                <span class="task">
                                    <span class="desc">New release v1.2 </span>
                                    <span class="percent">30%</span>
                                </span>
                                <span class="progress">
                                    <span style="width: 40%;" class="progress-bar progress-bar-success" aria-valuenow="40"
                                          aria-valuemin="0" aria-valuemax="100">
                                        <span class="sr-only">40% Complete</span>
                                    </span>
                                </span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>--}}
                <!-- END TODO DROPDOWN -->
        <li class="droddown dropdown-separator">
            <span class="separator"></span>
        </li>
        <!-- BEGIN INBOX DROPDOWN -->
        <li class="dropdown dropdown-extended dropdown-inbox dropdown-dark" id="header_inbox_bar">
            <!--<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                <span class="circle">3</span>
                <span class="corner"></span>
            </a>
            <ul class="dropdown-menu">
                <li class="external">
                    <h3>You have
                        <strong>7 New</strong> Messages</h3>
                    <a href="app_inbox.html">view all</a>
                </li>
                <li>
                    <ul class="dropdown-menu-list scroller" style="height: 275px;" data-handle-color="#637283">
                        <li>
                            <a href="#">
                                                    <span class="photo">
                                                        <img src="/assets/layouts/layout3/img/avatar3.jpg" class="img-circle" alt=""> </span>
                                                    <span class="subject">
                                                        <span class="from"> Richard Doe </span>
                                                        <span class="time">46 mins </span>
                                                    </span>
                                <span class="message"> Vivamus sed congue nibh auctor nibh congue nibh. auctor nibh auctor nibh... </span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul> -->
        </li>
        <!-- END INBOX DROPDOWN -->
        <!-- BEGIN USER LOGIN DROPDOWN -->
        <li class="dropdown dropdown-user dropdown-dark">
            <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                <img alt="" class="img" src="/img/user_icon.png">
                <span class="username username-hide-mobile">
                    @if (Auth::check())
                        @if (Auth::user()->username == 'admin')
                            <span class="label label-danger">admin</span>
                        @elseif (Auth::user()->firstname)
                            {{ Auth::user()->firstname }}
                        @else
                            {{ Auth::user()->username }}
                        @endif
                    @endif
                </span>
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
        <!-- END USER LOGIN DROPDOWN -->
        <!-- BEGIN QUICK SIDEBAR TOGGLER -->
        <!--<li class="dropdown dropdown-extended quick-sidebar-toggler">
            <span class="sr-only">Toggle Quick Sidebar</span>
            <i class="icon-logout"></i>
        </li>-->
        <!-- END QUICK SIDEBAR TOGGLER -->
    </ul>
</div>