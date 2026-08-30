@component('mail::message')
    <style>
        .diff td {
            padding: 0 0.667em;
            vertical-align: top;
            white-space: pre;
            white-space: pre-wrap;
            font-family: Consolas, 'Courier New', Courier, monospace;
            font-size: 0.75em;
            line-height: 1.333;
        }

        .diff span {
            display: block;
            min-height: 1.333em;
            margin-top: -1px;
            padding: 0 3px;
        }

        .diff span {
            height: 1.333em;
        }

        .diff span:first-child {
            margin-top: 0;
        }

        .diffDeleted span {
            border: 1px solid rgb(255, 192, 192);
            background: rgb(255, 224, 224);
        }

        .diffInserted span {
            border: 1px solid rgb(192, 255, 192);
            background: rgb(224, 255, 224);
        }
    </style>
    # New Toolbox Talk

    The Toolbox Talk **{{ $talk->name }}** (ID: {{ $talk->id }}) has been created by {{ $talk->createdBy->fullname }} ({{ $talk->createdBy->company->name }}).

    It's a **modified version** of template **{{ \App\Models\Safety\ToolboxTalk::find($talk->master_id)->name }}** v{{ \App\Models\Safety\ToolboxTalk::find($talk->master_id)->version }} (ID:{{ $talk->master_id }})

    {!! $diffs !!}

    @component('mail::button', ['url' => config('app.url').'/safety/doc/toolbox3/'.$talk->id])
        View Talk
    @endcomponent


    Regards,<br>
    {{ config('app.name') }}
@endcomponent

