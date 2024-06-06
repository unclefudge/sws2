@if ($main->docs->count())
        <?php $doc_count = 0; ?>
    <div style="width: 100%; overflow: hidden;">
        @foreach ($main->docs as $doc)
            @if ($doc->type == 'image')
                <div style="width: 60px; float: left; padding-right: 5px">
                    @if(Auth::user()->allowed2('del.site.maintenance', $main))
                        <i class="fa fa-times font-red deleteFile" style="cursor:pointer" data-name="{{ $doc->name }}" data-did="{{$doc->id}}"></i>
                    @endif
                    <a href="{{ $doc->AttachmentUrl }}" target="_blank" class="html5lightbox " title="{{ $doc->name }}" data-lityXXX>
                        <img src="{{ $doc->AttachmentUrl }}" class="thumbnail img-responsive img-thumbnail"></a>
                    <br>
                </div>
                    <?php $doc_count++; ?>
                @if ($doc_count == 10)
                    <br>
                @endif
            @endif
        @endforeach
    </div>
@else
    <div>No photos found<br><br></div>
@endif