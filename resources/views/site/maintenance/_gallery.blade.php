@php
    $images = $main->attachments->where('type', 'image');
    $doc_count = 0;
@endphp
@if ($images->isNotEmpty())
    <div style="width: 100%; overflow: hidden;">
        @foreach ($images as $attachment)
            <div style="width: 60px; float: left; padding-right: 5px">
                @if(Auth::user()->allowed2('del.site.maintenance', $main))
                    <i class="fa fa-times font-red deleteFile" style="cursor:pointer" data-name="{{ $attachment->name }}" data-did="{{$attachment->id}}"></i>
                @endif
                <a href="{{ $attachment->url }}" target="_blank" class="html5lightbox " title="{{ $attachment->name }}" data-lityXXX>
                    <img src="{{ $attachment->url }}" class="thumbnail img-responsive img-thumbnail"></a>
            </div>
                <?php $doc_count++; ?>
            @if ($doc_count == 10)
                <br>
            @endif
        @endforeach
    </div>
@else
    <div>No photos found<br><br></div>
@endif
