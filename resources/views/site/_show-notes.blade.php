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
                @foreach ($site->sitenotes()->whereDate('created_at', '>', $days60)->orderBy('created_at', 'DESC')->get() as $note)
                    <div class="row">
                        <div class="col-xs-3">
                            <small><a href="/site/note/{{$note->id}}"><i class="fa fa-search"></i></a> &nbsp; {{ $note->created_at->format('d/m/Y') }}</small>
                        </div>
                        <div class="col-xs-9">
                            <small>
                                {!! truncate(nl2br($note->notes, 40)) !!}<br>
                                @if ($note->category_id == '16')
                                    {{-- Approved Variation --}}
                                    <b>Approved Variation:</b> {{ $note->variation_name }}<br>
                                    {{ $note->variation_info }}<br>
                                    Cost: {{ $note->variation_cost }} &nbsp: Days: {{ $note->variation_days }}
                                @endif
                                @if ($note->category_id == '89')
                                    {{--Prac Completion --}}
                                    <b>Prac Notified:</b> {{ ($note->prac_notified) ? $note->prac_notified->format('d/m/Y') : '' }}<br>
                                    <b>Prac Meeting:</b> {{ ($note->prac_meeting) ? $note->prac_meeting->format('d/m/Y h:i a') : '' }}<br>
                                @endif
                                <br>- {{ $note->createdBy->name  }}<br>
                                @if ($note->attachments()->count())
                                    @foreach($note->attachments() as $attachment)
                                        <a href='{{$attachment->url}}' target='_blank'>{{$attachment->name}}</a><br>
                                    @endforeach
                                @endif
                            </small>
                        </div>
                    </div>
                    <hr class="field-hr">
                @endforeach
            </div>
        @elseif (count($site->sitenotes))
                <?php $note = $site->sitenotes()->orderBy('created_at', 'DESC')->first(); ?>
            <div class="row">
                <div class="col-xs-12">Last note on {{ $note->created_at->format('d/m/Y') }}
                    by {{ $note->createdBy->name }}<br>{!! nl2br($note->notes) !!}</div>
            </div>
        @else
            <div class="row">
                <div class="col-xs-12">No notes found</div>
            </div>
        @endif
    </div>
</div>
