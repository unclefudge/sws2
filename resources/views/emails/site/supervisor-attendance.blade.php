@include('emails/_email-begin')

<table class="v1inner-body" align="center" width="90%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; margin: 0 auto; padding: 0; width: 90%;">
    <tr>
        <td class="v1content-cell" style="padding: 35px">
            <h1>Supervisor Attendance Report</h1>

            @foreach ($supers as $id => $name)
                <h3>{{$name}}</h3>
                <table style="border: 1px solid; border-collapse: collapse">
                    <tr style="border: 1px solid; background-color: #F6F6F6; font-weight: bold;">
                        <td width="100" style="border: 1px solid">Date</td>
                        <td width="80" style="border: 1px solid">Time</td>
                        <td width="400" style="border: 1px solid">Site</td>
                    </tr>
                    @if ($attendance->count())
                        @foreach($attendance as $attend)
                            @if ($attend->user_id == $id)
                                <tr>
                                    <td style="border: 1px solid">{{ $attend->date->format('d/m/Y') }}</td>
                                    <td style="border: 1px solid">{{ $attend->date->format('g:i a') }}</td>
                                    <td style="border: 1px solid">{{ $attend->site->name  }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @else
                        <tr>
                            <td colspan="3" style="border: 1px solid">Didn't log into any sites this week</td>
                        </tr>
                    @endif
                </table>
                <br><br>
            @endforeach
            <br>
            <hr>
            <p>This email has been generated on behalf of Cape Cod</p>

            <p>Regards,<br/>SafeWorksite</p>
        </td>
    </tr>
</table>

@include('emails/_email-end')