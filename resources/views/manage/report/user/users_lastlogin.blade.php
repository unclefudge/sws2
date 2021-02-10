@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Users Last Login</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Onsite Users Last Login</span>
                        </div>
                        <div class="actions">
                            <a class="btn btn-circle btn-icon-only btn-default fullscreen" href="javascript:;"></a>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <?php
                        $listed = [];
                        $never = [];
                        foreach ($users->sortBy('company_id') as $user) {
                            if (in_array($user->company->category, [1, 2]) && $user->company->status == 1 && (!$user->last_login || $user->last_login->format('d/m/Y') == '30/11/-0001')) {
                                $listed[] = $user->id;
                                $never[] = $user->id;
                            }
                        }
                        ?>
                        <h3>Last Logged Over 1 Week Ago</h3>
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th width="15%"> Username</th>
                                <th width="20%"> Name</th>
                                <th> Company</th>
                                <th width="15%"> Company On Planner</th>
                                <th width="15%"> Last Login Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($over_1_week as $user)
                                <?php
                                $listed[] = $user->id;
                                $lastDate = ($user->company->lastDateOnPlanner()) ? $user->company->lastDateOnPlanner() : \Carbon\Carbon::now()->subYears(10);
                                ?>
                                @if (in_array($user->company->category, [1]) && $user->company->status == 1 && $user->last_login->lt($user->company->lastDateOnPlanner()) && $user->hasAnyRole2('ext-leading-hand|tradie|labourers'))
                                    <tr>
                                        <td>
                                            <div class="text-center"><a href="/user/{{$user->id}}"><i class="fa fa-search"></i></a></div>
                                        </td>
                                        <td>{{ $user->username }}</td>
                                        <td>{{ $user->fullname }}</td>
                                        <td>{{ $user->company->name_alias }}</td>
                                        <td>{!! $lastDate->format('d/m/Y') !!}</td>
                                        <td>{{ $user->last_login->format('d/m/Y') }}</td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>


                        <h3>Last Logged Over 2 Weeks Ago</h3>
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th width="15%"> Username</th>
                                <th width="20%"> Name</th>
                                <th> Company</th>
                                <th width="15%"> Company On Planner</th>
                                <th width="15%"> Last Login Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($over_2_week as $user)
                                <?php
                                $listed[] = $user->id;
                                $lastDate = ($user->company->lastDateOnPlanner()) ? $user->company->lastDateOnPlanner() : \Carbon\Carbon::now()->subYears(10);
                                ?>
                                @if (in_array($user->company->category, [1]) && $user->company->status == 1 && $user->last_login->lt($user->company->lastDateOnPlanner()) && $user->hasAnyRole2('ext-leading-hand|tradie|labourers'))
                                    <tr>
                                        <td>
                                            <div class="text-center"><a href="/user/{{$user->id}}"><i class="fa fa-search"></i></a></div>
                                        </td>
                                        <td>{{ $user->username }}</td>
                                        <td>{{ $user->fullname }}</td>
                                        <td>{{ $user->company->name_alias }}</td>
                                        <td>{!! $lastDate->format('d/m/Y') !!}</td>
                                        <td>{{ $user->last_login->format('d/m/Y') }}</td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>

                        <h3>Last Logged Over 3 Weeks Ago</h3>
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th width="15%"> Username</th>
                                <th width="20%"> Name</th>
                                <th> Company</th>
                                <th width="15%"> Company On Planner</th>
                                <th width="15%"> Last Login Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($over_3_week as $user)
                                <?php
                                $listed[] = $user->id;
                                $lastDate = ($user->company->lastDateOnPlanner()) ? $user->company->lastDateOnPlanner() : \Carbon\Carbon::now()->subYears(10);
                                ?>
                                @if (in_array($user->company->category, [1]) && $user->company->status == 1 && $user->last_login->lt($user->company->lastDateOnPlanner()) && $user->hasAnyRole2('ext-leading-hand|tradie|labourers'))
                                    <tr>
                                        <td>
                                            <div class="text-center"><a href="/user/{{$user->id}}"><i class="fa fa-search"></i></a></div>
                                        </td>
                                        <td>{{ $user->username }}</td>
                                        <td>{{ $user->fullname }}</td>
                                        <td>{{ $user->company->name_alias }}</td>
                                        <td>{!! $lastDate->format('d/m/Y') !!}</td>
                                        <td>{{ $user->last_login->format('d/m/Y') }}</td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>

                        <h3>Last Logged Over 4 Weeks Ago</h3>
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th width="15%"> Username</th>
                                <th width="20%"> Name</th>
                                <th> Company</th>
                                <th width="15%"> Company On Planner</th>
                                <th width="15%"> Last Login Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($over_4_week as $user)
                                <?php
                                $listed[] = $user->id;
                                $lastDate = ($user->company->lastDateOnPlanner()) ? $user->company->lastDateOnPlanner() : \Carbon\Carbon::now()->subYears(10);
                                ?>
                                @if (in_array($user->company->category, [1]) && $user->company->status == 1 && $user->last_login->lt($user->company->lastDateOnPlanner()) && $user->hasAnyRole2('ext-leading-hand|tradie|labourers'))
                                    <tr>
                                        <td>
                                            <div class="text-center"><a href="/user/{{$user->id}}"><i class="fa fa-search"></i></a></div>
                                        </td>
                                        <td>{{ $user->username }}</td>
                                        <td>{{ $user->fullname }}</td>
                                        <td>{{ $user->company->name_alias }}</td>
                                        <td>{!! $lastDate->format('d/m/Y') !!}</td>
                                        <td>{{ $user->last_login->format('d/m/Y') }}</td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>

                        <h3>Last Logged Over 3 Months Ago</h3>
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th width="15%"> Username</th>
                                <th width="20%"> Name</th>
                                <th> Company</th>
                                <th width="15%"> Company On Planner</th>
                                <th width="15%"> Last Login Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($over_3_month as $user)
                                <?php
                                $listed[] = $user->id;
                                $lastDate = ($user->company->lastDateOnPlanner()) ? $user->company->lastDateOnPlanner() : \Carbon\Carbon::now()->subYears(10);
                                ?>
                                @if (in_array($user->company->category, [1]) && $user->company->status == 1 && $user->last_login->lt($user->company->lastDateOnPlanner()) && $user->hasAnyRole2('ext-leading-hand|tradie|labourers'))
                                    <tr>
                                        <td>
                                            <div class="text-center"><a href="/user/{{$user->id}}"><i class="fa fa-search"></i></a></div>
                                        </td>
                                        <td>{{ $user->username }}</td>
                                        <td>{{ $user->fullname }}</td>
                                        <td>{{ $user->company->name_alias }}</td>
                                        <td>{!! $lastDate->format('d/m/Y') !!}</td>
                                        <td>{{ $user->last_login->format('d/m/Y') }}</td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>

                        <h3>Last Logged In Over 6 Months</h3>
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th width="15%"> Username</th>
                                <th width="20%"> Name</th>
                                <th> Company</th>
                                <th width="15%"> Company On Planner</th>
                                <th width="15%"> Last Login Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($users->sortBy('company_id') as $user)
                                <?php
                                $lastDate = ($user->company->lastDateOnPlanner()) ? $user->company->lastDateOnPlanner() : \Carbon\Carbon::now()->subYears(10);
                                ?>
                                @if (in_array($user->company->category, [1]) && $user->company->status == 1 && $user->last_login && $user->last_login->lt(\Carbon\Carbon::now()->subMonths(6)) &&
                                $user->hasAnyRole2('ext-leading-hand|tradie|labourers') && (!in_array($user->id, $listed) && $user->last_login->lt($user->company->lastDateOnPlanner())))
                                    <tr>
                                        <td>
                                            <div class="text-center"><a href="/user/{{$user->id}}"><i class="fa fa-search"></i></a></div>
                                        </td>
                                        <td>{{ $user->username }}</td>
                                        <td>{{ $user->fullname }}</td>
                                        <td>{{ $user->company->name_alias }}</td>
                                        <td>{!! $lastDate->format('d/m/Y') !!}</td>
                                        <td>{{ $user->last_login->format('d/m/Y') }}</td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>

                        <h3>Never Logged In</h3>
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th width="15%"> Username</th>
                                <th width="20%"> Name</th>
                                <th> Company</th>
                                <th width="15%"> Company On Planner</th>
                                <th width="15%"> Last Login Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($users->sortBy('company_id') as $user)
                                @if (in_array($user->company->category, [1]) && $user->company->status == 1 && (!$user->last_login || $user->last_login->format('d/m/Y') == '30/11/-0001'))
                                    <?php
                                    $listed[] = $user->id;
                                    $lastDate = ($user->company->lastDateOnPlanner()) ? $user->company->lastDateOnPlanner()->format('d/m/Y') : 'Never';
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="text-center"><a href="/user/{{$user->id}}"><i class="fa fa-search"></i></a></div>
                                        </td>
                                        <td>{{ $user->username }}</td>
                                        <td>{{ $user->fullname }}</td>
                                        <td>{{ $user->company->name_alias }}</td>
                                        <td>{!! $lastDate !!}</td>
                                        <td>Never</td>
                                    </tr>
                                @endif
                            @endforeach
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

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
@stop