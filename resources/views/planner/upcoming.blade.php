@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('view.weekly.planner'))
            <li><a href="/planner/weekly">Weekly Planner</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Up and Coming Projects</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze">Up and Coming Projects</span>
                        </div>
                        <div class="actions">
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th width="5%"> Site</th>
                                <th> Name</th>
                                <th> Job Start</th>
                                <th> Supervisor</th>
                                <th> Council Approval</th>
                                <th> Contracts Sent</th>
                                <th> Contracts Signed</th>
                                <th> Deposit Paid</th>
                                <th> Construction Certificate</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach($site_list as $site_id)
                                <?php $site = \App\Models\Site\Site::find($site_id) ?>
                                <tr>
                                    <td>
                                        <div class="text-center"><a href="/planner/preconstruction/{{ $site->id }}"><i class="fa fa-search"></i></a></div>
                                    </td>
                                    <td>{{ $site->code }}</td>
                                    <td>{{ $site->name }}</td>
                                    <td>{{ ($site->JobStart) ? $site->JobStart->format('d/m/Y') : '' }}</td>
                                    <td>
                                       {{--}} {!! Form::select('supervisors', Auth::user()->company->supervisorsSelect(),
                            $site->supervisors->pluck('id')->toArray(), ['class' => 'form-control bs-select', 'name' => 'supervisors[]', 'title' => 'Select one or more supervisors', 'multiple']) !!} --}}
                                        <select id="{{ $site->id }}" class="form-control bs-select" name="supervisor" title="Select supervisor">
                                            @foreach(Auth::user()->company->supervisorsSelect() as $id => $name)
                                                <option value="{{ $id }}" @if ($site->supervisors->first() && $id == $site->supervisors->first()->id) selected @endif>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>{{ ($site->JobFirstTaskOfType(576)) ? $site->JobFirstTaskOfType(576)->format('d/m/Y') : ''  }}</td>
                                    <td>
                                        {!! ($site->contract_sent) ? $site->contract_sent->format('d/m/Y') : '' !!}
                                    </td>
                                    <td>
                                        {!! ($site->contract_signed) ? $site->contract_signed->format('d/m/Y') : '' !!}
                                    </td>
                                    <td>
                                        {!! ($site->deposit_paid) ? $site->deposit_paid->format('d/m/Y') : '' !!}
                                    </td>
                                    <td>
                                        {!! ($site->construction) ? 'Yes' : '' !!}
                                        {{--}}{{ ($site->JobFirstTaskOfType(582)) ? $site->JobFirstTaskOfType(582)->format('d/m/Y') : ''  }} --}}
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
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script type="text/javascript">

    $(document).ready(function () {
        $('select').change(function() {
            //alert(this.value + ' : ' + this.id);
            $.ajax({
                url: '/site/' + this.id + '/supervisor/' + this.value,
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                   console.log('updated supervisor for Site:')
                },
            })

        });
    });
</script>
@stop