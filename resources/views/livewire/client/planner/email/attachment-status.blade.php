<div @if ($hasPending) wire:poll.3s="refreshAttachments" @endif>
    <div class="row">
        <div class="col-md-1"><b>Attachments:</b></div>
        <div class="col-md-11">
            @forelse ($attachments as $file)
                <span style="display:inline-block; margin:0 12px 5px 0">
                    @if ($file['status'] === 1)
                        <i class="fa fa-file-pdf-o"></i>
                        @if ($file['url'])
                            <a href="{{ $file['url'] }}" target="_blank" title="{{ $file['name'] }}">{{ $file['name'] }}</a>
                        @else
                            {{ $file['name'] }}
                        @endif
                    @elseif ($file['status'] === 2)
                        <span class="font-yellow-gold"><i class="fa fa-spinner fa-pulse"></i> {{ $file['name'] }} — generating</span>
                    @else
                        <span class="font-red"><i class="fa fa-exclamation-triangle"></i> {{ $file['name'] }} — failed</span>
                    @endif
                </span>
            @empty
                <span class="font-grey-silver">No attachments.</span>
            @endforelse
        </div>
    </div>

    @if ($hasPending)
        <div class="note note-info" style="margin:10px 0 0">
            <p style="margin:0"><i class="fa fa-spinner fa-pulse"></i> Waiting for attachments to finish generating. You can continue editing the email while they complete.</p>
        </div>
    @elseif ($hasFailed)
        <div class="note note-danger" style="margin:10px 0 0">
            <p style="margin:0"><i class="fa fa-exclamation-triangle"></i> One or more attachments failed to generate. The email cannot be sent until this is resolved.</p>
        </div>
    @endif
</div>
