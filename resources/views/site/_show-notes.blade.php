<div class="portlet light" id="show_notes">
    <div class="portlet-title tabbable-line">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Site Notes</span>
            <span class="caption-helper">(last 60 days)</span>
        </div>
        <div class="actions">
            @if (Auth::user()->hasPermission2('edit.site.note'))
                <a href="/site/{{$site->id}}/notes/create" class="btn btn-circle green btn-outline btn-sm">Add</a>
            @endif
        </div>
    </div>
    <div class="portlet-body">
        <?php
        $now = \Carbon\Carbon::now();
        $days60 = $now->subDays(60)->toDateTimeString();
        ?>
        @if ($site->sitenotes()->whereDate('created_at', '>', $days60)->count())
            <div class="scroller" style="height: 200px;" data-always-visible="1" data-rail-visible1="0">
                <div class="row">
                    @foreach ($site->sitenotes()->whereDate('created_at', '>', $days60)->orderBy('created_at', 'DESC')->get() as $note)
                        <div class="col-xs-3">
                            <small>{{ $note->created_at->format('d/m/Y') }}</small>
                        </div>
                        <div class="col-xs-9">
                            <small>{!! nl2br($note->notes) !!}<br>- {{ $note->createdBy->name  }}</small>
                        </div>
                    @endforeach
                </div>
                <hr class="field-hr">
            </div>
        @elseif ($site->sitenotes()->count())
            <?php
            $notes = $site->sitenotes()->orderBy('created_at', 'DESC')->first();
            ?>
            <div class="row">
                <div class="col-xs-12">Last note on {{ $note->created_at->format('d/m/Y') }} by {{ $note->createdBy->name }}<br>{!! nl2br($note->notes) !!}</div>
            </div>
        @else
            <div class="row">
                <div class="col-xs-12">No notes found</div>
            </div>
        @endif
    </div>
</div>