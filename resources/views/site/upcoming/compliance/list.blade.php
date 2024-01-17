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
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/upcoming/compliance/settings" data-original-title="Setting">Settings</a>
                            @endif
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th width="7%">Start Date</th>
                                <th width="20%">Site</th>
                                <th width="5%">Super</th>
                                <th width="5%">Company</th>
                                <th width="7%">Deposit Paid</th>
                                <th width="5%">ENG</th>
                                <th width="7%">HBCF</th>
                                <th width="5%">DC</th>
                                <th width="5%">FC-EST</th>
                                <th>CC</th>
                                <th>FC Plans</th>
                                <th>FC Structural</th>
                                <th>CF-EST</th>
                                <th>CF-ADM</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($startdata as $row)
                                <tr>
                                    <td style="{{ ($row['date_est']) ? 'background:#FDD7B1' : '' }}">{!! ($row['date']) ? $row['date'] : $row['date_est'] !!}</td>
                                    <td id="sitename-{{$row['id']}}">{!! $row['name'] !!}</td>
                                    <td>{!! $row['supervisor'] !!}</td>
                                    <td>{!! $row['company'] !!}</td>
                                    <td style="{{ ($row['deposit_paid'] == '-') ? 'background:#FDD7B1' : '' }}">{!! $row['deposit_paid'] !!}</td>
                                    <td>{!! $row['eng'] !!}</td>
                                    <td>{!! $row['hbcf'] !!}</td>
                                    <td>{!! $row['design_con'] !!}</td>
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
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END PAGE CONTENT INNER -->

    <!-- Edit Modal -->
    <div id="modal_edit" class="modal fade bs-modal-lg" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title" id="site_name"></h4>
                </div>
                <div class="modal-body">
                    {!! Form::model('upcoming', ['method' => 'POST', 'action' => ['Site\SiteUpcomingComplianceController@updateJob'], 'class' => 'horizontal-form', 'files' => true, 'id'=>'talk_form']) !!}
                    <input type="hidden" name="site_id" id="site_id" value="">

                    {{-- Drafting --}}
                    @if (Auth::user()->hasAnyRole2('dra-draftsperson|dra-drafting-manager|mgt-general-manager|web-admin'))
                        {{-- CC --}}
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('cc_stage', 'Stage', ['class' => 'control-label']) !!}
                                    {!! Form::select('cc_stage', $settings_select['opt'], null, ['class' => 'form-control bs-select', 'id' => 'cc_stage', 'width' => '100%']) !!}
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group">
                                    {!! Form::label('cc', 'CC', ['class' => 'control-label']) !!}
                                    {!! Form::text('cc', null, ['class' => 'form-control', 'id' => 'cc']) !!}
                                </div>
                            </div>
                        </div>
                        {{-- FC Plans --}}
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('fc_plans_stage', 'Stage', ['class' => 'control-label']) !!}
                                    {!! Form::select('fc_plans_stage', $settings_select['opt'], null, ['class' => 'form-control bs-select', 'id' => 'fc_plans_stage', 'width' => '100%']) !!}
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group">
                                    {!! Form::label('fc_plans', 'FC Plans', ['class' => 'control-label']) !!}
                                    {!! Form::text('fc_plans', null, ['class' => 'form-control', 'id' => 'fc_plans']) !!}
                                </div>
                            </div>
                        </div>
                        {{-- FC Struct --}}
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('fc_struct_stage', 'Stage', ['class' => 'control-label']) !!}
                                    {!! Form::select('fc_struct_stage', $settings_select['opt'], null, ['class' => 'form-control bs-select', 'id' => 'fc_struct_stage', 'width' => '100%']) !!}
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group">
                                    {!! Form::label('fc_struct', 'FC Structural', ['class' => 'control-label']) !!}
                                    {!! Form::text('fc_struct', null, ['class' => 'form-control', 'id' => 'fc_struct']) !!}
                                </div>
                            </div>
                        </div>
                    @endif
                    {{-- Estimators --}}
                    @if (Auth::user()->hasAnyRole2('est-estimator|est-estimating-manager|mgt-general-manager|web-admin'))
                        {{-- CF-EST --}}
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('cf_est_stage', 'Stage', ['class' => 'control-label']) !!}
                                    {!! Form::select('cf_est_stage', $settings_select['cfest'], null, ['class' => 'form-control bs-select', 'id' => 'cf_est_stage', 'width' => '100%']) !!}
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group">
                                    {!! Form::label('cf_est', 'CF-EST', ['class' => 'control-label']) !!}
                                    {!! Form::text('cf_est', null, ['class' => 'form-control', 'id' => 'cf_est']) !!}
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Admins --}}
                    @if (Auth::user()->hasAnyRole2('gen-administrator|gen-admin-manager|con-administrator|mgt-general-manager|web-admin'))
                        {{-- CF-ADM --}}
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('cf_adm_stage', 'Stage', ['class' => 'control-label']) !!}
                                    {!! Form::select('cf_adm_stage', $settings_select['cfadm'], null, ['class' => 'form-control bs-select', 'id' => 'cf_adm_stage', 'width' => '100%']) !!}
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group">
                                    {!! Form::label('cf_adm', 'CF-ADM', ['class' => 'control-label']) !!}
                                    {!! Form::text('cf_adm', null, ['class' => 'form-control', 'id' => 'cf_adm']) !!}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn dark btn-outline">Close</button>
                    <button type="submit" class="btn green">Save</button>
                </div>
                {!! Form::close() !!}
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