@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="{{ route('hia.contracts.index') }}">HIA Contracts</a><i class="fa fa-circle"></i></li>
        <li><span>{{ $contract->site->code }}</span></li>
    </ul>
@stop

@section('content')
    @php
        $display = fn ($value) => ($value === null || $value === '') ? '—' : $value;
        $money = fn ($value) => is_numeric(str_replace([',', '$'], '', (string) $value)) ? '$' . number_format((float) str_replace([',', '$'], '', (string) $value), 2) : $display($value);
        $hiaStatus = (int) ($hiaContract['Status'] ?? 0);
        $hiaStatusLabels = [1 => 'In Progress', 2 => 'Completed', 4 => 'Legacy - Read Only'];
    @endphp

    <div class="page-content-inner">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if($hiaError)<div class="alert alert-danger"><strong>Unable to load the live HIA contract.</strong><br>{{ $hiaError }}</div>@endif

        <div class="row">
            <div class="col-md-8">
                <div class="portlet light bordered">
                    <div class="portlet-title"><div class="caption font-green-haze"><span class="caption-subject bold uppercase">Contract Comparison</span><span class="caption-helper"> {{ $contract->site->code }} — {{ $contract->site->name }}</span></div></div>
                    <div class="portlet-body">
                        <table class="table table-striped table-hover">
                            <thead><tr><th>Field</th><th>SafeWorksite</th><th>Live HIA</th><th>Status</th></tr></thead>
                            <tbody>
                            @foreach($comparison as $item)
                                @php
                                    $comparable = $item['sws'] !== null && $item['hia'] !== null;
                                    $matches = $comparable && trim((string) $item['sws']) === trim((string) $item['hia']);
                                @endphp
                                <tr>
                                    <td><strong>{{ $item['label'] }}</strong></td>
                                    <td>{{ $display($item['sws']) }}</td>
                                    <td>{{ $display($item['hia']) }}</td>
                                    <td>@if(!$comparable)<span class="text-muted">—</span>@elseif($matches)<span class="label label-sm label-success">Match</span>@else<span class="label label-sm label-warning">Different</span>@endif</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="portlet light bordered">
                    <div class="portlet-title"><div class="caption font-green-haze"><span class="caption-subject bold uppercase">SafeWorksite Contract Details</span></div></div>
                    <div class="portlet-body">
                        <div class="row">
                            <div class="col-md-6"><p><strong>Owner 1</strong><br>{{ $display($contract->owner1_title) }} {{ $display($contract->owner1_name) }}<br>{{ $display($contract->owner1_mobile) }}<br>{{ $display($contract->owner1_email) }}</p></div>
                            <div class="col-md-6"><p><strong>Owner 2</strong><br>{{ $display($contract->owner2_title) }} {{ $display($contract->owner2_name) }}<br>{{ $display($contract->owner2_mobile) }}<br>{{ $display($contract->owner2_email) }}</p></div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6"><p><strong>Owner Address</strong><br>{{ $display($contract->owner_address) }}<br>{{ $display($contract->owner_suburb) }} {{ $display($contract->owner_state) }} {{ $display($contract->owner_postcode) }}</p></div>
                            <div class="col-md-6"><p><strong>Land</strong><br>Lot {{ $display($contract->land_lot) }} / DP {{ $display($contract->land_dp) }}<br>{{ $display($contract->land_address) }}<br>{{ $display($contract->land_suburb) }} {{ $display($contract->land_state) }} {{ $display($contract->land_postcode) }}</p></div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-3"><strong>Contract Price</strong><br>{{ $money($contract->contract_price) }}</div>
                            <div class="col-md-3"><strong>GST</strong><br>{{ $money($contract->contract_gst) }}</div>
                            <div class="col-md-3"><strong>Deposit</strong><br>{{ $money($contract->deposit) }}</div>
                            <div class="col-md-3"><strong>Building Period</strong><br>{{ $display($contract->building_period) }}</div>
                        </div>

                        <h4 style="margin-top: 25px;">Payment Stages</h4>
                        @if(count($contract->stages ?? []))
                            <table class="table table-striped table-hover"><thead><tr><th>#</th><th>Stage</th><th>Percentage</th><th>Amount</th></tr></thead><tbody>
                            @foreach($contract->stages as $stage)
                                <tr><td>{{ $stage['stage_no'] ?? $loop->iteration }}</td><td>{{ $stage['name'] ?? $stage['stage_name'] ?? $stage['description'] ?? '—' }}</td><td>{{ $stage['percentage'] ?? $stage['percent'] ?? '—' }}</td><td>{{ isset($stage['amount']) ? $money($stage['amount']) : '—' }}</td></tr>
                            @endforeach
                            </tbody></table>
                        @else
                            <p class="text-muted">No payment stages saved.</p>
                        @endif

                        <details style="margin-top: 20px;"><summary style="cursor:pointer;color:#337ab7;font-weight:600;">Show saved HIA XML</summary><pre style="white-space:pre-wrap;word-break:break-word;margin-top:10px;max-height:500px;overflow:auto;">{{ $contract->hia_xml ?: 'No HIA XML saved.' }}</pre></details>
                        @if($hiaJson)<details style="margin-top: 12px;"><summary style="cursor:pointer;color:#337ab7;font-weight:600;">Show live HIA response</summary><pre style="white-space:pre-wrap;word-break:break-word;margin-top:10px;max-height:500px;overflow:auto;">{{ $hiaJson }}</pre></details>@endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="portlet light bordered">
                    <div class="portlet-title"><div class="caption font-green-haze"><span class="caption-subject bold uppercase">HIA Summary</span></div></div>
                    <div class="portlet-body">
                        <p><strong>SafeWorksite Contract ID</strong><br>{{ $contract->id }}</p>
                        <p><strong>HIA Contract ID</strong><br>{{ $display($contract->hia_contract_id) }}</p>
                        <p><strong>HIA Template ID</strong><br>{{ $display($contract->hia_template_id) }}</p>
                        <p><strong>SafeWorksite Updated</strong><br>{{ optional($contract->updated_at)->format('d/m/Y H:i') }}</p>
                        <p><strong>HIA Status</strong><br>{{ $hiaStatusLabels[$hiaStatus] ?? ($hiaStatus ?: '—') }}</p>
                        <p><strong>HIA Updated</strong><br>{{ !empty($hiaContract['LastModifiedDate']) ? \Carbon\Carbon::parse($hiaContract['LastModifiedDate'])->format('d/m/Y H:i') : '—' }}</p>

                        @if($contract->hia_contract_id)
                            <a class="btn blue btn-block" href="{{ route('hia.contracts.pdf', $contract->hia_contract_id) }}" target="_blank"><i class="fa fa-file-pdf-o"></i> View Live HIA PDF</a>
                        @endif
                        @if($contract->hia_pdf)
                            <a class="btn btn-default btn-block" href="{{ route('hia.contracts.stored-pdf', $contract) }}" target="_blank"><i class="fa fa-file-pdf-o"></i> View Stored PDF</a>
                        @endif
                        @if($hiaStatus === 4)
                            <div class="alert alert-warning" style="margin-top:15px;">This contract belongs to HIA's retired system and cannot be edited.</div>
                            <button class="btn red btn-outline btn-block" type="button" data-toggle="modal" data-target="#detach_legacy_modal"><i class="fa fa-unlink"></i> Detach Legacy HIA Link</button>
                        @else
                            <button class="btn green btn-block" type="button" data-toggle="modal" data-target="#sync_contract_modal"><i class="fa fa-refresh"></i> {{ $contract->hia_contract_id ? 'Update HIA from SafeWorksite' : 'Create Contract in HIA' }}</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="sync_contract_modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button><h4 class="modal-title">Confirm HIA Synchronisation</h4></div>
            <div class="modal-body"><p>This will {{ $contract->hia_contract_id ? 'overwrite the linked HIA contract with the current SafeWorksite contract data' : 'create a new contract in HIA using the current SafeWorksite contract data' }}.</p><p><strong>Job:</strong> {{ $contract->site->code }} — {{ $contract->site->name }}</p></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('hia.contracts.sync', $contract) }}" style="display:inline;">@csrf<button type="submit" class="btn green"><i class="fa fa-refresh"></i> Confirm Sync</button></form>
            </div>
        </div></div>
    </div>

    @if($hiaStatus === 4)
        <div class="modal fade" id="detach_legacy_modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog"><div class="modal-content">
                <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button><h4 class="modal-title">Detach Legacy HIA Contract</h4></div>
                <div class="modal-body">
                    <div class="alert alert-warning"><strong>This does not delete anything from HIA.</strong></div>
                    <p>This will remove legacy HIA contract <strong>{{ $contract->hia_contract_id }}</strong> from this SafeWorksite record while retaining the SafeWorksite contract data.</p>
                    <p>If Zoho synchronises this contract later, SafeWorksite will be able to create a new contract in HIA's current system.</p>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button><form method="POST" action="{{ route('hia.contracts.detach-legacy', $contract) }}" style="display:inline;">@csrf<button type="submit" class="btn red"><i class="fa fa-unlink"></i> Detach Legacy Link</button></form></div>
            </div></div>
        </div>
    @endif
@stop
