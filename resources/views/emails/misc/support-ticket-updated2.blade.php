@include('emails/_email-begin')

<table class="v1inner-body" align="center" width="90%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; margin: 0 auto; padding: 0; width: 90%;">
    <tr>
        <td class="v1content-cell" style="padding: 35px">
            <h1>Support Ticket</h1>
            <p>A support ticket has been updated on {{ config('app.name') }} with the following details:</p>
            <table style="border-collapse: collapse">
                <tr>
                    <td width="100"><b>Ticket ID</b></td>
                    <td>{{ $ticket->id  }}</td>
                </tr>
                <tr>
                    <td><b>Priority</b></td>
                    <td>{{ $ticket->priority_text  }}</td>
                </tr>
                <tr>
                    <td><b>Name</b></td>
                    <td>{{ $ticket->name  }}</td>
                </tr>
                <tr>
                    <td style="vertical-align: top"><b>Description</b></td>
                    <td>{!! nl2br($action->action) !!}</td>
                </tr>

                <tr>
                    <td><b>Date/Time</b></td>
                    <td>{{ $ticket->created_at->format('d/m/Y g:i a')  }}</td>
                </tr>
                <tr>
                    <td><b>Created By</b></td>
                    <td>{{ $ticket->createdBy->name  }}</td>
                </tr>
            </table>
            <br>
            @include('emails/_button-begin')
            <a href="{{ config('app.url') }}/support/ticket/{{$ticket->id}}" class="v1button" target="_blank" rel="noreferrer">View Ticket</a>
            @include('emails/_button-end')
        </td>
    </tr>
</table>

@include('emails/_email-end')