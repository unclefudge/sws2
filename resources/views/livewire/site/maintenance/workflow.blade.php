<div>
    <hr>
    <div class="row">
        <div class="col-md-12">
            <h5><b>MAINTENANCE REQUEST ELECTRONIC SIGN-OFF</b></h5>
            <p>The above maintenance items have been checked by the site construction supervisor and conform to the Cape Cod standard set.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3 text-right">Maintenance Supervisor:</div>
        <div class="col-sm-9">
            @if ($main->supervisor_sign_by)
                {{ \App\User::find($main->supervisor_sign_by)?->full_name ?? 'Unknown' }},
                &nbsp;{{ $main->supervisor_sign_at?->format('d/m/Y') }}
            @elseif ($canSupervisorSign)
                <button type="button" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom" wire:click="signSupervisor" wire:loading.attr="disabled" wire:target="signSupervisor">
                    Sign Off
                </button>
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
            @if ($main->manager_sign_by)
                {{ \App\User::find($main->manager_sign_by)?->full_name ?? 'Unknown' }},
                &nbsp;{{ $main->manager_sign_at?->format('d/m/Y') }}
            @elseif (!$main->supervisor_sign_by)
                @if ($allDone)
                    <span class="font-red">Waiting for Maintenance Supervisor Sign Off</span>
                @else
                    <span class="font-grey-silver">Waiting for items to be completed</span>
                @endif
            @elseif ($canManagerSign)
                <button type="button" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom" wire:click="signManager" wire:loading.attr="disabled" wire:target="signManager">
                    Sign Off
                </button>
            @else
                <span class="font-red">Pending</span>
            @endif
        </div>
    </div>

    <hr>
    <div class="pull-right" style="min-height:50px">
        <a href="/site/maintenance" class="btn default">Back</a>

        @if ($canPlaceUnderReview)
            <button type="button" class="btn blue" wire:click="placeUnderReview" wire:loading.attr="disabled" wire:target="placeUnderReview">
                Place On Hold
            </button>
        @endif

        @if ($canMakeActive)
            <button type="button" class="btn green" wire:click="makeActive" wire:loading.attr="disabled" wire:target="makeActive">
                Make Active
            </button>
        @endif
    </div>
    <br><br>
</div>
