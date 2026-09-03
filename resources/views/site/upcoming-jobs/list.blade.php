@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Upcoming Jobs</span></li>
    </ul>
@stop

@section('content')
    <style>
        /* Keep the detached Steel menu above the modal card and interactive. */
        #modal_edit > .bs-container {
            z-index: 10070 !important;
            pointer-events: auto;
        }

        .keybox {
            /*float: left;*/
            display: inline;
            height: 20px;
            width: 20px;
            margin: 0px 3px 5px 0px;
            cursor: pointer !important;
        }

        .keybox2 {
            display: inline;
            height: 20px;
            width: 20px;
            margin: 0px 3px 5px 0px;
            cursor: pointer !important;
        }

        .state-red {
            background-color: #e26a6a;
        }

        .state-orange {
            background-color: #FDD7B1;
        }

        .state-green {
            background-color: #36d7b7;
        }

        .state-grey {
            background-color: #e9edef;
        }

        .upcoming-jobs-table {
            width: 100% !important;
            min-width: 100%;
            table-layout: fixed;
            border: 0 !important;
            border-collapse: separate !important;
            border-spacing: 0;
        }

        .upcoming-jobs-table th,
        .upcoming-jobs-table td {
            border: 0 !important;
            border-right: 1px solid #e7ecf1 !important;
            border-bottom: 1px solid #e7ecf1 !important;
            padding-left: 5px !important;
            padding-right: 5px !important;
            overflow-wrap: anywhere;
            word-break: normal;
        }

        .upcoming-jobs-table tr > :first-child {
            border-left: 1px solid #e7ecf1 !important;
        }

        .upcoming-jobs-table thead tr:first-child th {
            border-top: 1px solid #e7ecf1 !important;
            height: 58px;
            vertical-align: middle !important;
            text-align: center;
        }

        .upcoming-jobs-table.view-all {
            width: 1545px !important;
            min-width: 1545px;
        }

        .upcoming-jobs-table.view-prestart {
            width: 1100px !important;
            min-width: 1100px;
        }

        .upcoming-jobs-table.view-workflow {
            width: 1105px !important;
            min-width: 1105px;
        }

        .upcoming-jobs-table col:nth-child(1) {
            width: 85px !important;
        }

        .upcoming-jobs-table col:nth-child(2) {
            width: 165px !important;
        }

        .upcoming-jobs-table col:nth-child(3) {
            width: 65px !important;
        }

        .upcoming-jobs-table th:nth-child(1),
        .upcoming-jobs-table td:nth-child(1) {
            width: 85px;
            min-width: 85px;
            max-width: 85px;
        }

        .upcoming-jobs-table th:nth-child(2),
        .upcoming-jobs-table td:nth-child(2) {
            width: 165px;
            min-width: 165px;
            max-width: 165px;
        }

        .upcoming-jobs-table th:nth-child(3),
        .upcoming-jobs-table td:nth-child(3) {
            width: 65px;
            min-width: 65px;
            max-width: 65px;
        }

        .upcoming-jobs-table.view-prestart col:nth-child(4) {
            width: 210px !important;
        }

        .upcoming-jobs-table.view-prestart col:nth-child(5) {
            width: 130px !important;
        }

        .upcoming-jobs-table.view-prestart col:nth-child(6) {
            width: 75px !important;
        }

        .upcoming-jobs-table.view-prestart col:nth-child(7) {
            width: 105px !important;
        }

        .upcoming-jobs-table.view-prestart col:nth-child(8) {
            width: 80px !important;
        }

        .upcoming-jobs-table.view-prestart col:nth-child(9) {
            width: 80px !important;
        }

        .upcoming-jobs-table.view-prestart col:nth-child(10) {
            width: 105px !important;
        }

        .upcoming-jobs-table.view-prestart col:nth-child(n+11),
        .upcoming-jobs-table.view-prestart th:nth-child(n+11),
        .upcoming-jobs-table.view-prestart td:nth-child(n+11) {
            display: none;
        }

        .upcoming-jobs-table.view-workflow col:nth-child(n+4):nth-child(-n+10),
        .upcoming-jobs-table.view-workflow th:nth-child(n+4):nth-child(-n+10),
        .upcoming-jobs-table.view-workflow td:nth-child(n+4):nth-child(-n+10) {
            display: none;
        }

        .upcoming-jobs-scroll {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .upcoming-jobs-table.view-all th:nth-child(1),
        .upcoming-jobs-table.view-all td:nth-child(1),
        .upcoming-jobs-table.view-all th:nth-child(2),
        .upcoming-jobs-table.view-all td:nth-child(2),
        .upcoming-jobs-table.view-all th:nth-child(3),
        .upcoming-jobs-table.view-all td:nth-child(3) {
            position: sticky;
            z-index: 4;
            background-color: #fff;
            background-clip: padding-box;
        }

        .upcoming-jobs-table.view-all th:nth-child(1),
        .upcoming-jobs-table.view-all td:nth-child(1) {
            left: 0;
        }

        .upcoming-jobs-table.view-all th:nth-child(2),
        .upcoming-jobs-table.view-all td:nth-child(2) {
            left: 85px;
        }

        .upcoming-jobs-table.view-all th:nth-child(3),
        .upcoming-jobs-table.view-all td:nth-child(3) {
            left: 250px;
        }

        .upcoming-jobs-table thead th:nth-child(-n+3) {
            background-color: #d9e4eb !important;
            color: #4b5863 !important;
        }

        .upcoming-jobs-table.view-all thead th:nth-child(-n+3) {
            z-index: 6;
        }

        .upcoming-jobs-table.view-all.table-striped > tbody > tr:nth-of-type(odd) > td:nth-child(-n+3) {
            background-color: #f9f9f9;
        }

        .upcoming-jobs-table.view-all.table-hover > tbody > tr:hover > td:nth-child(-n+3) {
            background-color: #f5f5f5;
        }

        .upcoming-jobs-view-bar {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 15px;
        }

        .upcoming-jobs-view-tabs {
            flex: 1 1 auto;
            margin-bottom: 0;
        }

        .upcoming-jobs-key {
            flex: 0 0 auto;
            padding-bottom: 11px;
            white-space: nowrap;
        }

        .upcoming-jobs-key .keybox {
            display: inline-block;
            margin: 0 6px 0 0;
            vertical-align: middle;
        }

        .upcoming-jobs-view-tabs a:focus {
            outline: none !important;
        }

        .upcoming-jobs-view-tabs .nav-tabs > li > a,
        .upcoming-jobs-view-tabs .nav-tabs > li > a:hover,
        .upcoming-jobs-view-tabs .nav-tabs > li > a:focus,
        .upcoming-jobs-view-tabs .nav-tabs > li.active > a,
        .upcoming-jobs-view-tabs .nav-tabs > li.active > a:hover,
        .upcoming-jobs-view-tabs .nav-tabs > li.active > a:focus {
            border: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }

        @media (max-width: 767px) {
            .upcoming-jobs-view-bar {
                display: block;
            }

            .upcoming-jobs-key {
                padding: 8px 0 0;
                text-align: right;
            }
        }

    </style>

    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze">Upcoming Jobs</span>
                        </div>
                        <div class="actions">
                            <a class="btn btn-circle green btn-outline btn-sm" href="/site/upcoming/compliance/pdf" data-original-title="PDF">PDF</a>

                            @if(Auth::user()->hasPermission2('del.site.upcoming.compliance'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/upcoming/compliance/settings/stages" data-original-title="Setting">Settings</a>
                            @endif
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="upcoming-jobs-view-bar" style="border-bottom: 1px solid #ddd">
                            <div class="tabbable-line upcoming-jobs-view-tabs" style="font-weight: bold">
                                <ul class="nav nav-tabs">
                                    <li class="active" data-upcoming-view-tab="all">
                                        <a href="#" data-upcoming-view="all">All columns</a>
                                    </li>
                                    <li data-upcoming-view-tab="prestart">
                                        <a href="#" data-upcoming-view="prestart">Pre-start</a>
                                    </li>
                                    <li data-upcoming-view-tab="workflow">
                                        <a href="#" data-upcoming-view="workflow">Workflow</a>
                                    </li>
                                </ul>
                            </div>

                            <div class="upcoming-jobs-key">
                                <span class="keybox state-orange" aria-hidden="true">&nbsp;</span>
                                Start Date Estimate
                            </div>
                        </div>

                        <div class="table-responsive upcoming-jobs-scroll" id="upcoming-jobs-scroll">
                            <table class="table table-striped table-bordered table-hover order-column upcoming-jobs-table view-all" id="table1">
                                <colgroup>
                                    <col style="width: 85px">
                                    <col style="width: 165px">
                                    <col style="width: 65px">
                                    <col style="width: 110px">
                                    <col style="width: 80px">
                                    <col style="width: 45px">
                                    <col style="width: 60px">
                                    <col style="width: 45px">
                                    <col style="width: 45px">
                                    <col style="width: 55px">
                                    <col style="width: 120px">
                                    <col style="width: 120px">
                                    <col style="width: 130px">
                                    <col style="width: 120px">
                                    <col style="width: 120px">
                                    <col style="width: 105px">
                                    <col style="width: 75px">
                                </colgroup>
                                <thead>
                                <tr class="mytable-header">
                                    <th>Start Date</th>
                                    <th>Site</th>
                                    <th>Super</th>
                                    <th>Company</th>
                                    <th>Deposit Paid</th>
                                    <th>ENG</th>
                                    <th>HBCF</th>
                                    <th>DC</th>
                                    <th>PC</th>
                                    <th>FC-EST</th>
                                    <th>CC</th>
                                    <th>FC Plans</th>
                                    <th>FC Structural</th>
                                    <th>CF-EST</th>
                                    <th>CF-ADM</th>
                                    <th>GAL</th>
                                    <th>STEEL</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($startdata as $row)
                                    <tr>
                                        <td style="{{ ($row['status'] != 1) ? 'background:#FDD7B1' : '' }}">
                                            {!! ($row['date']) ? $row['date'] : $row['date_est'] !!}
                                            @if ($row['tasks_before_start'] > 1)
                                                <span class="font-red">{{$row['tasks_before_start']}} tasks before START</span>
                                            @endif
                                        </td>
                                        <td id="sitename-{{$row['id']}}">{!! $row['name'] !!}</td>
                                        <td>
                                            @if ($row['status'] == 1 || Auth::user()->hasAnyRole2('mgt-general-manager|web-admin'))
                                                {!! $row['supervisor'] !!}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{!! $row['company'] !!}</td>
                                        <td style="{{ ($row['deposit_paid'] == '-') ? 'background:#FDD7B1' : '' }}">{!! $row['deposit_paid'] !!}</td>
                                        <td>{!! $row['eng'] !!}</td>
                                        <td>{!! $row['hbcf'] !!}</td>
                                        <td>{!! $row['design_con'] !!}</td>
                                        <td>{!! $row['project_mgr'] !!}</td>
                                        <td>{!! $row['estimator_fc'] !!}</td>
                                        <td class="hoverDiv editField" id="cc-{{$row['id']}}-td" style="{{ ($row['cc_stage']) ? 'background:'.$settings_colours['opt'][$row['cc_stage']] : '' }}">
                                            <div id="cc-{{$row['id']}}">{!! $row['cc'] !!}</div>
                                            <input type="hidden" id="cc-{{$row['id']}}-s" value="{!! $row['cc_stage'] !!}">
                                        </td>
                                        <td class="hoverDiv editField" id="fcp-{{$row['id']}}-td" style="{{ ($row['fc_plans_stage']) ? 'background:'.$settings_colours['opt'][$row['fc_plans_stage']] : '' }}">
                                            <div id="fcp-{{$row['id']}}">{!! $row['fc_plans'] !!}</div>
                                            <input type="hidden" id="fcp-{{$row['id']}}-s" value="{!! $row['fc_plans_stage'] !!}">
                                        </td>
                                        <td class="hoverDiv editField" id="fcs-{{$row['id']}}-td" style="{{ ($row['fc_struct_stage']) ? 'background:'.$settings_colours['opt'][$row['fc_struct_stage']] : '' }}">
                                            <div id="fcs-{{$row['id']}}">{!! $row['fc_struct'] !!}</div>
                                            <input type="hidden" id="fcs-{{$row['id']}}-s" value="{!! $row['fc_struct_stage'] !!}">
                                        </td>
                                        <td class="hoverDiv editField" id="cfest-{{$row['id']}}-td" style="{{ ($row['cf_est_stage']) ? 'background:'.$settings_colours['cfest'][$row['cf_est_stage']] : '' }}">
                                            <div id="cfest-{{$row['id']}}">{!! $row['cf_est'] !!}</div>
                                            <input type="hidden" id="cfest-{{$row['id']}}-s" value="{!! $row['cf_est_stage'] !!}">
                                        </td>
                                        <td class="hoverDiv editField" id="cfadm-{{$row['id']}}-td" style="{{ ($row['cf_adm_stage']) ? 'background:'.$settings_colours['cfadm'][$row['cf_adm_stage']] : '' }}">
                                            <div id="cfadm-{{$row['id']}}">{!! $row['cf_adm'] !!}</div>
                                            <input type="hidden" id="cfadm-{{$row['id']}}-s" value="{!! $row['cf_adm_stage'] !!}">
                                        </td>
                                        <td>{!! $row['gal'] !!}</td>
                                        <td class="hoverDiv editField" id="steel-{{$row['id']}}-td">
                                            <div id="steel-{{$row['id']}}">{!! $row['steel_name'] !!}</div>
                                            <input type="hidden" id="steel-{{$row['id']}}-s" value="{!! $row['steel_id'] !!}">
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-ui.bootstrap-modal id="modal_edit" title="Update upcoming job" max-width="900px">
        <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteUpcomingComplianceController::class, 'updateJob']) }}" class="horizontal-form" id="upcoming-job-update-form">
            @csrf
            <x-form.hidden name="site_id" value=""/>

            <h4 id="site_name" class="bold margin-top-0"></h4>

            {{-- Drafting --}}
            @if (Auth::user()->hasAnyRole2('dra-draftsperson|dra-drafting-manager|mgt-general-manager|web-admin'))
                {{-- CC --}}
                <div class="row">
                    <div class="col-md-4">
                        <x-form.select name="cc_stage" label="CC stage" :options="$settings_select['opt']" style="width:100%"/>
                    </div>
                    <div class="col-md-8">
                        <x-form.input name="cc" label="CC text"/>
                    </div>
                </div>
                {{-- FC Plans --}}
                <div class="row">
                    <div class="col-md-4">
                        <x-form.select name="fc_plans_stage" label="FC Plans stage" :options="$settings_select['opt']" style="width:100%"/>
                    </div>
                    <div class="col-md-8">
                        <x-form.input name="fc_plans" label="FC Plans text"/>
                    </div>
                </div>
                {{-- FC Struct --}}
                <div class="row">
                    <div class="col-md-4">
                        <x-form.select name="fc_struct_stage" label="FC Structural stage" :options="$settings_select['opt']" style="width:100%"/>
                    </div>
                    <div class="col-md-8">
                        <x-form.input name="fc_struct" label="FC Structural text"/>
                    </div>
                </div>
            @endif
            {{-- Estimators --}}
            {{-- Allow access to edit for below roles + users [1268 (Richard Hill) --}}
            @if (Auth::user()->hasAnyRole2('est-estimator|est-estimating-manager|mgt-general-manager|web-admin') || in_array(Auth::user()->id, [1268]))
                {{-- CF-EST --}}
                <div class="row">
                    <div class="col-md-4">
                        <x-form.select name="cf_est_stage" label="CF-EST stage" :options="$settings_select['cfest']" style="width:100%"/>
                    </div>
                    <div class="col-md-8">
                        <x-form.input name="cf_est" label="CF-EST text"/>
                    </div>
                </div>
            @endif

            {{-- Admins + Jayden (473)--}}
            @if (Auth::user()->hasAnyRole2('gen-administrator|gen-admin-manager|con-administrator|mgt-general-manager|web-admin') || in_array(Auth::user()->id, [473]))
                {{-- CF-ADM --}}
                <div class="row">
                    <div class="col-md-4">
                        <x-form.select name="cf_adm_stage" label="CF-ADM stage" :options="$settings_select['cfadm']" style="width:100%"/>
                    </div>
                    <div class="col-md-8">
                        <x-form.input name="cf_adm" label="CF-ADM text"/>
                    </div>
                </div>
            @endif
            {{-- Kirsty/Ross/Damien --}}
            @if (Auth::user()->hasAnyRole2('mgt-general-manager|web-admin') || in_array(Auth::user()->id, [2252]))
                {{-- CF-ADM --}}
                <div class="row">
                    <div class="col-md-6">
                        <x-form.select name="steel" label="Steel" :options="$steel_cats" style="width:100%" data-container="#modal_edit"/>
                    </div>
                </div>
            @endif
        </form>

        <x-slot name="footer">
            <button type="button" data-dismiss="modal" class="sws-modal-btn sws-modal-btn-secondary">Cancel</button>
            <button type="submit" form="upcoming-job-update-form" class="sws-modal-btn sws-modal-btn-primary">Save changes</button>
        </x-slot>
    </x-ui.bootstrap-modal>
@stop

@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script type="text/javascript">
        $(document).ready(function () {

            function setUpcomingJobsView(view) {
                if (["prestart", "workflow", "all"].indexOf(view) === -1)
                    view = "workflow";

                $("#table1")
                    .removeClass("view-prestart view-workflow view-all")
                    .addClass("view-" + view);

                $("[data-upcoming-view-tab]")
                    .removeClass("active")
                    .find("a")
                    .removeClass("font-green-haze");

                $("[data-upcoming-view-tab='" + view + "']")
                    .addClass("active")
                    .find("a")
                    .addClass("font-green-haze");
                $("#upcoming-jobs-scroll").scrollLeft(0);

            }

            setUpcomingJobsView("all");

            $("[data-upcoming-view]").click(function (e) {
                e.preventDefault();
                setUpcomingJobsView($(this).data("upcoming-view"));
            });

            $(".editField").click(function (e) {
                var event_id = e.target.id.split('-');
                var site_id = event_id[1];
                $("#site_id").val(site_id);

                $("#site_name").text($("#sitename-" + site_id).text());
                // CC
                $("#cc").val($("#cc-" + site_id).text());
                $("#cc_stage").val($("#cc-" + site_id + "-s").val()).change();
                // FC Plans
                $("#fc_plans").val($("#fcp-" + site_id).text());
                $("#fc_plans_stage").val($("#fcp-" + site_id + "-s").val()).change();
                // FC Structural
                $("#fc_struct").val($("#fcs-" + site_id).text());
                $("#fc_struct_stage").val($("#fcs-" + site_id + "-s").val()).change();
                // CF-EST
                $("#cf_est").val($("#cfest-" + site_id).text());
                $("#cf_est_stage").val($("#cfest-" + site_id + "-s").val()).change();
                // CF-ADM
                $("#cf_adm").val($("#cfadm-" + site_id).text());
                $("#cf_adm_stage").val($("#cfadm-" + site_id + "-s").val()).change();
                // STEEL
                $("#steel").val($("#steel-" + site_id + "-s").val()).change();

                $("#modal_edit").modal('show');
            });

            $("#cc_stage").change(function (e) {
                var default_text = @json($settings_text['opt']);

                // Only perform action if Modal is open - avoids updating fields when initial modal creation
                if ($('#modal_edit').hasClass('in')) {
                    if (!$("#cc_stage").val())
                        $('#cc').val('');
                    else if (default_text[$("#cc_stage").val()])
                        $('#cc').val(default_text[$("#cc_stage").val()]);
                }
            });

            $("#fc_plans_stage").change(function (e) {
                var default_text = @json($settings_text['opt']);

                // Only perform action if Modal is open - avoids updating fields when initial modal creation
                if ($('#modal_edit').hasClass('in')) {
                    if (!$("#fc_plans_stage").val())
                        $('#fc_plans').val('');
                    else if (default_text[$("#fc_plans_stage").val()])
                        $('#fc_plans').val(default_text[$("#fc_plans_stage").val()]);
                }
            });

            $("#fc_struct_stage").change(function (e) {
                var default_text = @json($settings_text['opt']);

                // Only perform action if Modal is open - avoids updating fields when initial modal creation
                if ($('#modal_edit').hasClass('in')) {
                    if (!$("#fc_struct_stage").val())
                        $('#fc_struct').val('');
                    else if (default_text[$("#fc_struct_stage").val()])
                        $('#fc_struct').val(default_text[$("#fc_struct_stage").val()]);
                }
            });

            $("#cf_est_stage").change(function (e) {
                var default_text = @json($settings_text['cfest']);

                // Only perform action if Modal is open - avoids updating fields when initial modal creation
                if ($('#modal_edit').hasClass('in')) {
                    if (!$("#cf_est_stage").val())
                        $('#cf_est').val('');
                    else if (default_text[$("#cf_est_stage").val()])
                        $('#cf_est').val(default_text[$("#cf_est_stage").val()]);
                }
            });

            $("#cf_adm_stage").change(function (e) {
                var default_text = @json($settings_text['cfadm']);

                // Only perform action if Modal is open - avoids updating fields when initial modal creation
                if ($('#modal_edit').hasClass('in')) {
                    if (!$("#cf_adm_stage").val())
                        $('#cf_adm').val('');
                    else if (default_text[$("#cf_adm_stage").val()])
                        $('#cf_adm').val(default_text[$("#cf_adm_stage").val()]);
                }
            });

        });


    </script>
@stop
