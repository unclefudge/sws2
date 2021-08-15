{{-- Show Conversations --}}
<div class="portlet light" id="show_conversations">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Conversations</span>
        </div>
        <div class="actions">
            @if ($pEdit && $incident->status)
                <a href="/site/incident/{{ $incident->id }}/conversation/create" class="btn btn-circle green btn-outline btn-sm">Add</a>
            @endif
        </div>
    </div>
    <div class="portlet-body">
        @if ($incident->conversations->count())
            @foreach ($incident->conversations as $conversation)
                <div class="row">
                    <div class="col-xs-1"><a href="/site/incident/{{ $incident->id }}/conversation/{{ $conversation->id  }}"><i class="fa fa-search"></i></a></div>
                    <div class="col-xs-10">{{ $conversation->name }} @if ($conversation->user) ({{ $conversation->user->company->name }}) @endif </div>
                    <div class="col-xs-1">@if ($conversation->status == 2) <i class="fa fa-check font-green"></i> @endif</div>
                </div>
                <hr class="field-hr">
            @endforeach
        @else
            <div class="row">
                <div class="col-md-12">No conversations</div>
            </div>
        @endif
    </div>
</div>