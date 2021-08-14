{{-- Show Tasks --}}
<div class="portlet light" id="show_tasks">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Assigned Tasks</span>
        </div>
        <div class="actions">
            @if (Auth::user()->allowed2('edit.site.incident', $incident))
                <a href="/todo/create/incident/{{ $incident->id}}" class="btn btn-circle green btn-outline btn-sm">Add</a>
            @endif
        </div>
    </div>
    <div class="portlet-body">
        @if ($incident->todos()->count())
            <div class="row">
                <div class="col-xs-1">#</div>
                <div class="col-xs-8"><b>Action</b></div>
                <div class="col-xs-3"><b>Completed By</b></div>
            </div>
            <hr class="field-hr">

            @foreach ($incident->todos() as $todo)
                <div class="row">
                    <div class="col-xs-1"><a href="/todo/{{ $todo->id }}"><i class="fa fa-search"></i></a></div>
                    <div class="col-xs-8">
                        {{ $todo->info }}<br><br><i>Assigned to: {{ $todo->assignedToBySBC() }}</i>
                        @if ($todo->comments)
                            <br><b>Comments:</b> {{ $todo->comments }}
                        @endif
                        @if ($todo->attachment)
                            <br><a href="{{ $todo->attachmentUrl }}" target="_blank"  class="btn btn-xs blue"><i class="fa fa-picture-o"></i> Photo</a>
                        @endif
                    </div>
                    <div class="col-xs-3">
                        @if ($todo->status && !$todo->done_by)
                            <span class="font-red">Outstanding</span>
                        @else
                            {!! $todo->doneBy->full_name  !!}<br>{{ ($todo->done_at) ? $todo->done_at->format('d/m/Y') : '' }}
                        @endif
                    </div>
                </div>
                <hr class="field-hr">
            @endforeach
        @else
            <div class="row">
                <div class="col-md-12">No assigned tasks</div>
            </div>
        @endif
    </div>
</div>