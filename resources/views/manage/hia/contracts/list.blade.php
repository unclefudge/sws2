@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>HIA Contracts</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light">
                    <div class="portlet-title">
                        <div class="caption font-dark"><i class="fa fa-file-text-o font-green-haze"></i> <span class="caption-subject bold uppercase font-green-haze">HIA Contracts</span></div>
                        <div class="actions"><a class="btn btn-circle green btn-outline btn-sm" href="{{ route('hia.contracts.index') }}"><i class="fa fa-refresh"></i> Refresh from HIA</a></div>
                    </div>

                    @if($hiaError)
                        <div class="alert alert-danger"><strong>HIA could not be contacted.</strong> SafeWorksite contracts are still shown below.<br>{{ $hiaError }}</div>
                    @endif

                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-md-3">
                            <label class="control-label">Sync State</label>
                            <select id="filter_state" class="form-control select2">
                                <option value="current" selected>Current HIA System</option>
                                <option value="">All States</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                                <option value="SafeWorksite only">SafeWorksite only</option>
                                <option value="HIA only">HIA only</option>
                                <option value="Legacy - Read Only">Legacy - Read Only</option>
                                <option value="Possible match">Possible match</option>
                                <option value="HIA unavailable">HIA unavailable</option>
                            </select>
                        </div>
                    </div>

                    <div class="portlet-body">
                        <table class="table table-striped table-hover order-column" id="table_list">
                            <thead>
                            <tr>
                                <th>Job</th>
                                <th>Client</th>
                                <th>SWS ID</th>
                                <th>HIA ID</th>
                                <th>Template</th>
                                <th>Sync State</th>
                                <th>HIA Updated</th>
                                <th class="text-center">PDF</th>
                                <th class="text-center">View</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($contracts as $row)
                                @php
                                    $local = $row['local'];
                                    $hia = $row['hia'];
                                    $possible = $row['possible_match'];
                                    $stateLabels = [
                                        'matched' => ['Matched', 'success'],
                                        'sws_only' => ['SafeWorksite only', 'info'],
                                        'hia_only' => ['HIA only', 'warning'],
                                        'current_in_progress' => ['In Progress', 'success'],
                                        'current_completed' => ['Completed', 'primary'],
                                        'legacy' => ['Legacy - Read Only', 'default'],
                                        'possible_match' => ['Possible match', 'warning'],
                                        'hia_unavailable' => ['HIA unavailable', 'default'],
                                    ];
                                    [$stateText, $stateClass] = $stateLabels[$row['state']];
                                    $hiaId = $hia['contract_id'] ?? $possible['contract_id'] ?? $local?->hia_contract_id;
                                @endphp
                                <tr>
                                    <td>{{ $local?->site?->code ?? ($hia['job_number'] ?? '—') }}</td>
                                    <td>{{ $local?->site?->name ?? ($hia['client'] ?? '—') }}</td>
                                    <td>{{ $local?->id ?? '—' }}</td>
                                    <td>{{ $hiaId ?: '—' }}</td>
                                    <td>{{ $local?->hia_template_id ?? ($hia['template_id'] ?? '—') }}</td>
                                    <td><span class="label label-sm label-{{ $stateClass }}">{{ $stateText }}</span></td>
                                    <td data-order="{{ $hia['modified'] ?? '' }}">{{ !empty($hia['modified']) ? \Carbon\Carbon::parse($hia['modified'])->format('d/m/Y H:i') : '—' }}</td>
                                    <td class="text-center">@if($hiaId)
                                            <a href="{{ route('hia.contracts.pdf', $hiaId) }}" target="_blank" title="View live HIA PDF"><i class="fa fa-file-pdf-o font-red"></i></a>
                                        @else
                                            —
                                        @endif</td>
                                    <td class="text-center">@if($local)
                                            <a href="{{ route('hia.contracts.show', $local) }}" class="btn btn-outline btn-xs blue" title="View contract"></a>
                                        @else
                                            —
                                        @endif</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    <script type="text/javascript">
        var table = $('#table_list').DataTable({pageLength: 100, order: [[0, 'desc']]});

        function applyStateFilter(value) {
            if (value === 'current') {
                table.column(5).search('^(?!Legacy - Read Only$).*$', true, false).draw();
            } else {
                table.column(5).search(value ? '^' + $.fn.dataTable.util.escapeRegex(value) + '$' : '', true, false).draw();
            }
        }

        $('#filter_state').select2({width: '100%'}).on('change', function () {
            applyStateFilter(this.value);
        });
        applyStateFilter('current');
    </script>
@stop
