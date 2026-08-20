<div>
    <hr>
    <div class="row">
        <div class="col-md-12">
            <h5><b>PRACTICAL COMPLETION ELECTRONIC SIGN-OFF</b></h5>
            <p>The above items have been checked by the site construction supervisor and conform to the Cape Cod standard set.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3 text-right">Site Supervisor:</div>
        <div class="col-sm-9">
            @if ($prac->supervisor_sign_by)
                {{ \App\User::find($prac->supervisor_sign_by)?->full_name ?? 'Unknown' }}, &nbsp;{{ $prac->supervisor_sign_at?->format('d/m/Y') }}
                @if ($canClearSignoff)
                    <a href="/site/prac-completion/{{ $prac->id }}/clearsignoff" style="margin-left:20px" class="font-red"><i class="fa fa-times"></i> Clear</a>
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
        <div class="col-sm-3 text-right">Construction Manager:</div>
        <div class="col-sm-9">
            @if ($prac->manager_sign_by)
                {{ \App\User::find($prac->manager_sign_by)?->full_name ?? 'Unknown' }}, &nbsp;{{ $prac->manager_sign_at?->format('d/m/Y') }}
            @elseif (!$prac->supervisor_sign_by)
                @if ($allDone)
                    <span class="font-red">Waiting for Prac Supervisor Sign Off</span>
                @else
                    <span class="font-grey-silver">Waiting for items to be completed</span>
                @endif
            @elseif ($canManagerSign)
                <button type="button" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom" wire:click="signManager" wire:loading.attr="disabled" wire:target="signManager">Sign Off</button>
            @else
                <span class="font-red">Pending</span>
            @endif
        </div>
    </div>

    <hr>
    <div class="pull-right" style="min-height:50px">
        <a href="/site/prac-completion" class="btn default">Back</a>

        @if ($canPlaceOnHold)
            <button type="button" class="btn blue" wire:click="placeOnHold" wire:loading.attr="disabled" wire:target="placeOnHold">Place On Hold</button>
        @endif

        @if ($canMakeActive)
            <button type="button" class="btn green" wire:click="makeActive" wire:loading.attr="disabled" wire:target="makeActive">Make Active</button>
        @endif
    </div>

    <br><br>
</div>
