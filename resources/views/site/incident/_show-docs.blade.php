{{-- Show Docs --}}
<div class="portlet light" id="show_docs">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Photos / Documents</span>
        </div>
        <div class="actions">
            {{-- Users with 'Edit access' / Involved Person / Assigned Task / Reviewable are allowed to add Docs--}}
            @if ($pEdit || $incident->people->where('user_id', Auth::user()->id) || $incident->hasAssignedTask(Auth::user()->id) || isset($reviewsBy[Auth::user()->id]))
                <a href="/site/incident/{{ $incident->id }}/add_docs" class="btn btn-circle green btn-outline btn-sm">Add</a>
            @endif
        </div>
    </div>
    <div class="portlet-body">
        {{-- Attachments --}}
        @php
            $attachments = $incident->attachments;
            $images = $attachments->where('type', 'image');
            $files  = $attachments->where('type', 'file');
            $doc_count = 0;
        @endphp

        @if ($attachments->isNotEmpty())
            {{-- Photos --}}
            @if ($images->isNotEmpty())
                <div style="width: 100%; overflow: hidden;">
                    @foreach ($images as $attachment)
                        <div style="width: 80px; float: left; padding-right: 5px">
                            <a href="{{ $attachment->url }}" target="_blank" class="html5lightbox " title="{{ $attachment->name }}" data-lityXXX>
                                <img src="{{ $attachment->url }}" class="thumbnail img-responsive img-thumbnail"></a>
                        </div>
                            <?php $doc_count++; ?>
                        @if ($doc_count == 6)
                            <br>
                        @endif
                    @endforeach
                </div>
            @endif

            {{-- Docs --}}
            @if ($files->isNotEmpty())
                <div class="row">
                    <div class="col-xs-12"><b>Documents</b></div>
                </div>
                <hr class="field-hr">
                @foreach ($files as $attachment)
                    <div class="row">
                        <div class="col-xs-6"><i class="fa fa-file-text-o"></i> <a href="{{ $attachment->url }}" target="_blank" title="{{ $attachment->name }}"> {{ $attachment->name }}</a><br></div>
                        <div class="col-xs-6">{{ $attachment->createdBy->full_name }} ({{ $attachment->created_at->format('d/m/Y') }})</div>
                    </div>
                    <hr class="field-hr">
                @endforeach
            @endif
        @else
            <div class="row">
                <div class="col-md-12">No documents</div>
            </div>
        @endif
    </div>
</div>