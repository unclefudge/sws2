@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('view.weekly.planner'))
            <li><a href="/planner/weekly">Weekly Planner</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Upcoming Projects</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze">Upcoming Projects</span>
                        </div>
                        <div class="actions">
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header" style="height: 150px">
                                <th> Site Name</th>
                                <th width="12%"> Start Estimate</th>
                                <th> Super</th>
                                <th>
                                    <div style="writing-mode: vertical-lr;">Council Approved</div>
                                </th>
                                <th>
                                    <div style="writing-mode: vertical-lr;">Contract Sent</div>
                                </th>
                                <th>
                                    <div style="writing-mode: vertical-lr;">Contract Signed</div>
                                </th>
                                <th>
                                    <div style="writing-mode: vertical-lr;">Deposit Paid</div>
                                </th>
                                <th>
                                    <div style="writing-mode: vertical-lr;">Construction Cert.</div>
                                </th>
                                <th>
                                    <div style="writing-mode: vertical-lr;">OSD</div>
                                </th>
                                <th>
                                    <div style="writing-mode: vertical-lr;">SW</div>
                                </th>
                                <th>
                                    <div style="writing-mode: vertical-lr;">Electrical Report</div>
                                </th>
                                <th>
                                    <div style="writing-mode: vertical-lr;">Plumbing Report</div>
                                </th>
                                <th> Electrical Works<br>Plumbing Works</th>
                                <th></th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach($site_list as $site_id)
                                    <?php $site = \App\Models\Site\Site::find($site_id) ?>
                                <tr>
                                    <td>
                                        @if (Auth::user()->hasPermission2('edit.preconstruction.planner'))
                                            <a onclick="go2preconstruction({{ $site_id }})">{{ $site->name }}</a>
                                        @else
                                            {{ $site->name }}
                                        @endif
                                    </td>
                                    <td>
                                        @if (Auth::user()->hasPermission2('edit.preconstruction.planner'))
                                            <div class="input-group date date-picker">
                                                {!! Form::text('jobstart_estimate', ($site->jobstart_estimate) ? $site->jobstart_estimate->format('d/m/Y') : '', ['class' => 'form-control form-control-inline startEst', 'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy" , 'id' => "j$site->id"]) !!}
                                                <span class="input-group-btn">
                                                <button class="btn default date-set" type="button"
                                                        style="padding: 0"></button>
                                            </span>
                                            </div>
                                        @else
                                            {!! ($site->jobstart_estimate) ? $site->jobstart_estimate->format('d/m/Y') : '' !!}
                                        @endif
                                    </td>
                                    <td>
                                        @if (Auth::user()->hasPermission2('edit.preconstruction.planner'))
                                            <select id="s{{ $site->id }}" class="form-control bs-select superSelect"
                                                    name="supervisor" title="Select supervisor">
                                                @foreach(Auth::user()->company->supervisorsSelect('prompt', 'short') as $id => $name)
                                                    <option value="{{ $id }}"
                                                            @if ($site->supervisor_id && $id == $site->supervisor_id) selected @endif>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            {!! $site->supervisorInitials !!}
                                        @endif
                                    </td>
                                    <td>{!! ($site->council_approval) ? 'Y' : '' !!}</td>
                                    <td>{!! ($site->contract_sent) ? 'Y' : '' !!}</td>
                                    <td>{!! ($site->contract_signed) ? 'Y' : '' !!}</td>
                                    <td>{!! ($site->deposit_paid) ? 'Y' : '' !!}</td>
                                    <td>{!! ($site->construction) ? 'Y' : '' !!}</td>
                                    <td>{!! $site->osd !!}</td>
                                    <td>{!! $site->sw !!}</td>
                                    <td>{{ $site->inspection_electrical->first() ? 'Y' : '' }}</td>
                                    <td>{{ $site->inspection_plumbing->first() ? 'Y' : '' }}</td>
                                    <td>
                                        @if (Auth::user()->hasPermission2('edit.preconstruction.planner'))
                                            <select id="e{{ $site->id }}" class="form-control bs-select eworksSelect" name="e{{ $site->id }}">
                                                <option value="null">Select electrician</option>
                                                @foreach(Auth::user()->company->tradeSelect(4, 'compact') as $id => $name)
                                                    <option value="{{ $id }}"
                                                            @if ($site->eworks && $id == $site->eworks) selected @endif>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                            <select id="p{{ $site->id }}" class="form-control bs-select pworksSelect" name="p{{ $site->id }}">
                                                <option value="null">Select plumber</option>
                                                @foreach(Auth::user()->company->tradeSelect(8, 'compact') as $id => $name)
                                                    <option value="{{ $id }}"
                                                            @if ($site->pworks && $id == $site->pworks) selected @endif>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            {!! $site->supervisorInitials !!}
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn dark btn-xs margin-bottom btn-expand " data-siteid="{{$site->id}}"><i class="fa fa-plus"></i></button>
                                    </td>
                                </tr>
                                <tr id="info-{{$site->id}}" style="display: none">
                                    <td colspan="15" style="width: 100%; background: #333; color: #fff">
                                        {{ $site->name }}
                                        <div class="row" style="background: #fff; color:#636b6f;  padding: 20px">
                                            <div class="col-md-2">
                                                Council Approval:<br>
                                                Contracts Sent:<br>
                                                Contracts Signed:<br>
                                                Deposit Paid:<br>
                                                Construction Certificate:<br>
                                            </div>
                                            <div class="col-md-2">
                                                {!! ($site->council_approval) ? $site->council_approval->format('d/m/Y') : '-' !!}<br>
                                                {!! ($site->contract_sent) ? $site->contract_sent->format('d/m/Y') : '-' !!}<br>
                                                {!! ($site->contract_signed) ? $site->contract_signed->format('d/m/Y') : '-' !!}<br>
                                                {!! ($site->deposit_paid) ? $site->deposit_paid->format('d/m/Y') : '-' !!}<br>
                                                {!! ($site->construction) ? 'Y' : '-' !!}<br>
                                            </div>
                                            <div class="col-md-2">
                                                Electrical Report:<br>
                                                Plumbing Report:<br>
                                            </div>
                                            <div class="col-md-4">
                                                @php
                                                    $e_report = $site->inspection_electrical->first();
                                                    $e_inspected = ($e_report && $e_report->inspected_at) ?  "$e_report->inspected_name (".$e_report->inspected_at->format('d/m/Y').")" : '';
                                                    $p_report = $site->inspection_plumbing->first();
                                                    $p_inspected = ($p_report && $p_report->inspected_at) ?  "$p_report->inspected_name (".$p_report->inspected_at->format('d/m/Y').")" : '';
                                                @endphp
                                                {{ $e_inspected }}<br>
                                                {{ $p_inspected }}<br>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>

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
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet"
          type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet"
          type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"
            type="text/javascript"></script>
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js"
            type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script type="text/javascript">

        $(document).ready(function () {
            $('.superSelect').change(function () {
                var site_id = this.id.substring(1);
                //alert('SiteID:' + site_id + '  SuperID:' + this.value);
                if (site_id) {
                    $.ajax({
                        url: '/site/' + site_id + '/supervisor/' + this.value,
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            console.log('updated supervisor for Site:')
                        },
                    })
                }
            });
            /*$('.startEst').change(function () {
                //alert(this.value + ' : ' + this.id);
                var site_id = this.id.substring(1);
                var start = this.value.replace(/\s/g, "");
                console.log(start);
                var date_formated = '';

                if (start) {
                    var date = this.value.split('/');
                    date_formated = date[2] + '-' + date[1] + '-' + date[0];
                } else {
                    date_formated = 'clear';
                    $('#j'.site_id).datepicker('setDate', null);
                    $('#j'.site_id).datepicker('clearDates');
                    console.log('cleared');
                }
                console.log(date_formated);


                //alert(date_formated);
                $.ajax({
                    url: '/site/' + site_id + '/jobstart_estimate/' + date_formated,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        console.log('updated supervisor for Site:')
                    },
                })
            });*/

            $('.startEst').datepicker({
                autoclose: true,
                clearBtn: true,
                format: 'dd/mm/yyyy',
            }).on("changeDate clearDate", function (e) {
                var site_id = this.id.substring(1);

                if (e.type == 'clearDate') {
                    this.clearBtnClicked = true;
                    console.log('clear date');
                    date_formated = 'clear';
                    console.log("cc:" + date_formated);
                    $.ajax({
                        url: '/site/' + site_id + '/jobstart_estimate/' + date_formated,
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            console.log('updated supervisor for Site:')
                        },
                    })
                } else {
                    if (this.clearBtnClicked) {
                        this.clearBtnClicked = false;
                    } else {
                        console.log('update date');
                        var date = this.value.split('/');
                        date_formated = date[2] + '-' + date[1] + '-' + date[0];
                    }
                    console.log("aa:" + date_formated);
                    $.ajax({
                        url: '/site/' + site_id + '/jobstart_estimate/' + date_formated,
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            console.log('updated supervisor for Site:')
                        },
                    })
                }
            });

            $('.eworksSelect').change(function () {
                //alert(this.id);
                var site_id = this.id.substring(1);
                //alert(site_id)
                var company = this.value;
                $.ajax({
                    url: '/site/' + site_id + '/eworks/' + company,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        console.log('updated eworks for Site:')
                    },
                })
            });

            $('.pworksSelect').change(function () {
                var site_id = this.id.substring(1);
                var company = this.value;
                $.ajax({
                    url: '/site/' + site_id + '/pworks/' + company,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        console.log('updated pworks for Site:')
                    },
                })
            });

            $('.btn-expand').click(function (e) {
                e.preventDefault();
                var id = $(this).data('siteid');
                $("#info-" + id).toggle();
            });
        });

        function go2preconstruction(site_id) {
            var postData = {site_start: 'start', _token: $('meta[name=token]').attr('value')};
            var postFormStr = "<form method='POST' action='/planner/preconstruction/" + site_id + "'>\n";

            for (var key in postData) {
                if (postData.hasOwnProperty(key))
                    postFormStr += "<input type='hidden' name='" + key + "' value='" + postData[key] + "'></input>";
            }

            postFormStr += "</form>";
            var formElement = $(postFormStr);

            $('body').append(formElement);
            $(formElement).submit();
        }

        $('.date-picker').datepicker({
            autoclose: true,
            clearBtn: true,
            format: 'dd/mm/yyyy',
        });
    </script>
@stop