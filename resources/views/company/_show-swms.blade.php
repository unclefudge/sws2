<div class="portlet light" id="show_swms">
    <div class="portlet-title tabbable-line">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">SWMS Documents</span>
        </div>
    </div>
    <div class="portlet-body">
        {{-- Licence equired --}}
        <div class="row">
            @if ($company->wmsDocs()->where('status', '>', 0)->count())
                @foreach ($company->wmsDocs()->where('status', '>', 0)->get() as $doc)
                    {{-- Accepted --}}
                    @if ($doc->status == 1)
                        <div class="col-xs-8"><i class="fa fa-check" style="width:35px; padding: 4px 15px; {!! ($company->isCompliant()) ? 'color: #26C281' : '' !!}"></i>
                            <a href="/safety/doc/wms/{{ $doc->id }}" class="linkDark" target="_blank">{{ $doc->name }}</a>
                        </div>
                        <div class="col-xs-4">
                            <?php
                            $now = \Carbon\Carbon::now();
                            $twoyearago = $now->subYears(2)->toDateTimeString();
                            ?>
                            <span class="{!! ($doc->updated_at < $twoyearago) ? 'label label-danger label-sm' : '' !!}">{{ $doc->updated_at->format('d/m/Y') }}</span>
                        </div>

                    @endif
                    {{-- Pending --}}
                    @if ($doc->status == 2)
                        <div class="col-xs-8"><i class="fa fa-question" style="width:35px; padding: 4px 15px"></i>
                            <a href="/safety/doc/wms/{{ $doc->id }}" class="linkDark" target="_blank">{{ $doc->name }}</a>
                        </div>
                        <div class="col-xs-4">
                            <span class="label label-warning label-sm">Pending Approval</span>
                        </div>
                    @endif
                @endforeach
                <hr class="field-hr">
            @else
                <div class="col-xs-12">No active documents found</div>
            @endif
        </div>
    </div>
</div>
