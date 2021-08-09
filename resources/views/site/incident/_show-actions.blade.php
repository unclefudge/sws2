{{-- Show Tasks --}}
<div class="portlet light" id="show_tasks">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Assigned Tasks</span>
        </div>
        <div class="actions">
            @if (Auth::user()->allowed2('edit.site.incident', $incident))
                <a href="/site/incident/{{ $incident->id }}/witness/create" class="btn btn-circle green btn-outline btn-sm">Add</a>
            @endif
        </div>
    </div>
    <div class="portlet-body">
        @if ($incident->todos()->count())
            @foreach ($incident->witness as $witness)
                <div class="row">
                    <div class="col-xs-1"><a href="/site/incident/{{ $incident->id }}/witness/{{ $witness->id  }}"><i class="fa fa-search"></i></a></div>
                    <div class="col-xs-10">{{ $witness->name }} @if ($witness->user) ({{ $witness->user->company->name }}) @endif </div>
                    <div class="col-xs-1">@if ($witness->status == 2) <i class="fa fa-check font-green"></i> @endif</div>
                </div>
                <hr class="field-hr">
            @endforeach
        @else
            <div class="row">
                <div class="col-md-12">No witness statements</div>
            </div>
        @endif
    </div>
</div>