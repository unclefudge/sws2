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
                {!! $site->client_phone_desc ? "$site->client_phone_desc<br>" : '' !!}
                {!! ($site->client_phone) ? "<a href='tel:'".preg_replace("/[^0-9]/", "", $site->client_phone)."> $site->client_phone </a><br>" : '' !!}
                {!! ($site->client_email) ? "<a href='mailto:". $site->client_email."'> $site->client_email </a>" : '' !!}
            </div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="col-md-3">Secondary Contact:</div>
            <div class="col-xs-9">
                {!! $site->client_phone2_desc ? "$site->client_phone2_desc<br>" : '' !!}
                {!! ($site->client_phone2) ? "<a href='tel:'".preg_replace("/[^0-9]/", "", $site->client_phone2)."> $site->client_phone2 </a><br>" : '' !!}
                {!! ($site->client_email2) ? "<a href='mailto:". $site->client_email2."'> $site->client_email2 </a>" : '' !!}
            </div>
        </div>
        <hr class="field-hr">
    </div>
</div>