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
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th style="width:8%">Start Date</th>
                                <th style="width:20%">Site</th>
                                <th style="width:5%">Super</th>
                                <th style="width:5%">Company</th>
                                <th style="width:7%">Deposit Paid</th>
                                <th style="width:5%">ENG</th>
                                <th style="width:7%">HBCF</th>
                                <th style="width:5%">DC</th>
                                <th style="width:5%">PC</th>
                                <th style="width:5%">FC-EST</th>
                                <th>CC</th>
                                <th>FC Plans</th>
                                <th>FC Structural</th>
                                <th>CF-EST</th>
                                <th>CF-ADM</th>
                                <th style="width:5%">GAL</th>
                                <th style="width:5%">STEEL</th>
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
                                    <td class="hoverDiv editField" id="cfest-{{$row['id']}}-td" style="{{ ($row['cf_adm_stage']) ? 'background:'.$settings_colours['cfadm'][$row['cf_adm_stage']] : '' }}">
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

                        <div class="row">
                            <div class="col-md-12">
                                <span class="keybox state-orange" style="margin-left: 20px"> &nbsp; &nbsp; </span> &nbsp; Start Date Estimate
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="modal_edit" class="modal fade bs-modal-lg" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title" id="site_name"></h4>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteUpcomingComplianceController::class, 'updateJob']) }}" class="horizontal-form" enctype="multipart/form-data" id="talk_form">
                    @csrf
                    <x-form.hidden name="site_id" value=""/>

                    {{-- Drafting --}}
                    @if (Auth::user()->hasAnyRole2('dra-draftsperson|dra-drafting-manager|mgt-general-manager|web-admin'))
                        {{-- CC --}}
                        <div class="row">
                            <div class="col-md-3">
                                <x-form.select name="cc_stage" label="Stage" :options="$settings_select['opt']" style="width:100%"/>
                            </div>
                            <div class="col-md-9">
                                <x-form.input name="cc" label="CC"/>
                            </div>
                        </div>
                        {{-- FC Plans --}}
                        <div class="row">
                            <div class="col-md-3">
                                <x-form.select name="fc_plans_stage" label="Stage" :options="$settings_select['opt']" style="width:100%"/>
                            </div>
                            <div class="col-md-9">
                                <x-form.input name="fc_plans" label="FC Plans"/>
                            </div>
                        </div>
                        {{-- FC Struct --}}
                        <div class="row">
                            <div class="col-md-3">
                                <x-form.select name="fc_struct_stage" label="Stage" :options="$settings_select['opt']" style="width:100%"/>
                            </div>
                            <div class="col-md-9">
                                <x-form.input name="fc_struct" label="FC Structural"/>
                            </div>
                        </div>
                    @endif
                    {{-- Estimators --}}
                    {{-- Allow access to edit for below roles + users [1268 (Richard Hill) --}}
                    @if (Auth::user()->hasAnyRole2('est-estimator|est-estimating-manager|mgt-general-manager|web-admin') || in_array(Auth::user()->id, [1268]))
                        {{-- CF-EST --}}
                        <div class="row">
                            <div class="col-md-3">
                                <x-form.select name="cf_est_stage" label="Stage" :options="$settings_select['cfest']" style="width:100%"/>
                            </div>
                            <div class="col-md-9">
                                <x-form.input name="cf_est" label="CF-EST"/>
                            </div>
                        </div>
                    @endif

                    {{-- Admins + Jayden (473)--}}
                    @if (Auth::user()->hasAnyRole2('gen-administrator|gen-admin-manager|con-administrator|mgt-general-manager|web-admin') || in_array(Auth::user()->id, [473]))
                        {{-- CF-ADM --}}
                        <div class="row">
                            <div class="col-md-3">
                                <x-form.select name="cf_adm_stage" label="Stage" :options="$settings_select['cfadm']" style="width:100%"/>
                            </div>
                            <div class="col-md-9">
                                <x-form.input name="cf_adm" label="CF-ADM"/>
                            </div>
                        </div>
                    @endif
                    {{-- Kirsty/Ross/Damien --}}
                    @if (Auth::user()->hasAnyRole2('mgt-general-manager|web-admin') || in_array(Auth::user()->id, [2252]))
                        {{-- CF-ADM --}}
                        <div class="row">
                            <div class="col-md-6">
                                <x-form.select name="steel" label="Steel" :options="$steel_cats" style="width:100%"/>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn dark btn-outline">Close</button>
                    <button type="submit" class="btn green">Save</button>
                </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script type="text/javascript">
        $(document).ready(function () {

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