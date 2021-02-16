{{-- Safety Details --}}
<div class="col-lg-6 col-xs-12 col-sm-12 pull-right">
    <div class="portlet light">
        <div class="portlet-title">
            <div class="caption">
                <span class="caption-subject font-dark bold uppercase">Site Safety</span>
            </div>
            <div class="actions">
            </div>
        </div>
        <div class="portlet-body">
            <div class="row">
                <div class="col-md-3">Accidents:</div>
                <div class="col-xs-9">
                    <span class="hidden-md hidden-lg"><br></span>
                    <h3 style="padding: 0px; margin: -7px 0px 0px 0px"><span class="label {{ ($site->accidents->count() > 0) ? 'label-danger' : 'label-success'  }}">  {{ $site->accidents->count() }} </span></h3>
                </div>
            </div>
            <hr class="field-hr">
            <div class="row">
                <div class="col-md-3">Hazards:</div>
                <div class="col-xs-9">
                    <span class="hidden-md hidden-lg"><br></span>
                    <h3 style="padding: 0px; margin: -7px 0px 0px 0px"><span class="label {{ ($site->hazards->count() > 0) ? 'label-danger' : 'label-success'  }}">  {{ $site->hazards->count() }} </span>
                        @if ($site->hasHazardsOpen())
                            <small> &nbsp &nbsp; <a href="/site/hazard"> View Active Hazards</a></small>
                        @endif
                    </h3>
                </div>
            </div>
            <hr class="field-hr">
        </div>
    </div>
</div>