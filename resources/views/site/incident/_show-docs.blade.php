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
        <?php $doc_count = 0; $have_docs = 0; ?>
        @if ($incident->docs->count())
            {{-- Photos --}}
            <div style="width: 100%; overflow: hidden;">
                @foreach ($incident->docs as $doc)
                    @if ($doc->type == 'photo' && file_exists(public_path($doc->AttachmentUrl)))
                        <div style="width: 80px; float: left; padding-right: 5px">
                            <a href="{{ $doc->AttachmentUrl }}" target="_blank" class="html5lightbox " title="{{ $doc->name }}" data-lityXXX>
                                <img src="{{ $doc->AttachmentUrl }}" class="thumbnail img-responsive img-thumbnail"></a>
                        </div>
                        <?php $doc_count ++; ?>
                        @if ($doc_count == 6)
                            <br>
                        @endif
                    @endif
                @endforeach
            </div>

            {{-- Docs --}}
            @foreach ($incident->docs as $doc)
                @if ($doc->type == 'doc')
                    @if (!$have_docs)
                        <div class="row">
                            <div class="col-xs-12"><b>Documents</b></div>
                        </div>
                        <hr class="field-hr">
                        <?php $have_docs = 1; ?>
                    @endif
                    <div class="row">
                        <div class="col-xs-6"><i class="fa fa-file-text-o"></i> <a href="{{ $doc->AttachmentUrl }}" target="_blank" title="{{ $doc->name }}"> {{ $doc->name }}</a><br></div>
                        <div class="col-xs-6">{{ $doc->createdBy->full_name }} ({{ $doc->created_at->format('d/m/Y') }})</div>
                    </div>
                    <hr class="field-hr">
                @endif
            @endforeach
        @else
            <div class="row">
                <div class="col-md-12">No documents</div>
            </div>
        @endif
    </div>
</div>