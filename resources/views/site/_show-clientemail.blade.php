<div class="portlet light" id="show_swms">
    <div class="portlet-title tabbable-line">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Client Planner Emails</span>
            <span class="caption-helper">(last 60 days)</span>
        </div>
    </div>
    <div class="portlet-body">
        <?php
        $now = \Carbon\Carbon::now();
        $days60 = $now->subDays(60)->toDateTimeString();
        ?>
        @if ($site->clientPlannerEmails()->where('status', 0)->whereDate('created_at', '>', $days60)->count())
            <div class="scroller" style="height: 300px;" data-always-visible="1" data-rail-visible1="0">
                <div class="row">
                    @foreach ($site->clientPlannerEmails()->whereDate('created_at', '>', $days60)->orderBy('created_at', 'DESC')->get() as $email)
                        <div class="col-xs-3">
                            <small>{{ $email->created_at->format('d M h:i a') }}</small>
                        </div>
                        <div class="col-xs-9">
                            <small>
                                    <a href="/client/planner/email/{{ $email->id }}" target="_blank" title="Client Planner">Client Planner Email</a>  &nbsp; (included {{ $email->docs->count() - 1 }} QA checklists)
                            </small>
                        </div>
                    @endforeach
                </div>
                <hr class="field-hr">
            </div>
        @elseif ($site->clientPlannerEmails()->where('status', 0)->count())
            <?php
            $last = $site->clientPlannerEmails()->where('status', 0)->orderBy('created_at', 'DESC')->first();
            ?>
            <div class="row">
                <div class="col-xs-12">Last email sent on {{ $last->created_at->format('d/m/Y') }}</div>
            </div>
        @else
            <div class="row">
                <div class="col-xs-12">No emails found</div>
            </div>
        @endif
    </div>
</div>
