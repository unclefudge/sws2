@if ($main->docs->count())
    <?php $doc_count = 0; ?>
    <div style="width: 100%; overflow: hidden;">
        @foreach ($main->docs as $doc)
            @if ($doc->type == 'file')
                <i class="fa fa-file-text-o"></i> <a href="{{ $doc->AttachmentUrl }}" target="_blank" title="{{ $doc->name }}"> {{ $doc->name }}</a><br>
            @endif
        @endforeach
    </div>
@else
    <div>No documents found<br><br></div>
@endif