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
                                        <div class="text-center"><a onclick="go2preconstruction({{ $site_id }})"><i class="fa fa-search"></i></a></div>
                                    </td>
                                    <td>{{ $site->code }}</td>
                                    <td>{{ $site->name }}</td>
                                    <td>{{ ($site->JobStart) ? $site->JobStart->format('d/m/Y') : '' }}</td>
                                    <td>
                                       <select id="{{ $site->id }}" class="form-control bs-select" name="supervisor" title="Select supervisor">
                                            @foreach(Auth::user()->company->supervisorsSelect() as $id => $name)
                                                <option value="{{ $id }}" @if ($site->supervisors->first() && $id == $site->supervisors->first()->id) selected @endif>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>{!! ($site->council_approval) ? $site->council_approval->format('d/m/Y') : '' !!}</td>
                                    <td>{!! ($site->contract_sent) ? $site->contract_sent->format('d/m/Y') : '' !!}</td>
                                    <td>{!! ($site->contract_signed) ? $site->contract_signed->format('d/m/Y') : '' !!}</td>
                                    <td>{!! ($site->deposit_paid) ? $site->deposit_paid->format('d/m/Y') : '' !!}</td>
                                    <td>{!! ($site->construction) ? 'Yes' : '' !!}</td>
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

        //$("button").click(function(){
            // /planner/preconstruction/{{ $site->id }}

            //$.post("demo_test_post.asp",
             //       {
             //           name: "Donald Duck",
              //          city: "Duckburg"
              //      },
              //      function(data,status){
              //          alert("Data: " + data + "\nStatus: " + status);
              //      });

        //});
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
</script>
@stop