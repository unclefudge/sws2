{{-- Show Prevent --}}
<div class="portlet light" id="show_prevent">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Actions to Prevent Reoccurence</span>
        </div>
        <div class="actions">
            @if (Auth::user()->allowed2('edit.site.incident', $incident))
                <button class="btn btn-circle green btn-outline btn-sm" onclick="editForm('prevent')">Edit</button>
            @endif
        </div>
    </div>
    <div class="portlet-body">
        {{-- Preventive Strategies --}}
        <div class="row">
            <div class="col-md-2">Preventive Strategies:</div>
            @if ($qPreventive->responsesCSV('site_incidents', $incident->id))
                <div class="col-md-10">{!! $qPreventive->responsesBullet('site_incidents', $incident->id) !!}</div>
            @else
                <div class="col-md-10">No actions specified</div>
            @endif
        </div>
        <br>

        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped table-bordered table-hover order-column" id="table_prevent">
                    <thead>
                    <tr class="mytable-header">
                        <th width="5%"> #</th>
                        <th width="30%"> Contributing Factor(s) / Root cause(s)</th>
                        <th> Action)s) Taken / Recommended</th>
                        <th> By Whom</th>
                        <th> Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($incident->preventActions() as $action)
                        <?php list($crap, $action_name) = explode(' : ', $action->name); ?>
                        <tr>
                            <td>
                                <div class="text-center"><a href="/todo/{{ $action->id  }}"><i class="fa fa-search"></i></a></div>
                            </td>
                            <td>{{ $action_name }}</td>
                            <td>{{ ($action->info) ? $action->info : '' }}</td>
                            <td>{!! ($action->assignedToBySBC()) ? $action->assignedToBySBC() : "<span class='font-red'>Unassigned</span>" !!}</td>
                            <td>{{ ($action->due_at) ? $action->due_at->format('d/m/Y') : '' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <hr class="field-hr">
    </div>
</div>