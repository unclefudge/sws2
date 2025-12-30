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
                <div class="portlet light">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze">
                            Recent Reports
                        </span>
                        </div>
                    </div>

                    <div class="portlet-body">
                        <h3>Reports created in the last 10 days</h3>

                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th>Report</th>
                                <th width="20%">Status</th>
                                <th width="20%">Date</th>
                            </tr>
                            </thead>

                            <tbody>
                            <tr v-for="report in xx.reports" :key="report.id">
                                <td>
                                    <a v-if="report.status === 'completed'" :href="`/reports/${report.id}`" target="_blank">@{{ report.name }}</a>
                                    <span v-else>@{{ report.name }}</span>

                                </td>
                                <td>
                                    <span v-if="report.status === 'pending'" class="text-muted"><i class="fa fa-spin fa-spinner text-muted"></i> Pending</span>
                                    <span v-if="report.status === 'processing'" class="text-warning"><i class="fa fa-spin fa-spinner text-muted"></i> Processing…</span>
                                    <span v-if="report.status === 'completed'" class="text-success">Ready
                                </span>
                                    <span v-if="report.status === 'failed'" class="text-danger">Failed</span>
                                </td>

                                <td>@{{ formatDate(report.created_at) }}</td>
                            </tr>

                            <tr v-if="!xx.reports.length">
                                <td colspan="4" class="text-center">No reports</td>
                            </tr>
                            </tbody>
                        </table>

                        <div class="form-actions right">
                            <a href="/manage/report" class="btn default">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts')
    <script src="/js/libs/vue.1.0.24.js"></script>
    <script>
        var xx = {reports: []};

        new Vue({
            el: 'body',
            data() {
                return {xx};
            },
            methods: {
                loadData() {
                    $.get('/manage/report/recent/files', res => {
                        this.xx.reports = res;
                    });
                },
                formatDate(date) {
                    return new Date(date).toLocaleDateString();
                }
            },
            ready() {
                this.loadData();
                setInterval(this.loadData, 4000);
            }
        });
    </script>
@stop