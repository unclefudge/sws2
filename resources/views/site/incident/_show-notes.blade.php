{{-- Show Notes --}}
<div class="portlet light" id="show_notes">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Notes</span>
        </div>
        <div class="actions">
            @if (Auth::user()->allowed2('edit.site.incident', $incident))
                <button class="btn btn-circle green btn-outline btn-sm" onclick="addForm('notes')">Add</button>
            @endif
        </div>
    </div>
    <div class="portlet-body">
        {{--}}
       @if ($incident->actions->count())
          @foreach ($incident->actions as $action)
              <div class="row">
                  <div class="col-xs-3">{{ $action->created_at->format('d/m/Y') }}</div>
                  <div class="col-xs-9">{{ $action->action }}<br>- {{ $action->user->fullname }} </div>
              </div>
              <hr class="field-hr">
           @endforeach
        @else
            <div class="row">
                <div class="col-md-12">No notes</div>
            </div>
        @endif
        --}}
    </div>
</div>
