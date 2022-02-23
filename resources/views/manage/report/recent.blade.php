@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Recent</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Recent Reports</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <h3>Reports created in the last 10 days</h3>
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th> Report</th>
                                <th width="20%"> Date</th>
                            </tr>
                            </thead>

                            <tbody v-if="xx.reports">
                            <tr v-for="(report, size) in xx.reports ">
                                <td>
                                    <div class="text-center"><a href="/filebank/tmp/report/{{ Auth::user()->company_id }}/@{{ report }}" target="_blank"><i class="fa fa-file-text-o"></i></a></div>
                                </td>
                                <td>@{{ report }}</td>
                                <td>
                                    <span v-if="size">@{{ date(report) }}</span>
                                    <span v-else="size"><span class="font-red"><i class="fa fa-spin fa-spinner"> </i> Processing</span></span>
                                </td>
                            </tr>
                            </tbody>
                            <tr v-if="!xx.reports"><td colspan="3"><br>No reports<br><br></td> </tr>
                        </table>

                        <div class="form-actions right">
                            <a href="/manage/report" class="btn default"> Back</a>
                        </div>
                    </div>
                    <pre v-if="xx.dev">@{{ $data | json }}</pre>-->
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
<script src="/js/libs/vue.1.0.24.js" type="text/javascript"></script>
<script src="/js/libs/vue-resource.0.7.0.js" type="text/javascript"></script>
<script src="/js/vue-app-basic-functions.js" type="text/javascript"></script>
<script>
    var xx = {
        dev: dev, permission: '', user_company_id: '',
        params: {date: '', supervisor_id: '', site_id: '', site_start: '', trade_id: '', _token: $('meta[name=token]').attr('value')},
        reports: []
    };

    new Vue({
        el: 'body',
        data: function () {
            //items: []
            return {xx: xx};
        },
        methods: {
            loadData: function () {
                $.get('/manage/report/recent/files', function (response) {
                    console.log(response);
                    this.xx.reports = response;
                }.bind(this));
            },
            date: function(file) {
                var y = file.substr(file.length - 18, 4);
                var m = file.substr(file.length - 14, 2);
                var d = file.substr(file.length - 12, 2);
                return d+'/'+m+'/'+y;
            }
        },
        ready: function () {
            this.loadData();

            setInterval(function () {
                this.loadData();
            }.bind(this), 3000);
        }
    });
</script>
@stop