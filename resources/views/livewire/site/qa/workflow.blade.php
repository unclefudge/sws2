<div>
    @if ($handoverBlocked)
        <div class="row">
            <div class="col-md-12">
                <div class="note note-warning">
                    <b>Handover can't be Signed Off</b><br>
                    This Handover QA can't be signed off by Site Supervisor/Manager until all other related Quality Assurnce documents for this site have been completed.
                    <br><br>Below are a list of outstanding QA's that haven't been signed off yet:<br>
                    <ul>
                        @foreach ($outstandingHandoverQas as $otherQa)
                            <li><a href="/site/qa/{{ $otherQa->id }}" target="_blank">{{ $otherQa->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <p></p><br>
            </div>
        </div>
    @elseif (!$qa->master)
        <div class="row">
            <div class="col-md-12">
                <h5><b>QUALITY ASSURANCE ELECTRONIC SIGN-OFF</b></h5>
                <p>The above inspection items have been checked by the site construction supervisor and conform to the Cape Cod standard set.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3 text-right">Site Supervisor:</div>
            <div class="col-sm-9">
                @if ($qa->supervisor_sign_by)
                    {{ \App\User::find($qa->supervisor_sign_by)?->full_name ?? 'Unknown' }}, &nbsp;{{ $qa->supervisor_sign_at?->format('d/m/Y') }}
                    @if (!$qa->manager_sign_by && Auth::user()->hasPermission2('sig.site.qa'))
                        <a href="/site/qa/{{ $qa->id }}/resetsign"><i class="fa fa-times font-red" style="margin-left:10px"></i></a>
                    @endif
                @elseif ($canSupervisorSign)
                    <button type="button" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom" wire:click="signSupervisor" wire:loading.attr="disabled" wire:target="signSupervisor">Sign Off</button>
                @elseif ($allDone)
                    <span class="font-red">Pending</span>
                @else
                    <span class="font-grey-silver">Waiting for items to be completed</span>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3 text-right">Site Manager:</div>
            <div class="col-sm-9">
                @if ($qa->manager_sign_by)
                    {{ \App\User::find($qa->manager_sign_by)?->full_name ?? 'Unknown' }}, &nbsp;{{ $qa->manager_sign_at?->format('d/m/Y') }}
                @elseif ($qa->supervisor_sign_by)
                    @if ($canManagerSign)
                        <button type="button" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom" wire:click="signManager" wire:loading.attr="disabled" wire:target="signManager">Sign Off</button>
                    @elseif ($allDone)
                        <span class="font-red">Pending</span>
                    @endif
                @elseif ($allDone)
                    <span class="font-red">Waiting for Site Supervisor Sign Off</span>
                @else
                    <span class="font-grey-silver">Waiting for items to be completed</span>
                @endif
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6 pull-right text-right" style="margin-top:15px; padding-right:20px">
            @if ($qa->master)
                <span class="font-grey-salsa">Current version {{ $qa->version }}<br>{!! nl2br($qa->notes) !!}</span>
            @else
                <span class="font-grey-salsa">version {{ $qa->version }}</span>
            @endif
        </div>
    </div>

    <hr>

    <div class="pull-right" style="min-height:50px">
        <a href="{{ $qa->master ? '/site/qa/templates' : '/site/qa' }}" class="btn default">Back</a>

        @if ($canMarkNotRequired)
            <button type="button" class="btn red" wire:click="markNotRequired" wire:loading.attr="disabled" wire:target="markNotRequired">Page Not Required</button>
        @endif

        @if ($canPlaceOnHold)
            <button type="button" class="btn blue" wire:click="placeOnHold" wire:loading.attr="disabled" wire:target="placeOnHold">Place On Hold</button>
            <button type="button" class="btn dark" wire:click="changeToOwnersWorks" wire:loading.attr="disabled" wire:target="changeToOwnersWorks">Change to Owners Works</button>
        @endif

        @if ($canMakeActive)
            <button type="button" class="btn green" wire:click="makeActive" wire:loading.attr="disabled" wire:target="makeActive">Make Active</button>
        @endif

        @if (!$qa->master && $canEdit && $qa->manager_sign_by && (int)$qa->status === 0)
            <a href="/site/qa/{{ $qa->id }}/resetsign" class="btn green">Make Active</a>
        @endif
    </div>

    <br><br>
</div>
