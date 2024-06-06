@if ($main->docs->count())
        <?php $doc_count = 0; ?>
    <div style="width: 100%; overflow: hidden;">
        @foreach ($main->docs as $doc)
            @if ($doc->type == 'file')
                <i class="fa fa-file-text-o"></i> <a href="{{ $doc->AttachmentUrl }}" target="_blank" title="{{ $doc->name }}"> {{ $doc->name }}</a>
                @if(Auth::user()->allowed2('del.site.maintenance', $main))
                    <i class="fa fa-times font-red deleteFile" style="cursor:pointer" data-name="{{ $doc->name }}" data-did="{{$doc->id}}"></i>
                @endif<br>
            @endif
        @endforeach
    </div>
@else
    <div>No documents found<br><br></div>
@endif