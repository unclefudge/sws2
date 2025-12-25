@php
    $files  = $report->attachments->where('type', 'file');
@endphp
@if ($files->isNotEmpty())
    <div style="width: 100%; overflow: hidden;">
        @foreach ($files as $attachment)
            <i class="fa fa-file-text-o"></i> <a href="{{ $attachment->url }}" target="_blank" title="{{ $attachment->name }}"> {{ $attachment->name }}</a>
            @if(Auth::user()->allowed2('del.site.inspection', $report))
                <i class="fa fa-times font-red deleteFile" style="cursor:pointer" data-name="{{ $attachment->name }}" data-did="{{$attachment->id}}"></i>
            @endif
            <br>
        @endforeach
    </div>
@else
    <div>No documents found<br><br></div>
@endif

