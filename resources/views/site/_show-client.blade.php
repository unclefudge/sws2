{{-- Show Client Details --}}
<div class="portlet light" id="show_client">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Client Details</span>
        </div>
        <div class="actions">
            @if (Auth::user()->allowed2('edit.site', $site))
                <button class="btn btn-circle green btn-outline btn-sm" onclick="editForm('client')">Edit</button>
            @endif
        </div>
    </div>
    <div class="portlet-body">
        <div class="row">
            <div class="col-md-3">Primary Contact:</div>
            <div class="col-xs-9">
                {!! $site->client1_name ? "$site->client1_name<br>" : '' !!}
                {!! ($site->client1_mobile) ? "<a href='tel:'".preg_replace("/[^0-9]/", "", $site->client1_mobile)."> $site->client1_mobile </a><br>" : '' !!}
                {!! ($site->client1_email) ? "<a href='mailto:". $site->client1_email."'> $site->client1_email </a>" : '' !!}
            </div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Secondary Contact:</div>
            <div class="col-xs-9">
                {!! $site->client2_name ? "$site->client2_name<br>" : '' !!}
                {!! ($site->client2_mobile) ? "<a href='tel:'".preg_replace("/[^0-9]/", "", $site->client2_mobile)."> $site->client2_mobile </a><br>" : '' !!}
                {!! ($site->client2_email) ? "<a href='mailto:". $site->client2_email."'> $site->client2_email </a>" : '' !!}
            </div>
        </div>
        <hr class="field-hr">
    </div>
</div>