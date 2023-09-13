@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Contract Time Extensions</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze">Contract Time Extensions</span>
                        </div>
                        <div class="actions">
                            {{--}}<a class="btn btn-circle green btn-outline btn-sm" href="{{ $extension->attachmentUrl }}" target="_blank" data-original-title="PDF"><i class="fa fa-file-pdf-o"></i> PDF</a>--}}
                            <a class="btn btn-circle green btn-outline btn-sm" href="/site/extension/{{$extension->id}}/pdf" target="_blank" data-original-title="PDF"><i class="fa fa-file-pdf-o"></i> PDF</a>
                            <a class="btn btn-circle green btn-outline btn-sm" href="/site/extension/past" data-original-title="Past">Past Weeks</a>
                            @if(Auth::user()->hasPermission2('del.site.extension'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/extension/settings" data-original-title="Setting">Settings</a>
                            @endif
                        </div>
                    </div>
                    <div class="portlet-body">
                        <h3>Week of {{ $extension->date->format('d/m/Y') }}
                            @if (in_array(Auth::user()->id, [3, 108, 7, 325, 1359]))
                                {{-- Fudge, Kirstie, Gary, Michelle, Courtney --}}
                                <span class="pull-right" style="width: 200px">{!! Form::select('supervisor', ['0' => 'All supervisors'] + Auth::user()->company->reportsTo()->supervisorsSelect(), $supervisor_id, ['class' => 'form-control bs-select', 'id' => 'supervisor']) !!}</span>
                            @endif
                        </h3>
                        <table class="table table-striped table-bordered table-nohover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th style="width:25%">Site</th>
                                <th style="width:5%">Supervisor</th>
                                <th style="width:8%">Forecast Completion</th>
                                <th style="width:25%">Extend Reasons</th>
                                <th style="width:5%">Days</th>
                                <th>Extend Notes</th>
                                <th style="width:5%">Total Days</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $today = \Carbon\Carbon::now(); $completion_date = null; ?>
                            @foreach ($data as $row)
                                    <?php
                                    $completion_date = ($row['completion_date']) ? \Carbon\Carbon::createFromFormat('d/m/y H:i', $row['completion_date'] . ' 00:00') : null;
                                    $complete_date_sub2month = ($row['completion_date']) ? \Carbon\Carbon::createFromFormat('d/m/y H:i', $row['completion_date'] . ' 00:00')->subMonths(2) : null;

                                    $completion_bg = '';
                                    if ($completion_date && $completion_date->lte($today))
                                        $completion_bg = "background:#FDD7B1";
                                    else if ($row['completion_type'] == 'prac')
                                        $completion_bg = "background:#B5E2CD";
                                    ?>
                                @if ($supervisor_id == 0 || $supervisor_id == $row['super_id'])
                                    <tr>
                                        <td id="sitename-{{$row['id']}}">{{ $row['name'] }}</td>
                                        <td>{{ $row['super_initials'] }}</td>
                                        <td style="{{ $completion_bg }}">
                                            <span class="{{ ($completion_date && $completion_date->lte($today)) ? 'font-red' : '' }}">{{ $row['completion_date'] }}</span>
                                        </td>
                                        <td class="hoverDiv editField" id="reason-{{$row['id']}}-td">
                                            {{ $row['extend_reasons_text'] }}
                                            <input type="hidden" id="reason-{{$row['id']}}-array" name="reason-{{$row['id']}}-array[]" value="{{ $row['extend_reasons'] }}">
                                        </td>
                                        <td class="hoverDiv editField" id="days-{{$row['id']}}-td">
                                            <div id="days-{{$row['id']}}">{!! $row['days'] !!}</div>
                                            <input type="hidden" id="days-{{$row['days']}}-s" value="{!! $row['days'] !!}"></td>
                                        <td class="hoverDiv editField" id="note-{{$row['id']}}-td">
                                            <div id="note-{{$row['id']}}">{!! nl2br($row['notes']) !!}</div>
                                            <input type="hidden" id="note-{{$row['id']}}-s" value="{!! $row['notes'] !!}">
                                        </td>
                                        @if ($row['total_days'])
                                            <td class="hoverDiv toggleTotalDays" id="total-{{$row['id']}}">
                                                {{ $row['total_days'] }}
                                            </td>
                                        @else
                                            <td>-</td>
                                        @endif
                                    </tr>
                                    <tr id="extrainfo-{{$row['id']}}" style="display: none">
                                        <td colspan="7" style="background: #333; color: #fff">
                                            <b>Summary of existing Extensions</b>
                                            <div style="background: #fff; color:#636b6f;  padding: 20px">
                                                {!! nl2br($row['past_extentions']) !!}
                                            </div>
                                        </td>
                                    </tr>
                                    <tr style="display: none">
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>

                        <div class="row">
                            <div class="col-md-12">
                                <h5><b>CONTRACT TIME EXTENSIONS ELECTRONIC SIGN-OFF</b></h5>
                                <p>The above contract time extensions have been verified by the construction manager.</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-3 text-right">Construction Manager:</div>
                            <div class="col-sm-9">
                                @if ($extension->approved_by)
                                    {!! \App\User::find($extension->approved_by)->full_name !!}, &nbsp;{{ $extension->approved_at->format('d/m/Y') }}
                                @elseif ($extension->sites->count() != $extension->sitesCompleted()->count())
                                    <span class="font-grey-silver">Waiting for ({{ ($extension->sites->count()  - $extension->sitesCompleted()->count()) }}) sites to be completed</span>
                                @elseif (Auth::user()->hasAnyRole2('con-construction-manager|con-administrator|web-admin|mgt-general-manager'))
                                    <button class="btn blue btn-xs btn-outline sbold uppercase margin-bottom signoff">Sign Off</button>
                                @else
                                    <span class="font-red">Pending</span>
                                @endif
                            </div>
                        </div>
                        <br>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END PAGE CONTENT INNER -->

    <!--  Edit Modal -->
    <div id="modal_edit" class="modal fade bs-modal-lg" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title" id="site_name"></h4>
                </div>
                <div class="modal-body">
                    {!! Form::model('upcoming', ['method' => 'POST', 'action' => ['Site\SiteExtensionController@updateJob'], 'class' => 'horizontal-form', 'files' => true, 'id'=>'talk_form']) !!}
                    <input type="hidden" name="site_id" id="site_id" value="">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('reasons', 'Extend Reasons', ['class' => 'control-label']) !!}
                                {!! Form::select('reasons', $extend_reasons, null, ['class' => 'form-control select2', 'id' => 'reasons', 'name' => 'reasons[]', 'multiple', 'width' => '100%']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('days', 'Days', ['class' => 'control-label', 'id' => 'days_label']) !!}
                                <input type="text" class="form-control" value="{{ old('days') }}" id="days" name="days" onkeypress="return isNumber(event)"/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('extension_notes', 'Extend notes', ['class' => 'control-label', 'id' => 'extension_notes_label']) !!}
                                {!! Form::textarea('extension_notes', null, ['class' => 'form-control', 'rows' => 5, 'id' => 'extension_notes']) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn dark btn-outline">Close</button>
                    <button type="submit" class="btn green" id="savenote">Save</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@stop

@section('page-level-plugins-head')
@stop

@section('page-level-styles-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script type="text/javascript">
        $(document).ready(function () {
            /* Select2 */
            $("#reasons").select2({placeholder: "Select one or more", width: '100%'});

            $(".toggleTotalDays").click(function (e) {
                var event_id = e.target.id.split('-');
                var site_id = event_id[1];

                $("#extrainfo-"+site_id).toggle();
            });

            $(".editField").click(function (e) {
                var event_id = e.target.id.split('-');
                var site_id = event_id[1];
                $("#site_id").val(site_id);
                $("#site_name").text($("#sitename-" + site_id).text());
                $("#days").val($("#days-" + site_id).text());

                // Extension reason + notes
                $("#extension_notes").val($("#note-" + site_id).text());
                var reason_array_str = $("#reason-" + site_id + "-array").val();
                var reason_array = reason_array_str.split(',');

                $("#reasons").val(reason_array);
                $("#reasons").trigger('change');

                $("#modal_edit").modal('show');
            });

            $(".signoff").click(function (e) {
                e.preventDefault();
                window.location.href = "/site/extension/{{$extension->id}}/signoff";
            });

            $("#supervisor").change(function (e) {
                e.preventDefault();
                window.location.href = "/site/extension/{{$extension->id}}/" + $("#supervisor").val();
            });

            $("#reasons").change(function (e) {
                e.preventDefault();
                validateForm();
            });

            $("#days").keyup(function (e) {
                e.preventDefault();
                validateForm();
            });

            $("#extension_notes").keyup(function (e) {
                e.preventDefault();
                validateForm();
            });

            function validateForm() {
                $("#savenote").show();
                $("#days_label").text('Days');
                $("#extension_notes_label").text('Extend notes');

                if ($("#reasons option:selected").length) {
                    if ($("#reasons").val().includes('1')) {
                        // NA selected so clear all other options and leave NA only
                        $("#reasons").val(['1']).trigger('change.select2'); // update select2 val without triggering change
                        $("#days").hide();
                        $("#days_label").hide();
                        $("#extension_notes").hide();
                        $("#extension_notes_label").hide();
                    } else {
                        $("#days").show();
                        $("#days_label").show();
                        $("#extension_notes").show();
                        $("#extension_notes_label").show();
                        // Enforce Days + Notes are required
                        $("#days_label").html("Days <span class='font-red'>(required)</span>");
                        let arr = ['2', '4', '5', '6', '7', '8', '9', '10'];  // all except Public Holidays
                        let required = false;
                        if (containsAny($("#reasons").val(), arr)) {
                            $("#extension_notes_label").html("Extent notes <span class='font-red'>(required)</span>");
                            required = true;
                        }
                        if (!$("#days").val() || (required && !$("#extension_notes").val()))
                            $("#savenote").hide();
                    }
                } else {
                    if ($("#days").val())
                        $("#savenote").hide();
                }
            }


        });

        function containsAny(source, target) {
            var result = source.filter(function (item) {
                return target.indexOf(item) > -1
            });
            return (result.length > 0);
        }

        function isNumber(evt) {
            evt = (evt) ? evt : window.event;
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if ((charCode > 31 && charCode < 48) || charCode > 57) {
                return false;
            }
            return true;
        }


    </script>
@stop