@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('view.weekly.planner'))
            <li><a href="/planner/weekly">Weekly Planner</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Forecast Planner</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze">Forecast Planner</span>
                        </div>
                        <div class="actions">
                            <img src="/img/col-blue-C5D1EC.png"> Prac Completion &nbsp; &nbsp; <img src="/img/col-green-B5E2CD.png"> Active &nbsp; &nbsp; <img src="/img/col-orange-FDD7B1.png"> Job Start
                        </div>
                    </div>
                    <div class="portlet-body">
                        {{--}}<select id="super_id" class="form-control bs-select" name="supervisor" title="Select supervisor">
                            @foreach(Auth::user()->company->supervisorsSelect() as $id => $name)
                                <option value="{{ $id }}" @if ($site->supervisors->first() && $id == $site->supervisors->first()->id) selected @endif>{{ $name }}</option>
                            @endforeach
                        </select>--}}
                        <div class="row">
                            <div class="col-md-4">
                                {!! Form::select('supervisor_id', ['' => 'All'] + Auth::user()->company->supervisorsSelect(), $supervisor_id, ['class' => 'form-control bs-select', 'id' => 'supervisor_id',]) !!}
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-striped table-bordered order-column" id="table_list">
                                    <tbody>
                                    @foreach($data as $row)
                                        @if (!$supervisor_id || ($supervisor_id && $supervisor_id == $row['supervisor_id']))
                                            @if ($row['site_id'])
                                                <tr>
                                                    <td width="5%">
                                                        <div class="text-center"><a href="/planner/site/{{ $row['site_id'] }}"><i class="fa fa-search"></i></a></div>
                                                    </td>
                                                    <td>{{ $row['site_name'] }} </td>
                                                    {{--<td>{{ $row['super_initials'] }}</td>--}}

                                                    {{-- Loop through for each month --}}
                                                    @for ($i = 0; $i < 6; $i++)
                                                        @if ($row["m$i"] == 'Active')
                                                            <td width="7%" style="background: #B5E2CD;"></td>
                                                        @elseif ($row["m$i"] == 'START')
                                                            <td width="7%" style="background: #FDD7B1;">
                                                                <?php $split = explode(' ', $row['job_start_day']); ?>
                                                                {{ $split[0] }}<sup> {{ $split[1] }}</sup>
                                                            </td>
                                                        @elseif ($row["m$i"] == 'PRAC')
                                                            <td width="7%" style="background: #C5D1EC;">
                                                                <?php $split = explode(' ', $row['prac_complete_day']); ?>
                                                                {{ $split[0] }}<sup> {{ $split[1] }}</sup>
                                                            </td>
                                                        @else
                                                            <td width="7%"></td>
                                                        @endif
                                                    @endfor
                                                </tr>
                                            @elseif ($row['site_name'] == 'Totals')
                                                <tr style="background: #eee">
                                                    <td colspan="2"><span class="pull-right"><b>{{ $row['site_name'] }}</b></span></td>
                                                    <th width="7%" class="text-center"> {{ $row["m0"] }}</th>
                                                    <th width="7%" class="text-center"> {{ $row["m1"] }}</th>
                                                    <th width="7%" class="text-center"> {{ $row["m2"] }}</th>
                                                    <th width="7%" class="text-center"> {{ $row["m3"] }}</th>
                                                    <th width="7%" class="text-center"> {{ $row["m4"] }}</th>
                                                    <th width="7%" class="text-center"> {{ $row["m5"] }}</th>
                                                </tr>
                                                <tr>
                                                    <td colspan="8"></td>
                                                </tr>
                                            @else
                                                <tr style="background: #f0f6fa">
                                                    <td colspan="2"><b> &nbsp; {{ $row['site_name'] }}</b></td>
                                                    <th width="7%"> {{ $months[0] }}</th>
                                                    <th width="7%"> {{ $months[1] }}</th>
                                                    <th width="7%"> {{ $months[2] }}</th>
                                                    <th width="7%"> {{ $months[3] }}</th>
                                                    <th width="7%"> {{ $months[4] }}</th>
                                                    <th width="7%"> {{ $months[5] }}</th>
                                                </tr>
                                            @endif
                                        @endif
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
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
<script type="text/javascript">

    $.ajaxSetup({
        headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
    });

    $(document).ready(function () {

        $('#supervisor_id').change(function () {
            var params = {supervisor_id: $('#supervisor_id').val(), _token: $('meta[name=token]').attr('value')}
            postAndRedirect('/planner/forecast', params);
        });


    });

    // Post data to url via POST method
    function postAndRedirect(url, postData) {
        var postFormStr = "<form method='POST' action='" + url + "'>\n";

        for (var key in postData) {
            if (postData.hasOwnProperty(key))
                postFormStr += "<input type='hidden' name='" + key + "' value='" + postData[key] + "'></input>";
        }

        postFormStr += "</form>";
        var formElement = $(postFormStr);

        $('body').append(formElement);
        $(formElement).submit();
    }

</script>
@stop