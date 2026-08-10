@inject('ozstates', 'App\Http\Utilities\OzStates')
@inject('companyEntity', 'App\Http\Utilities\CompanyEntityTypes')
@inject('companyDocTypes', 'App\Http\Utilities\CompanyDocTypes')
@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->allowed2('view.company', $user->company))
            <li><a href="/company/{{ $user->company_id }}">Company</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('user'))
            <li><a href="/company/{{ Auth::user()->company->id}}/user">Users</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Profile</span></li>
    </ul>
@stop


@section('content')
    {{-- BEGIN PAGE CONTENT INNER --}}
    <div class="page-content-inner">
        @include('user/_header')

        <form method="POST" action="{{ action([App\Http\Controllers\UserController::class, 'updateSecurity'], $user->id) }}">
            @csrf
        <div class="form-body">
            <div class="tabbable tabbable-tabdrop">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#tab1" data-toggle="tab">
                            <div class="hidden-sm hidden-xs"><h3><b>{{ $user->company->name_alias }}</b></h3></div>
                            <div class="visible-sm visible-xs"><b>{{ $user->company->name_alias }}</b></div>
                        </a>
                    </li>
                    @if ($user->company->parent_company)
                        <li>
                            <a href="#tab2" data-toggle="tab">
                                <div class="hidden-sm hidden-xs"><h3><b>{{ $user->company->reportsTo()->name }}</b></h3></div>
                                <div class="visible-sm visible-xs"><b>{{ $user->company->reportsTo()->name }}</b></div>
                            </a>
                        </li>
                    @endif
                </ul>
                <div class="tab-content" style="margin-top: -10px">
                    <div class="tab-pane active" id="tab1">
                        @include('user/_tab-security-internal')
                    </div>
                    @if ($user->company->parent_company)
                        <div class="tab-pane" id="tab2">
                            @include('user/_tab-security-external')
                        </div>
                    @endif
                </div>
            </div>
        </div>
        </form>
    </div>
    <div>
        <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
            {!! $user->displayUpdatedBy() !!}
        </div>
    </div>
    <!-- END PAGE CONTENT INNER -->
@stop

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" tytype="text/css"/>
@stop

@section('page-level-styles-head')
    <link href="/assets/pages/css/profile-2.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function () {

        /* Select2 */
        $("#roles").select2({
            placeholder: "Select role",
            width: '100%',
        });

    });
</script>
@stop