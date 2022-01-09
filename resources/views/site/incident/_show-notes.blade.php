{{-- Show Notes --}}
<div class="portlet light" id="show_notes">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Notes</span>
        </div>
        <div class="actions">
            @if ($pView)
                <button class="btn btn-circle green btn-outline btn-sm" onclick="addForm('notes')">Add</button>
            @endif
        </div>
    </div>
    <div class="portlet-body">
        <?php
        $notes_count = ($pEdit) ? $incident->actions->count() : $incident->actions->where('created_by', Auth::user()->id)->count();
        ?>
        @if ($notes_count)
            @foreach ($incident->actions as $action)
                {{-- Users can only see their own Notes ulness they have 'Edit' access --}}
                @if ($pEdit || $action->created_by == Auth::user()->id)
                    <div class="row">
                        <div class="col-xs-3">{{ $action->created_at->format('d/m/Y') }}</div>
                        <div class="col-xs-9">{{ $action->action }}<br>- {{ $action->user->fullname }} </div>
                    </div>
                    <hr class="field-hr">
                @endif
            @endforeach
        @else
            <div class="row">
                <div class="col-md-12">No notes</div>
            </div>
        @endif
    </div>
</div>
